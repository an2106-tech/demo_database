<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Candidate;
use App\Models\RecruitmentJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CandidateAccountMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_without_candidate_account_is_redirected_to_activation(): void
    {
        $user = new User([
            'name' => 'HR',
            'email' => 'hr-mw@example.com',
            'role' => 'hr',
            'metadata' => ['account_types' => ['employer']],
        ]);
        $user->id = 10;

        $this->actingAs($user);

        $response = $this->get(route('candidates.submit_resume'));

        $response->assertRedirect();
        $response->assertRedirectToRoute('candidates.register', [
            'next_route' => 'candidates.submit_resume',
        ]);
    }

    public function test_candidate_can_access_candidate_pages(): void
    {
        $user = User::factory()->create([
            'name' => 'Candidate',
            'email' => 'cand-mw@example.com',
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate']],
        ]);

        $this->actingAs($user);

        $this->get(route('candidates.submit_resume'))->assertStatus(200);
    }

    public function test_hr_without_candidate_account_is_redirected_before_apply(): void
    {
        $job = $this->makeJob();
        $user = User::factory()->create([
            'name' => 'HR',
            'email' => 'hr-apply-mw@example.com',
            'role' => 'hr',
            'is_active' => true,
            'metadata' => ['account_types' => ['employer']],
        ]);

        $this->actingAs($user);

        $response = $this->get(route('candidates.apply_job', ['job' => $job]));

        $response->assertRedirectToRoute('candidates.register', [
            'next_route' => 'candidates.apply_job',
        ]);
    }

    public function test_candidate_with_incomplete_profile_is_redirected_before_apply(): void
    {
        $job = $this->makeJob();
        $user = User::factory()->create([
            'name' => 'Candidate Incomplete',
            'email' => 'candidate-incomplete@example.com',
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate'], 'phone' => '0901234567'],
        ]);

        $this->actingAs($user);

        $response = $this->get(route('candidates.apply_job', ['job' => $job]));

        $response->assertRedirect(route('candidates.candidate_profile'));
        $response->assertSessionHas('profile_incomplete');
    }

    public function test_candidate_with_complete_profile_can_open_apply_page(): void
    {
        $job = $this->makeJob();
        $user = User::factory()->create([
            'name' => 'Candidate Complete',
            'email' => 'candidate-complete@example.com',
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate']],
        ]);
        Candidate::query()->create([
            'user_id' => $user->id,
            'name' => 'Candidate Complete',
            'email' => 'candidate-complete@example.com',
            'phone' => '0901234567',
            'cv_file' => 'candidates/complete/cv.pdf',
        ]);

        $this->actingAs($user)
            ->get(route('candidates.apply_job', ['job' => $job]))
            ->assertOk();
    }

    private function makeJob(): RecruitmentJob
    {
        $branch = Branch::query()->create([
            'name' => 'FPT Middleware Branch',
            'code' => 'MW',
            'city' => 'ho_chi_minh',
            'is_active' => true,
        ]);
        $employer = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);

        return RecruitmentJob::query()->create([
            'title' => 'Middleware Developer',
            'slug' => 'middleware-developer-' . uniqid(),
            'description' => 'Build Laravel middleware.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $employer->id,
        ]);
    }
}
