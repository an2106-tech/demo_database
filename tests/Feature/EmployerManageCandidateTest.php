<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
use App\Livewire\Client\Employers\ManageCandidate;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\CandidateJobSubmission;
use App\Models\RecruitmentJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EmployerManageCandidateTest extends TestCase
{
    use RefreshDatabase;

    public function test_manage_candidates_page_renders_with_submissions_eager_loaded(): void
    {
        $branch = Branch::query()->create([
            'name' => 'Manage Candidate Branch',
            'code' => 'MC',
            'city' => 'ho_chi_minh',
            'is_active' => true,
        ]);

        $hr = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);

        $candidate = Candidate::query()->create([
            'name' => 'Manage Candidate',
            'email' => 'manage-candidate@example.com',
            'phone' => '0901234567',
            'cv_file' => 'candidates/manage/cv.pdf',
        ]);

        $job = RecruitmentJob::query()->create([
            'title' => 'Manage Candidate Job',
            'slug' => 'manage-candidate-job',
            'description' => 'Manage candidate job.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $hr->id,
        ]);

        $application = Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'cv_path' => 'candidates/manage/cv.pdf',
            'apply_method' => 'cv',
            'source' => 'website',
            'status' => StatusApplicationEnum::NEW,
            'branch_id' => $branch->id,
            'profile_snapshot' => [
                'candidate' => [
                    'name' => 'Manage Candidate',
                    'email' => 'manage-candidate@example.com',
                ],
                'resume' => [
                    'profile_title' => 'Snapshot Developer',
                ],
                'cv' => [
                    'path' => 'candidates/manage/cv.pdf',
                    'original_filename' => 'manage-cv.pdf',
                ],
            ],
        ]);

        CandidateJobSubmission::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'apply_method' => 'cv',
            'profile_snapshot' => $application->profile_snapshot,
            'cv_path' => $application->cv_path,
        ]);

        $this->actingAs($hr);

        Livewire::test(ManageCandidate::class)
            ->assertOk()
            ->assertSee('Quản lý ứng viên')
            ->assertSee('Manage Candidate');
    }
}
