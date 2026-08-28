<?php

namespace Tests\Unit;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\Interview;
use App\Models\RecruitmentJob;
use App\Models\ScorecardTemplate;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\InterviewEvaluationService;
use App\Services\InterviewEvaluatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InterviewEvaluationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_passing_evaluation_creates_scorecard_and_moves_to_offer(): void
    {
        [$hr, $application] = $this->makeInterviewApplication(StatusApplicationEnum::INTERVIEWING);

        $result = app(InterviewEvaluationService::class)->evaluate($application, [
            'template_id' => $this->templateId(),
            'criteria' => $this->criteria(8),
            'conclusion' => 'pass',
            'notes' => 'Đạt yêu cầu chuyên môn.',
        ], $hr);

        $application->refresh();

        $this->assertSame('pass', $result['conclusion']);
        $this->assertSame(StatusApplicationEnum::OFFERED, $application->status);
        $this->assertDatabaseHas('scorecards', [
            'application_id' => $application->id,
            'evaluator_id' => $hr->id,
            'conclusion' => 'pass',
        ]);
    }

    public function test_failed_evaluation_requires_rejection_reason_and_moves_to_rejected(): void
    {
        [$hr, $application] = $this->makeInterviewApplication(StatusApplicationEnum::INTERVIEWING);

        $result = app(InterviewEvaluationService::class)->evaluate($application, [
            'template_id' => $this->templateId(),
            'criteria' => $this->criteria(3),
            'conclusion' => 'fail',
            'notes' => 'Ứng viên chưa đáp ứng yêu cầu chuyên môn của vị trí.',
            'rejected_reason' => 'Kinh nghiệm thực tế chưa đáp ứng yêu cầu vị trí.',
        ], $hr);

        $application->refresh();

        $this->assertSame('fail', $result['conclusion']);
        $this->assertSame(StatusApplicationEnum::REJECTED, $application->status);
        $this->assertSame('interview', $application->rejected_stage);
        $this->assertSame('Kinh nghiệm thực tế chưa đáp ứng yêu cầu vị trí.', $application->rejected_reason);
    }

    public function test_hold_evaluation_keeps_application_in_interview_stage(): void
    {
        [$hr, $application] = $this->makeInterviewApplication(StatusApplicationEnum::INTERVIEW_SCHEDULED);

        $result = app(InterviewEvaluationService::class)->evaluate($application, [
            'template_id' => $this->templateId(),
            'criteria' => $this->criteria(6),
            'conclusion' => 'hold',
            'notes' => 'Cần trao đổi thêm với quản lý trực tiếp.',
        ], $hr);

        $application->refresh();

        $this->assertSame('hold', $result['conclusion']);
        $this->assertSame(StatusApplicationEnum::INTERVIEWING, $application->status);
    }

    public function test_submitting_an_evaluation_requires_an_internal_note(): void
    {
        [$hr, $application] = $this->makeInterviewApplication(StatusApplicationEnum::INTERVIEWING);

        try {
            app(InterviewEvaluationService::class)->complete($application, [
                'template_id' => $this->templateId(),
                'criteria' => $this->criteria(6),
                'conclusion' => 'hold',
            ], $hr);

            $this->fail('An evaluation without notes should not be submitted.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'Vui lòng nhập nhận xét nội bộ trước khi gửi phiếu đánh giá.',
                $exception->errors()['notes'][0],
            );
        }
    }

    public function test_saving_a_follow_up_note_keeps_a_hold_conclusion(): void
    {
        [$hr, $application] = $this->makeInterviewApplication(StatusApplicationEnum::INTERVIEWING);
        $service = app(InterviewEvaluationService::class);

        $service->evaluate($application, [
            'template_id' => $this->templateId(),
            'criteria' => $this->criteria(6),
            'conclusion' => 'hold',
            'notes' => 'Cần trao đổi thêm với quản lý trực tiếp.',
        ], $hr);

        $service->saveDraft($application, [
            'template_id' => $this->templateId(),
            'criteria' => $this->criteria(6),
            'notes' => 'Đã bổ sung nhận xét sau trao đổi nội bộ.',
        ], $hr);

        $this->assertDatabaseHas('scorecards', [
            'application_id' => $application->id,
            'evaluator_id' => $hr->id,
            'conclusion' => 'hold',
            'notes' => 'Đã bổ sung nhận xét sau trao đổi nội bộ.',
        ]);
    }

    public function test_draft_evaluation_does_not_move_the_application_to_the_next_stage(): void
    {
        [$hr, $application] = $this->makeInterviewApplication(StatusApplicationEnum::INTERVIEWING);

        $result = app(InterviewEvaluationService::class)->saveDraft($application, [
            'template_id' => $this->templateId(),
            'criteria' => [
                ['name' => 'Chuyen mon', 'score' => 8, 'note' => 'Phan hoi tot.'],
                ['name' => 'Giao tiep', 'score' => null, 'note' => null],
            ],
            'notes' => 'Dang dien ra phong van.',
        ], $hr);

        $application->refresh();

        $this->assertFalse($result['is_complete']);
        $this->assertSame(StatusApplicationEnum::INTERVIEWING, $application->status);
        $this->assertDatabaseHas('scorecards', [
            'application_id' => $application->id,
            'evaluator_id' => $hr->id,
            'conclusion' => null,
        ]);
    }

    public function test_draft_keeps_the_selected_conclusion_without_submitting_the_scorecard(): void
    {
        [$hr, $application] = $this->makeInterviewApplication(StatusApplicationEnum::INTERVIEWING);

        app(InterviewEvaluationService::class)->saveDraft($application, [
            'template_id' => $this->templateId(),
            'criteria' => $this->criteria(8),
            'conclusion' => 'pass',
            'notes' => 'Ứng viên đáp ứng yêu cầu chuyên môn.',
        ], $hr);

        $scorecard = $application->scorecards()->firstOrFail();

        $this->assertSame('pass', $scorecard->conclusion);
        $this->assertSame('pass', $scorecard->recommended_conclusion);
        $this->assertNull($scorecard->submitted_at);
        $this->assertSame(StatusApplicationEnum::INTERVIEWING, $application->fresh()->status);
    }

    public function test_completion_requires_a_score_for_every_template_criterion(): void
    {
        [$hr, $application] = $this->makeInterviewApplication(StatusApplicationEnum::INTERVIEWING);

        $this->expectException(ValidationException::class);

        app(InterviewEvaluationService::class)->complete($application, [
            'template_id' => $this->templateId(),
            'criteria' => [
                ['name' => 'Chuyen mon', 'score' => 8, 'note' => null],
                ['name' => 'Giao tiep', 'score' => null, 'note' => null],
            ],
            'conclusion' => 'pass',
        ], $hr);
    }

    public function test_saving_an_unchanged_draft_does_not_create_or_update_another_scorecard(): void
    {
        [$hr, $application] = $this->makeInterviewApplication(StatusApplicationEnum::INTERVIEWING);
        $data = [
            'template_id' => $this->templateId(),
            'criteria' => [
                ['name' => 'Chuyen mon', 'score' => 8, 'note' => 'Tot.'],
                ['name' => 'Giao tiep', 'score' => null, 'note' => null],
            ],
            'notes' => 'Dang ghi nhan.',
        ];

        $first = app(InterviewEvaluationService::class)->saveDraft($application, $data, $hr);
        $second = app(InterviewEvaluationService::class)->saveDraft($application, $data, $hr);

        $this->assertTrue($first['saved']);
        $this->assertFalse($second['saved']);
        $this->assertNotNull($first['saved_at']);
        $this->assertSame(
            $first['saved_at']->toISOString(),
            $second['saved_at']->toISOString(),
        );
        $this->assertSame(1, $application->scorecards()->count());
    }

    public function test_completed_early_interview_can_be_finalized_after_explicit_confirmation(): void
    {
        [$hr, $application] = $this->makeInterviewApplication(StatusApplicationEnum::INTERVIEWING);
        $interview = $application->interviews()->latest('id')->firstOrFail();
        $interview->update([
            'scheduled_at' => now()->subMinutes(10),
            'duration_minutes' => 60,
        ]);

        $result = app(InterviewEvaluationService::class)->complete($application, [
            'template_id' => $this->templateId(),
            'criteria' => $this->criteria(8),
            'conclusion' => 'pass',
            'notes' => 'Ứng viên đáp ứng yêu cầu và buổi phỏng vấn đã hoàn tất.',
            'confirm_early_completion' => true,
        ], $hr);

        $application->refresh();
        $interview->refresh();

        $this->assertSame('pass', $result['conclusion']);
        $this->assertSame(StatusApplicationEnum::OFFERED, $application->status);
        $this->assertNotNull($interview->actual_ended_at);
    }

    public function test_locked_interview_template_uses_its_snapshot_for_evaluation(): void
    {
        [$hr, $application] = $this->makeInterviewApplication(StatusApplicationEnum::INTERVIEWING);
        $template = ScorecardTemplate::query()->firstOrFail();
        $interview = $application->interviews()->latest('id')->firstOrFail();
        $snapshotCriteria = [
            ['name' => 'Nghiệp vụ giáo dục', 'score' => null, 'note' => null],
            ['name' => 'Giao tiếp với người học', 'score' => null, 'note' => null],
        ];

        $interview->update([
            'scorecard_template_id' => $template->id,
            'scorecard_template_snapshot' => [
                'name' => 'Mẫu đã gắn với lịch',
                'criteria' => $snapshotCriteria,
            ],
        ]);
        $template->update([
            'criteria' => [['name' => 'Tiêu chí mới của hệ thống', 'score' => null, 'note' => null]],
        ]);

        app(InterviewEvaluationService::class)->complete($application, [
            'template_id' => $template->id,
            'criteria' => [
                ['name' => 'Nghiệp vụ giáo dục', 'score' => 8, 'note' => null],
                ['name' => 'Giao tiếp với người học', 'score' => 9, 'note' => null],
            ],
            'conclusion' => 'pass',
            'notes' => 'Ứng viên đáp ứng các tiêu chí của mẫu đã gắn với lịch.',
        ], $hr);

        $criteria = $application->scorecards()->firstOrFail()->criteria;

        $this->assertSame('Nghiệp vụ giáo dục', $criteria[0]['name']);
        $this->assertSame('Giao tiếp với người học', $criteria[1]['name']);
    }

    public function test_multiple_evaluators_submit_individually_before_lead_finalizes_the_round(): void
    {
        [$hr, $application] = $this->makeInterviewApplication(StatusApplicationEnum::INTERVIEWING);
        $panelist = User::factory()->create([
            'role' => 'pm',
            'is_active' => true,
            'branch_id' => $hr->branch_id,
        ]);
        $interview = $application->interviews()->latest('id')->firstOrFail();
        app(InterviewEvaluatorService::class)->sync(
            $interview->loadMissing('application.job'),
            [$panelist->id],
        );
        $service = app(InterviewEvaluationService::class);

        $first = $service->complete($application, [
            'template_id' => $this->templateId(),
            'criteria' => $this->criteria(8),
            'conclusion' => 'pass',
            'notes' => 'Đáp ứng yêu cầu chuyên môn của vòng.',
        ], $hr);

        $this->assertFalse($first['finalized']);
        $this->assertSame(1, $first['progress']['submitted']);
        $this->assertSame(StatusApplicationEnum::INTERVIEWING, $application->fresh()->status);

        $second = $service->complete($application->fresh(), [
            'template_id' => $this->templateId(),
            'criteria' => $this->criteria(6),
            'conclusion' => 'hold',
            'notes' => 'Cần hội đồng trao đổi thêm trước khi chốt.',
        ], $panelist);

        $this->assertFalse($second['finalized']);
        $this->assertTrue($second['progress']['all_submitted']);
        $this->assertSame(StatusApplicationEnum::INTERVIEWING, $application->fresh()->status);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $hr->id,
            'type' => 'interview_panel_ready',
        ]);
        $this->assertSame(
            'Đã đủ phiếu đánh giá',
            UserNotification::query()
                ->where('user_id', $hr->id)
                ->where('type', 'interview_panel_ready')
                ->firstOrFail()
                ->title,
        );
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $panelist->id,
            'type' => 'interview_panel_ready',
        ]);

        try {
            $service->finalizePanel($application->fresh(), [
                'conclusion' => 'hold',
            ], $hr);

            $this->fail('A panel hold conclusion without notes should not be finalized.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'Vui lòng nhập nhận xét chung trước khi chốt kết quả vòng.',
                $exception->errors()['notes'][0],
            );
        }

        $final = $service->finalizePanel($application->fresh(), [
            'conclusion' => 'pass',
            'notes' => 'Hội đồng thống nhất ứng viên đáp ứng yêu cầu.',
        ], $hr);

        $this->assertTrue($final['finalized']);
        $this->assertSame(7.0, $final['average']);
        $this->assertSame(StatusApplicationEnum::OFFERED, $application->fresh()->status);
        $this->assertNotNull($interview->fresh()->finalized_at);
    }

    public function test_individual_fail_recommendation_does_not_require_candidate_rejection_reason(): void
    {
        [$hr, $application] = $this->makeInterviewApplication(StatusApplicationEnum::INTERVIEWING);
        $panelist = User::factory()->create([
            'role' => 'pm',
            'is_active' => true,
            'branch_id' => $hr->branch_id,
        ]);
        $interview = $application->interviews()->latest('id')->firstOrFail();
        app(InterviewEvaluatorService::class)->sync(
            $interview->loadMissing('application.job'),
            [$panelist->id],
        );

        $result = app(InterviewEvaluationService::class)->complete($application, [
            'template_id' => $this->templateId(),
            'criteria' => $this->criteria(3),
            'conclusion' => 'fail',
            'notes' => 'Chưa đáp ứng yêu cầu chuyên môn theo phiếu cá nhân.',
        ], $panelist);

        $this->assertSame('submitted', $result['completion_state']);
        $this->assertSame(StatusApplicationEnum::INTERVIEWING, $application->fresh()->status);
        $this->assertNull($application->fresh()->rejected_reason);
    }

    public function test_lead_can_waive_a_pending_panelist_and_finalize_the_remaining_panel(): void
    {
        [$hr, $application] = $this->makeInterviewApplication(StatusApplicationEnum::INTERVIEWING);
        $panelist = User::factory()->create([
            'role' => 'pm',
            'is_active' => true,
            'branch_id' => $hr->branch_id,
        ]);
        $interview = $application->interviews()->latest('id')->firstOrFail();
        $evaluatorService = app(InterviewEvaluatorService::class);
        $evaluatorService->sync($interview->loadMissing('application.job'), [$panelist->id]);

        app(InterviewEvaluationService::class)->complete($application, [
            'template_id' => $this->templateId(),
            'criteria' => $this->criteria(8),
            'conclusion' => 'pass',
            'notes' => 'Đáp ứng yêu cầu chuyên môn của vòng.',
        ], $hr);

        $evaluatorService->waivePendingEvaluator(
            $interview,
            $panelist->id,
            $hr,
            'Không tham gia buổi phỏng vấn.',
        );

        $progress = $evaluatorService->progress($interview);
        $this->assertTrue($progress['is_panel']);
        $this->assertSame(1, $progress['required']);
        $this->assertSame(1, $progress['submitted']);
        $this->assertSame(1, $progress['waived']);
        $this->assertTrue($progress['all_submitted']);
        $this->assertDatabaseHas('interview_evaluators', [
            'interview_id' => $interview->id,
            'user_id' => $panelist->id,
            'is_required' => false,
            'waived_by_user_id' => $hr->id,
            'waiver_reason' => 'Không tham gia buổi phỏng vấn.',
        ]);
        $this->assertDatabaseHas('application_status_histories', [
            'application_id' => $application->id,
            'from_status' => StatusApplicationEnum::INTERVIEWING->value,
            'to_status' => StatusApplicationEnum::INTERVIEWING->value,
            'changed_by_id' => $hr->id,
        ]);

        $result = app(InterviewEvaluationService::class)->finalizePanel($application->fresh(), [
            'conclusion' => 'pass',
            'notes' => 'Chốt theo phiếu của thành viên thực tế tham gia.',
        ], $hr);

        $this->assertTrue($result['finalized']);
        $this->assertSame(StatusApplicationEnum::OFFERED, $application->fresh()->status);
    }

    public function test_panelist_cannot_waive_another_required_evaluator(): void
    {
        [$hr, $application] = $this->makeInterviewApplication(StatusApplicationEnum::INTERVIEWING);
        $panelist = User::factory()->create([
            'role' => 'pm',
            'is_active' => true,
            'branch_id' => $hr->branch_id,
        ]);
        $otherPanelist = User::factory()->create([
            'role' => 'pm',
            'is_active' => true,
            'branch_id' => $hr->branch_id,
        ]);
        $interview = $application->interviews()->latest('id')->firstOrFail();
        $service = app(InterviewEvaluatorService::class);
        $service->sync($interview->loadMissing('application.job'), [$panelist->id, $otherPanelist->id]);

        $this->expectException(ValidationException::class);

        $service->waivePendingEvaluator(
            $interview,
            $otherPanelist->id,
            $panelist,
            'Không tham gia buổi phỏng vấn.',
        );
    }

    public function test_submitted_panelist_cannot_be_waived(): void
    {
        [$hr, $application] = $this->makeInterviewApplication(StatusApplicationEnum::INTERVIEWING);
        $panelist = User::factory()->create([
            'role' => 'pm',
            'is_active' => true,
            'branch_id' => $hr->branch_id,
        ]);
        $interview = $application->interviews()->latest('id')->firstOrFail();
        $evaluatorService = app(InterviewEvaluatorService::class);
        $evaluatorService->sync($interview->loadMissing('application.job'), [$panelist->id]);
        app(InterviewEvaluationService::class)->complete($application, [
            'template_id' => $this->templateId(),
            'criteria' => $this->criteria(7),
            'conclusion' => 'pass',
            'notes' => 'Ứng viên đáp ứng phần lớn tiêu chí đánh giá.',
        ], $panelist);

        $this->expectException(ValidationException::class);

        $evaluatorService->waivePendingEvaluator(
            $interview,
            $panelist->id,
            $hr,
            'Không cần phiếu này.',
        );
    }

    public function test_submitted_scorecard_cannot_be_edited_or_sent_twice(): void
    {
        [$hr, $application] = $this->makeInterviewApplication(StatusApplicationEnum::INTERVIEWING);
        $panelist = User::factory()->create([
            'role' => 'pm',
            'is_active' => true,
            'branch_id' => $hr->branch_id,
        ]);
        $interview = $application->interviews()->latest('id')->firstOrFail();
        app(InterviewEvaluatorService::class)->sync(
            $interview->loadMissing('application.job'),
            [$panelist->id],
        );
        $service = app(InterviewEvaluationService::class);
        $service->complete($application, [
            'template_id' => $this->templateId(),
            'criteria' => $this->criteria(8),
            'conclusion' => 'pass',
            'notes' => 'Ứng viên đáp ứng yêu cầu chuyên môn của vòng.',
        ], $hr);

        $this->expectException(ValidationException::class);
        $service->saveDraft($application->fresh(), [
            'template_id' => $this->templateId(),
            'criteria' => $this->criteria(9),
        ], $hr);
    }

    public function test_single_evaluator_hold_remains_editable_and_does_not_finalize_round(): void
    {
        [$hr, $application] = $this->makeInterviewApplication(StatusApplicationEnum::INTERVIEWING);

        $result = app(InterviewEvaluationService::class)->complete($application, [
            'template_id' => $this->templateId(),
            'criteria' => $this->criteria(6),
            'conclusion' => 'hold',
            'notes' => 'Cần trao đổi thêm trước khi chốt.',
        ], $hr);

        $interview = $application->interviews()->latest('id')->firstOrFail();
        $scorecard = $application->scorecards()->firstOrFail();

        $this->assertSame('held', $result['completion_state']);
        $this->assertNull($scorecard->submitted_at);
        $this->assertNull($interview->finalized_at);
        $this->assertSame(StatusApplicationEnum::INTERVIEWING, $application->fresh()->status);
    }

    /**
     * @return array{0: User, 1: Application}
     */
    private function makeInterviewApplication(StatusApplicationEnum $status): array
    {
        $branch = Branch::query()->create([
            'name' => 'Greenwich Viet Nam - Can Tho',
            'code' => 'GWCT',
            'city' => 'can_tho',
            'is_active' => true,
        ]);
        $hr = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);
        $candidate = Candidate::query()->create([
            'name' => 'Ung vien kiem thu',
            'email' => 'candidate-evaluation@example.com',
            'phone' => '0901234567',
        ]);
        $job = RecruitmentJob::query()->create([
            'title' => 'Giang vien Cong nghe thong tin',
            'slug' => 'giang-vien-cntt-evaluation-test',
            'description' => 'Mo ta cong viec.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $hr->id,
        ]);
        $application = Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'status' => $status,
            'branch_id' => $branch->id,
            'cv_path' => 'applications/cv/test.pdf',
            'apply_method' => 'cv',
            'source' => 'website',
            'profile_snapshot' => ['candidate' => ['name' => $candidate->name, 'email' => $candidate->email]],
        ]);
        ScorecardTemplate::query()->create([
            'name' => 'Mau danh gia kiem thu',
            'criteria' => [
                ['name' => 'Chuyen mon', 'score' => null, 'note' => null],
                ['name' => 'Giao tiep', 'score' => null, 'note' => null],
            ],
            'is_default' => true,
            'created_by' => $hr->id,
        ]);
        Interview::query()->create([
            'application_id' => $application->id,
            'interviewer_id' => $hr->id,
            'round_number' => 1,
            'round_name' => 'Phong van vong 1',
            'scheduled_at' => now()->subHours(2),
            'duration_minutes' => 60,
            'type' => 'online',
            'meeting_link' => 'https://meet.google.com/abc-defg-hij',
            'invite_sent_at' => now()->subHours(3),
            'result' => 'pending',
        ]);

        return [$hr, $application];
    }

    /**
     * @return array<int, array{name: string, score: int, note: null}>
     */
    private function criteria(int $score): array
    {
        return [
            ['name' => 'Chuyen mon', 'score' => $score, 'note' => null],
            ['name' => 'Giao tiep', 'score' => $score, 'note' => null],
        ];
    }

    private function templateId(): int
    {
        return (int) ScorecardTemplate::query()->value('id');
    }
}
