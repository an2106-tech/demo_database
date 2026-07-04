<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
use App\Livewire\Client\Employers\ApplicationPipeline;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\RecruitmentJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class ApplicationPipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_pipeline_uses_application_snapshot_for_candidate_card(): void
    {
        $branch = Branch::query()->create([
            'name' => 'FPT Pipeline Branch',
            'code' => 'PB',
            'city' => 'ho_chi_minh',
            'is_active' => true,
        ]);
        $hr = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
            'metadata' => ['account_types' => ['employer'], 'account_type' => 'employer'],
        ]);
        $candidate = Candidate::query()->create([
            'name' => 'Current Candidate Name',
            'email' => 'current-candidate@example.com',
            'phone' => '0901234567',
            'cv_file' => 'candidates/current/cv.pdf',
        ]);
        $job = RecruitmentJob::query()->create([
            'title' => 'Pipeline Developer',
            'slug' => 'pipeline-developer',
            'description' => 'Build pipeline.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $hr->id,
        ]);
        Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'cv_path' => 'candidates/snapshot/cv.pdf',
            'apply_method' => 'cv',
            'source' => 'website',
            'status' => StatusApplicationEnum::NEW,
            'branch_id' => $branch->id,
            'profile_snapshot' => [
                'candidate' => [
                    'name' => 'Snapshot Candidate Name',
                    'email' => 'snapshot@example.com',
                ],
                'resume' => [
                    'profile_title' => 'Snapshot Backend Developer',
                ],
                'cv' => [
                    'original_filename' => 'snapshot-cv.pdf',
                ],
            ],
        ]);

        $this->actingAs($hr);

        Livewire::test(ApplicationPipeline::class)
            ->assertSee('Snapshot Candidate Name')
            ->assertSee('snapshot@example.com')
            ->assertSee('Snapshot Backend Developer')
            ->assertSee('snapshot-cv.pdf')
            ->assertDontSee('updateStatus')
            ->assertDontSee('Current Candidate Name')
            ->assertDontSee('current-candidate@example.com');
    }

    public function test_hr_can_mark_application_as_viewed_from_pipeline(): void
    {
        [$hr, $application] = $this->createPipelineApplication([
            'is_viewed' => false,
            'viewed_at' => null,
        ]);

        $this->actingAs($hr);

        Livewire::test(ApplicationPipeline::class)
            ->call('markAsViewed', $application->id);

        $application->refresh();

        $this->assertTrue($application->is_viewed);
        $this->assertNotNull($application->viewed_at);
    }

    public function test_pipeline_shows_legacy_application_without_branch_id_when_job_is_in_hr_branch(): void
    {
        [$hr] = $this->createPipelineApplication([
            'branch_id' => null,
        ]);

        $this->actingAs($hr);

        Livewire::test(ApplicationPipeline::class)
            ->assertSee('Pipeline Candidate Snapshot')
            ->assertSee('pipeline-cv.pdf');
    }

    public function test_hr_can_advance_application_to_next_pipeline_status(): void
    {
        [$hr, $application] = $this->createPipelineApplication([
            'status' => StatusApplicationEnum::CV_REVIEWING,
        ]);

        $this->actingAs($hr);

        Livewire::test(ApplicationPipeline::class)
            ->call('advanceApplication', $application->id);

        $application->refresh();

        $this->assertSame(StatusApplicationEnum::SCREENING, $application->status);
        $this->assertDatabaseHas('application_status_histories', [
            'application_id' => $application->id,
            'from_status' => StatusApplicationEnum::CV_REVIEWING->value,
            'to_status' => StatusApplicationEnum::SCREENING->value,
            'changed_by_id' => $hr->id,
            'comment' => 'HR chuyển nhanh từ Pipeline.',
        ]);
    }

    public function test_hr_can_reject_application_from_pipeline(): void
    {
        Mail::fake();

        [$hr, $application] = $this->createPipelineApplication([
            'status' => StatusApplicationEnum::CV_REVIEWING,
        ]);

        $this->actingAs($hr);

        Livewire::test(ApplicationPipeline::class)
            ->call('rejectApplication', $application->id);

        $application->refresh();

        $this->assertSame(StatusApplicationEnum::REJECTED, $application->status);
        $this->assertDatabaseHas('application_status_histories', [
            'application_id' => $application->id,
            'from_status' => StatusApplicationEnum::CV_REVIEWING->value,
            'to_status' => StatusApplicationEnum::REJECTED->value,
            'changed_by_id' => $hr->id,
            'comment' => 'HR từ chối nhanh từ Pipeline.',
        ]);
    }

    public function test_hr_cannot_quick_action_application_from_other_branch(): void
    {
        [$hr] = $this->createPipelineApplication();
        [, $otherApplication] = $this->createPipelineApplication(
            branchAttributes: ['name' => 'Other Branch', 'code' => 'OB'],
            userAttributes: ['email' => 'other-hr@example.com']
        );

        $this->actingAs($hr);

        Livewire::test(ApplicationPipeline::class)
            ->call('advanceApplication', $otherApplication->id)
            ->assertForbidden();
    }

    /**
     * @return array{0: User, 1: Application}
     */
    private function createPipelineApplication(
        array $applicationAttributes = [],
        array $branchAttributes = [],
        array $userAttributes = [],
    ): array {
        $branch = Branch::query()->create(array_merge([
            'name' => 'FPT Pipeline Branch',
            'code' => 'PB' . fake()->unique()->numberBetween(100, 999),
            'city' => 'ho_chi_minh',
            'is_active' => true,
        ], $branchAttributes));

        $hr = User::factory()->create(array_merge([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
            'metadata' => ['account_types' => ['employer'], 'account_type' => 'employer'],
        ], $userAttributes));

        $candidate = Candidate::query()->create([
            'name' => 'Pipeline Candidate',
            'email' => 'pipeline-candidate-' . fake()->unique()->numberBetween(1000, 9999) . '@example.com',
            'phone' => '0901234567',
            'cv_file' => 'candidates/current/cv.pdf',
        ]);

        $job = RecruitmentJob::query()->create([
            'title' => 'Pipeline Developer ' . fake()->unique()->numberBetween(100, 999),
            'slug' => 'pipeline-developer-' . fake()->unique()->numberBetween(100, 999),
            'description' => 'Build pipeline.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $hr->id,
        ]);

        $application = Application::query()->create(array_merge([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'cv_path' => 'candidates/snapshot/cv.pdf',
            'apply_method' => 'cv',
            'source' => 'website',
            'status' => StatusApplicationEnum::CV_REVIEWING,
            'branch_id' => $branch->id,
            'is_viewed' => false,
            'profile_snapshot' => [
                'candidate' => [
                    'name' => 'Pipeline Candidate Snapshot',
                    'email' => 'pipeline-snapshot@example.com',
                ],
                'resume' => [
                    'profile_title' => 'Pipeline Backend Developer',
                ],
                'cv' => [
                    'original_filename' => 'pipeline-cv.pdf',
                ],
            ],
        ], $applicationAttributes));

        return [$hr, $application];
    }
}
