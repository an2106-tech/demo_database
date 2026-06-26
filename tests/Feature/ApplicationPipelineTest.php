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
            ->assertDontSee('Current Candidate Name')
            ->assertDontSee('current-candidate@example.com');
    }
}
