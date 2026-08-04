<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\Offer;
use App\Models\RecruitmentJob;
use App\Models\User;
use App\Services\ApplicationKanbanTransitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationKanbanTransitionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_blocks_invalid_stage_jump(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::NEW);

        $result = app(ApplicationKanbanTransitionService::class)
            ->evaluateStageMove($application, 'hired', $hr);

        $this->assertFalse($result['allowed']);
        $this->assertNull($result['target_status']);
        $this->assertNull($result['requires']);
        $this->assertStringContainsString('Không thể chuyển', $result['message']);
    }

    public function test_it_requires_screening_data_before_moving_to_screening_stage(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::NEW);

        $result = app(ApplicationKanbanTransitionService::class)
            ->evaluateStageMove($application, 'screening', $hr);

        $this->assertFalse($result['allowed']);
        $this->assertSame(StatusApplicationEnum::SCREENING->value, $result['target_status']);
        $this->assertSame('cv_screening', $result['requires']);
    }

    public function test_it_requires_rejection_reason_before_moving_to_rejected_stage(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::SCREENING);

        $result = app(ApplicationKanbanTransitionService::class)
            ->evaluateStageMove($application, 'rejected', $hr);

        $this->assertFalse($result['allowed']);
        $this->assertSame(StatusApplicationEnum::REJECTED->value, $result['target_status']);
        $this->assertSame('rejection_reason', $result['requires']);
    }

    public function test_it_requires_interview_evaluation_before_moving_interview_stage_to_offer_stage(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::INTERVIEW_SCHEDULED);

        $result = app(ApplicationKanbanTransitionService::class)
            ->evaluateStageMove($application, 'offer', $hr);

        $this->assertFalse($result['allowed']);
        $this->assertSame(StatusApplicationEnum::OFFERED->value, $result['target_status']);
        $this->assertSame('interview_evaluation', $result['requires']);
        $this->assertStringContainsString('đánh giá phỏng vấn', $result['message']);
    }

    public function test_it_only_allows_hired_stage_after_candidate_accepts_offer(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::OFFER);

        Offer::query()->create([
            'application_id' => $application->id,
            'status' => 'pending',
            'salary_offered' => 15000000,
            'start_date' => now()->addMonth(),
            'probation_months' => 2,
            'expires_at' => now()->addDays(3),
            'content' => 'Offer content',
        ]);

        $service = app(ApplicationKanbanTransitionService::class);
        $pendingResult = $service->evaluateStageMove($application->fresh('latestOffer'), 'hired', $hr);

        $this->assertFalse($pendingResult['allowed']);
        $this->assertStringContainsString('đã đồng ý', $pendingResult['message']);

        $application->latestOffer()->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        $acceptedResult = $service->evaluateStageMove($application->fresh('latestOffer'), 'hired', $hr);

        $this->assertTrue($acceptedResult['allowed']);
        $this->assertSame(StatusApplicationEnum::HIRED->value, $acceptedResult['target_status']);
        $this->assertNull($acceptedResult['requires']);
    }

    /**
     * @return array{0: User, 1: Application}
     */
    private function makeApplication(StatusApplicationEnum $status): array
    {
        $branch = Branch::query()->create([
            'name' => 'Kanban Transition Branch',
            'code' => 'KTB',
            'city' => 'can_tho',
            'is_active' => true,
        ]);

        $hr = User::factory()->create([
            'role' => 'hr',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        $candidate = Candidate::query()->create([
            'name' => 'Kanban Candidate',
            'email' => 'kanban-candidate@example.com',
            'phone' => '0901234567',
        ]);

        $job = RecruitmentJob::query()->create([
            'title' => 'Kanban Job',
            'slug' => 'kanban-job-'.strtolower($status->value),
            'description' => 'Kanban transition test.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $hr->id,
        ]);

        $application = Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'cv_path' => 'candidates/kanban/cv.pdf',
            'apply_method' => 'cv',
            'source' => 'website',
            'status' => $status,
            'branch_id' => $branch->id,
            'applied_at' => now(),
        ]);

        return [$hr, $application];
    }
}
