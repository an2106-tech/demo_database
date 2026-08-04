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
use App\Services\InterviewEvaluationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_completion_requires_a_score_for_every_template_criterion(): void
    {
        [$hr, $application] = $this->makeInterviewApplication(StatusApplicationEnum::INTERVIEWING);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

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
            'confirm_early_completion' => true,
        ], $hr);

        $application->refresh();
        $interview->refresh();

        $this->assertSame('pass', $result['conclusion']);
        $this->assertSame(StatusApplicationEnum::OFFERED, $application->status);
        $this->assertNotNull($interview->actual_ended_at);
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
