<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
use App\Livewire\Client\CandidateDashboard;
use App\Livewire\Client\Home;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\CandidateResume;
use App\Models\RecruitmentJob;
use App\Models\User;
use App\Services\AiMatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CandidateDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_marks_cv_state_from_candidate_profile(): void
    {
        $user = $this->candidateUser();
        Candidate::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '0901234567',
            'cv_file' => null,
        ]);

        $this->actingAs($user);

        Livewire::test(CandidateDashboard::class)
            ->assertSet('hasCv', false)
            ->assertSet('profileCompletion', 30);
    }

    public function test_dashboard_uses_full_profile_completion(): void
    {
        $user = $this->candidateUser();
        $candidate = Candidate::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '0901234567',
            'cv_file' => 'candidates/demo/cv.pdf',
            'experience_years' => 2,
        ]);
        CandidateResume::query()->create([
            'candidate_id' => $candidate->id,
            'profile_title' => 'Backend Developer',
            'career_objective' => 'Build useful recruitment products.',
            'desired_job' => ['position' => 'Laravel Developer'],
            'educations' => [['school' => 'FPT Polytechnic']],
            'skills' => [['name' => 'Laravel']],
        ]);

        $this->actingAs($user);

        Livewire::test(CandidateDashboard::class)
            ->assertSet('hasCv', true)
            ->assertSet('profileCompletion', 100);
    }

    public function test_dashboard_links_and_counts_guest_applications_by_email(): void
    {
        $branch = Branch::query()->create([
            'name' => 'FPT Dashboard Branch',
            'code' => 'DB',
            'city' => 'ho_chi_minh',
            'is_active' => true,
        ]);
        $employer = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);
        $job = RecruitmentJob::query()->create([
            'title' => 'Dashboard Linked Job',
            'slug' => 'dashboard-linked-job',
            'description' => 'Build dashboard linking.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $employer->id,
        ]);
        $candidate = Candidate::query()->create([
            'name' => 'Dashboard Guest',
            'email' => 'dashboard-guest@example.com',
            'phone' => '0901234567',
            'cv_file' => 'candidates/guest/cv.pdf',
        ]);
        Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'cv_path' => 'candidates/guest/cv.pdf',
            'apply_method' => 'cv',
            'source' => 'website',
            'status' => StatusApplicationEnum::NEW,
            'applied_at' => now()->subDay(),
            'branch_id' => $branch->id,
        ]);
        $user = User::factory()->create([
            'name' => 'Dashboard Guest',
            'email' => 'dashboard-guest@example.com',
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate'], 'account_type' => 'candidate'],
        ]);

        $this->actingAs($user);

        Livewire::test(CandidateDashboard::class)
            ->assertSet('appliedCount', 1)
            ->assertSee('Dashboard Linked Job');

        $this->assertDatabaseHas('candidates', [
            'id' => $candidate->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_home_cta_redirects_candidates_to_dashboard_job_matching(): void
    {
        $user = $this->candidateUser();
        Candidate::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '0901234567',
            'cv_file' => 'candidates/demo/cv.pdf',
        ]);

        $this->actingAs($user);

        Livewire::test(Home::class)
            ->call('openJobMatching')
            ->assertRedirect(route('candidates.candidate_dashboard'));
    }

    public function test_dashboard_auto_runs_job_matching_when_requested_from_home(): void
    {
        $user = $this->candidateUser();
        $candidate = Candidate::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '0901234567',
            'cv_file' => 'candidates/demo/cv.pdf',
        ]);
        CandidateResume::query()->create([
            'candidate_id' => $candidate->id,
            'desired_job' => ['position' => 'Laravel Developer'],
            'skills' => [['name' => 'Laravel']],
        ]);

        $branch = Branch::query()->create([
            'name' => 'Matching Branch',
            'code' => 'MATCH',
            'city' => 'ho_chi_minh',
            'is_active' => true,
        ]);
        $employer = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);
        $job = RecruitmentJob::query()->create([
            'title' => 'Laravel Developer',
            'slug' => 'laravel-developer-match',
            'description' => 'Build Laravel systems.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $employer->id,
        ]);

        $this->mock(AiMatchingService::class, function ($mock) use ($job): void {
            $mock->shouldReceive('matchJobsWithCv')
                ->once()
                ->andReturn([
                    [
                        'job_id' => $job->id,
                        'match_percentage' => 92,
                        'reason' => 'Khớp tiêu đề và kỹ năng chính.',
                        'matched_requirements' => ['Laravel'],
                        'missing_requirements' => [],
                    ],
                ]);
        });

        $this->actingAs($user)
            ->withSession(['run_candidate_job_match' => true]);

        Livewire::test(CandidateDashboard::class)
            ->assertSee('Laravel Developer')
            ->assertSee('Khớp tiêu đề và kỹ năng chính.');
    }

    private function candidateUser(): User
    {
        return User::factory()->create([
            'name' => 'Candidate Dashboard',
            'email' => 'candidate-dashboard@example.com',
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate'], 'account_type' => 'candidate'],
        ]);
    }
}
