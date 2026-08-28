<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\Offer;
use App\Models\RecruitmentJob;
use App\Models\User;
use App\Services\ApplicationPipelineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ApplicationPipelineServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_allows_only_the_next_pipeline_steps_and_rejection(): void
    {
        $service = app(ApplicationPipelineService::class);

        $this->assertEqualsCanonicalizing([
            StatusApplicationEnum::SCREENING,
            StatusApplicationEnum::REJECTED,
            StatusApplicationEnum::WITHDRAWN,
        ], $service->allowedTransitions(StatusApplicationEnum::NEW));

        $this->assertEqualsCanonicalizing([
            StatusApplicationEnum::INTERVIEW_SCHEDULED,
            StatusApplicationEnum::REJECTED,
            StatusApplicationEnum::WITHDRAWN,
        ], $service->allowedTransitions(StatusApplicationEnum::SCREENING));

        $this->assertEqualsCanonicalizing([
            StatusApplicationEnum::INTERVIEW,
            StatusApplicationEnum::REJECTED,
            StatusApplicationEnum::WITHDRAWN,
        ], $service->allowedTransitions(StatusApplicationEnum::INTERVIEW_SCHEDULED));

        $this->assertEqualsCanonicalizing([
            StatusApplicationEnum::OFFER,
            StatusApplicationEnum::REJECTED,
            StatusApplicationEnum::WITHDRAWN,
        ], $service->allowedTransitions(StatusApplicationEnum::INTERVIEW));

        $this->assertEqualsCanonicalizing([
            StatusApplicationEnum::HIRED,
            StatusApplicationEnum::REJECTED,
            StatusApplicationEnum::WITHDRAWN,
        ], $service->allowedTransitions(StatusApplicationEnum::OFFER));

        $this->assertSame([], $service->allowedTransitions(StatusApplicationEnum::HIRED));
        $this->assertSame([], $service->allowedTransitions(StatusApplicationEnum::REJECTED));
        $this->assertSame([], $service->allowedTransitions(StatusApplicationEnum::WITHDRAWN));
    }

    public function test_it_transitions_valid_status_and_rejects_invalid_jump(): void
    {
        Mail::fake();

        $application = $this->makeApplication(StatusApplicationEnum::NEW);
        $service = app(ApplicationPipelineService::class);

        $service->transition($application, StatusApplicationEnum::SCREENING);

        $application->refresh();
        $this->assertSame(StatusApplicationEnum::SCREENING, $application->status);
        $this->assertDatabaseHas('application_status_histories', [
            'application_id' => $application->id,
            'from_status' => StatusApplicationEnum::NEW->value,
            'to_status' => StatusApplicationEnum::SCREENING->value,
        ]);

        $this->expectException(ValidationException::class);

        $service->transition($application, StatusApplicationEnum::HIRED);
    }

    public function test_hired_transition_requires_an_accepted_offer(): void
    {
        $application = $this->makeApplication(StatusApplicationEnum::OFFER);
        $service = app(ApplicationPipelineService::class);

        try {
            $service->transition($application, StatusApplicationEnum::HIRED);
            $this->fail('Expected hired transition without accepted offer to fail.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'Chỉ có thể chuyển sang Đã tuyển sau khi ứng viên chấp nhận đề nghị tuyển dụng.',
                $exception->errors()['status'][0],
            );
        }

        $this->assertSame(StatusApplicationEnum::OFFER, $application->fresh()->status);

        Offer::query()->create([
            'application_id' => $application->id,
            'content' => 'Offer accepted by candidate.',
            'salary_offered' => 20000000,
            'status' => 'accepted',
            'accepted_at' => now(),
            'response_at' => now(),
        ]);

        $service->transition($application->fresh(), StatusApplicationEnum::HIRED);

        $this->assertSame(StatusApplicationEnum::HIRED, $application->fresh()->status);
    }

    public function test_terminal_statuses_cannot_move_forward(): void
    {
        $service = app(ApplicationPipelineService::class);

        $this->assertFalse($service->canTransition(StatusApplicationEnum::HIRED, StatusApplicationEnum::SCREENING));
        $this->assertFalse($service->canTransition(StatusApplicationEnum::REJECTED, StatusApplicationEnum::SCREENING));
        $this->assertFalse($service->canTransition(StatusApplicationEnum::WITHDRAWN, StatusApplicationEnum::SCREENING));
    }

    private function makeApplication(StatusApplicationEnum $status): Application
    {
        $branch = Branch::query()->create([
            'name' => 'Pipeline Service Branch',
            'code' => 'PSB',
            'city' => 'ho_chi_minh',
            'is_active' => true,
        ]);
        $hr = User::factory()->create([
            'role' => 'hr',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $candidate = Candidate::query()->create([
            'name' => 'Pipeline Candidate',
            'email' => 'pipeline-candidate@example.com',
            'phone' => '0901234567',
        ]);
        $job = RecruitmentJob::query()->create([
            'title' => 'Pipeline Job',
            'slug' => 'pipeline-job',
            'description' => 'Pipeline service test.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $hr->id,
        ]);

        return Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'cv_path' => 'candidates/pipeline/cv.pdf',
            'apply_method' => 'cv',
            'source' => 'website',
            'status' => $status,
            'branch_id' => $branch->id,
            'applied_at' => now(),
        ]);
    }
}
