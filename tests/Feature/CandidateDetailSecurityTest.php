<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\RecruitmentJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CandidateDetailSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_branch_scoped_hr_cannot_view_candidate_from_another_branch(): void
    {
        $ownBranch = Branch::query()->create([
            'name' => 'Ha Noi',
            'code' => 'HN',
            'city' => 'Ha Noi',
        ]);
        $otherBranch = Branch::query()->create([
            'name' => 'Ho Chi Minh',
            'code' => 'HCM',
            'city' => 'Ho Chi Minh',
        ]);

        $hr = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $ownBranch->id,
        ]);
        $candidate = Candidate::query()->create([
            'name' => 'Other Branch Candidate',
            'email' => 'candidate@example.com',
        ]);
        $job = RecruitmentJob::query()->create([
            'title' => 'Other Branch Job',
            'slug' => 'other-branch-job',
            'description' => 'Job description',
            'branch_id' => $otherBranch->id,
            'positions_count' => 1,
            'created_by' => $hr->id,
        ]);

        Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'branch_id' => $otherBranch->id,
            'cv_path' => 'cv/other-branch.pdf',
        ]);

        $this->actingAs($hr)
            ->get(route('candidates.candidate_detail', ['id' => $candidate->id]))
            ->assertForbidden();
    }

    public function test_branch_scoped_hr_can_view_candidate_from_own_branch(): void
    {
        $branch = Branch::query()->create([
            'name' => 'Ha Noi',
            'code' => 'HN',
            'city' => 'Ha Noi',
        ]);

        $hr = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);
        $candidate = Candidate::query()->create([
            'name' => 'Own Branch Candidate',
            'email' => 'own-candidate@example.com',
        ]);
        $job = RecruitmentJob::query()->create([
            'title' => 'Own Branch Job',
            'slug' => 'own-branch-job',
            'description' => 'Job description',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $hr->id,
        ]);

        Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'branch_id' => $branch->id,
            'cv_path' => 'cv/own-branch.pdf',
        ]);

        $this->actingAs($hr)
            ->get(route('candidates.candidate_detail', ['id' => $candidate->id]))
            ->assertOk();
    }
}
