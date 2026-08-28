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
use App\Services\ApplicationKanbanTransitionService;
use App\Services\ApplicationWorkflowGuard;
use App\Services\ApplicationWorkflowSummaryService;
use App\Services\InterviewEvaluationService;
use App\Services\InterviewEvaluatorService;
use App\Services\InterviewRoundWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MultiRoundInterviewWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_passing_an_intermediate_round_keeps_application_in_interview_and_opens_next_round(): void
    {
        [$hr, $application, $templates] = $this->makeApplicationWithTwoRounds();
        $coordinatingHr = User::factory()->create([
            'role' => 'hr',
            'branch_id' => $hr->branch_id,
            'is_active' => true,
        ]);
        $interview = $this->createInterview($application, $hr, 1, $templates[0]);

        $result = app(InterviewEvaluationService::class)->complete($application, [
            'template_id' => $templates[0]->id,
            'criteria' => [['name' => 'Chuyên môn', 'score' => 8, 'note' => null]],
            'conclusion' => 'pass',
            'notes' => 'Ứng viên đạt yêu cầu chuyên môn của vòng một.',
        ], $hr);

        $application->refresh();
        $interview->refresh();
        $next = app(InterviewRoundWorkflowService::class)->schedulingContext($application);
        $summary = app(ApplicationWorkflowSummaryService::class)->summarize($application);

        $this->assertSame('round_passed', $result['completion_state']);
        $this->assertFalse($result['process_completed']);
        $this->assertSame(StatusApplicationEnum::INTERVIEWING, $application->status);
        $this->assertSame('pass', $interview->result);
        $this->assertNotNull($interview->finalized_at);
        $this->assertSame(2, $next['round_number']);
        $this->assertFalse($next['is_update']);
        $this->assertSame($templates[1]->id, $next['round']['scorecard_template']['id']);
        $this->assertTrue(app(ApplicationWorkflowGuard::class)->canManageInterview($hr, $application));
        $this->assertSame('Đã đạt vòng 1', $summary['status_label']);
        $this->assertStringContainsString('phỏng vấn phù hợp đơn vị', $summary['description']);

        $offerMove = app(ApplicationKanbanTransitionService::class)
            ->evaluateStageMove($application, 'offer', $hr);
        $this->assertFalse($offerMove['allowed']);
        $this->assertNull($offerMove['requires']);
        $this->assertStringContainsString('Phỏng vấn phù hợp đơn vị', $offerMove['message']);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $coordinatingHr->id,
            'type' => 'interview_next_round_ready',
        ]);
    }

    public function test_only_passing_the_final_round_moves_application_to_offer(): void
    {
        [$hr, $application, $templates] = $this->makeApplicationWithTwoRounds();
        $roundOne = $this->createInterview($application, $hr, 1, $templates[0]);
        $roundOne->forceFill([
            'result' => 'pass',
            'finalized_at' => now()->subDay(),
            'finalized_by_user_id' => $hr->id,
        ])->save();
        $roundTwo = $this->createInterview($application, $hr, 2, $templates[1]);

        $result = app(InterviewEvaluationService::class)->complete($application, [
            'template_id' => $templates[1]->id,
            'criteria' => [['name' => 'Phù hợp đơn vị', 'score' => 8, 'note' => null]],
            'conclusion' => 'pass',
            'notes' => 'Ứng viên phù hợp với đơn vị tuyển dụng.',
        ], $hr);

        $this->assertSame('finalized', $result['completion_state']);
        $this->assertTrue($result['process_completed']);
        $this->assertSame(StatusApplicationEnum::OFFERED, $application->fresh()->status);
        $this->assertSame('pass', $roundTwo->fresh()->result);
    }

    public function test_round_schedule_keeps_the_scorecard_snapshot_from_the_job_process(): void
    {
        [$hr, $application, $templates] = $this->makeApplicationWithTwoRounds();
        $interview = $this->createInterview($application, $hr, 1, $templates[0]);
        $templates[0]->update([
            'name' => 'Mẫu đã thay đổi sau khi đăng tin',
            'criteria' => [['name' => 'Tiêu chí mới']],
        ]);

        $context = app(InterviewRoundWorkflowService::class)->schedulingContext($application);

        $this->assertTrue($context['is_update']);
        $this->assertSame($interview->id, $context['interview']->id);
        $this->assertSame('Mẫu vòng 1', $context['round']['scorecard_template']['name']);
        $this->assertSame('Chuyên môn', $context['round']['scorecard_template']['criteria'][0]['name']);
    }

    public function test_panel_finalization_of_an_intermediate_round_also_keeps_application_in_interview(): void
    {
        [$hr, $application, $templates] = $this->makeApplicationWithTwoRounds();
        $member = User::factory()->create([
            'role' => 'pm',
            'branch_id' => $hr->branch_id,
            'is_active' => true,
        ]);
        $interview = $this->createInterview($application, $hr, 1, $templates[0]);
        app(InterviewEvaluatorService::class)->sync(
            $interview->loadMissing('application.job'),
            [$member->id],
        );
        $service = app(InterviewEvaluationService::class);

        foreach ([[$hr, 8], [$member, 7]] as [$evaluator, $score]) {
            $service->complete($application->fresh(), [
                'template_id' => $templates[0]->id,
                'criteria' => [['name' => 'Chuyên môn', 'score' => $score, 'note' => null]],
                'conclusion' => 'pass',
                'notes' => 'Ứng viên đáp ứng yêu cầu theo phiếu đánh giá.',
            ], $evaluator);
        }

        $result = $service->finalizePanel($application->fresh(), [
            'conclusion' => 'pass',
            'notes' => 'Hội đồng thống nhất chuyển vòng.',
        ], $hr);

        $this->assertSame('round_passed', $result['completion_state']);
        $this->assertSame(StatusApplicationEnum::INTERVIEWING, $application->fresh()->status);
        $this->assertSame(2, $result['next_round']['round_number']);
        $this->assertNotNull($interview->fresh()->finalized_at);
    }

    public function test_evaluators_must_match_the_roles_configured_for_the_current_round(): void
    {
        [$hr, $application, $templates] = $this->makeApplicationWithTwoRounds();
        $pm = User::factory()->create([
            'role' => 'pm',
            'branch_id' => $hr->branch_id,
            'is_active' => true,
        ]);
        $interview = $this->createInterview($application, $hr, 2, $templates[1]);

        $this->expectException(ValidationException::class);

        app(InterviewEvaluatorService::class)->sync(
            $interview->loadMissing('application.job'),
            [$pm->id],
        );
    }

    /**
     * @return array{User, Application, array{ScorecardTemplate, ScorecardTemplate}}
     */
    private function makeApplicationWithTwoRounds(): array
    {
        $branch = Branch::query()->create([
            'name' => 'FPT Polytechnic Cần Thơ',
            'code' => 'FPTCT-MULTI',
            'city' => 'Cần Thơ',
            'is_active' => true,
        ]);
        $hr = User::factory()->create([
            'role' => 'hr',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $candidate = Candidate::query()->create([
            'name' => 'Ứng viên nhiều vòng',
            'email' => 'multi-round-candidate@example.test',
            'phone' => '0901000001',
        ]);
        $roundOneTemplate = ScorecardTemplate::query()->create([
            'name' => 'Mẫu vòng 1',
            'criteria' => [['name' => 'Chuyên môn', 'score' => null, 'note' => null]],
            'is_default' => true,
            'created_by' => $hr->id,
        ]);
        $roundTwoTemplate = ScorecardTemplate::query()->create([
            'name' => 'Mẫu vòng 2',
            'criteria' => [['name' => 'Phù hợp đơn vị', 'score' => null, 'note' => null]],
            'is_default' => false,
            'created_by' => $hr->id,
        ]);
        $job = RecruitmentJob::query()->create([
            'title' => 'Giảng viên Công nghệ thông tin',
            'slug' => 'giang-vien-cntt-multi-round',
            'description' => 'Tuyển dụng giảng viên cho FPT Education.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $hr->id,
        ]);
        $job->forceFill([
            'interview_process_snapshot' => [
                'version' => 1,
                'code' => 'two-round-test',
                'name' => 'Quy trình hai vòng',
                'round_count' => 2,
                'rounds' => [
                    [
                        'round_number' => 1,
                        'name' => 'Phỏng vấn chuyên môn',
                        'candidate_label' => 'Trao đổi chuyên môn',
                        'evaluator_roles' => ['hr', 'pm'],
                        'scorecard_template' => [
                            'id' => $roundOneTemplate->id,
                            'name' => $roundOneTemplate->name,
                            'criteria' => $roundOneTemplate->criteria,
                        ],
                    ],
                    [
                        'round_number' => 2,
                        'name' => 'Phỏng vấn phù hợp đơn vị',
                        'candidate_label' => 'Trao đổi với đơn vị tuyển dụng',
                        'evaluator_roles' => ['hr', 'director'],
                        'scorecard_template' => [
                            'id' => $roundTwoTemplate->id,
                            'name' => $roundTwoTemplate->name,
                            'criteria' => $roundTwoTemplate->criteria,
                        ],
                    ],
                ],
            ],
        ])->saveQuietly();
        $application = Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'status' => StatusApplicationEnum::INTERVIEWING,
            'branch_id' => $branch->id,
            'cv_path' => 'applications/cv/multi-round.pdf',
            'apply_method' => 'cv',
            'source' => 'website',
        ]);

        return [$hr, $application, [$roundOneTemplate, $roundTwoTemplate]];
    }

    private function createInterview(
        Application $application,
        User $hr,
        int $round,
        ScorecardTemplate $template,
    ): Interview {
        $name = $round === 1 ? 'Phỏng vấn chuyên môn' : 'Phỏng vấn phù hợp đơn vị';

        return Interview::query()->create([
            'application_id' => $application->id,
            'interviewer_id' => $hr->id,
            'scorecard_template_id' => $template->id,
            'scorecard_template_snapshot' => [
                'id' => $template->id,
                'name' => $template->name,
                'criteria' => $template->criteria,
            ],
            'round_number' => $round,
            'round_name' => $name,
            'scheduled_at' => now()->subHours(2),
            'duration_minutes' => 60,
            'type' => 'online',
            'meeting_link' => 'https://meet.google.com/fpt-round-'.$round,
            'invite_sent_at' => now()->subHours(3),
            'result' => 'pending',
        ]);
    }
}
