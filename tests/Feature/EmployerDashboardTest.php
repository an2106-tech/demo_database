<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
use App\Livewire\Client\Employers\EmployersDashboard;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\RecruitmentJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EmployerDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_snapshot_candidate_name_in_recent_applications(): void
    {
        $branch = Branch::query()->create([
            'name' => 'Employer Dashboard Branch',
            'code' => 'ED',
            'city' => 'ho_chi_minh',
            'is_active' => true,
        ]);

        $hr = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);

        $candidate = Candidate::query()->create([
            'name' => 'Current Candidate',
            'email' => 'employer-dashboard@example.com',
            'phone' => '0901234567',
            'cv_file' => 'candidates/dashboard/cv.pdf',
        ]);

        $job = RecruitmentJob::query()->create([
            'title' => 'Employer Dashboard Job',
            'slug' => 'employer-dashboard-job',
            'description' => 'Employer dashboard job.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $hr->id,
        ]);

        Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'cv_path' => 'candidates/dashboard/cv.pdf',
            'apply_method' => 'cv',
            'source' => 'website',
            'status' => StatusApplicationEnum::NEW,
            'applied_at' => now()->subHour(),
            'branch_id' => $branch->id,
            'profile_snapshot' => [
                'name' => 'Snapshot Candidate',
                'email' => 'snapshot-dashboard@example.com',
                'candidate' => [
                    'name' => 'Snapshot Candidate',
                    'email' => 'snapshot-dashboard@example.com',
                ],
            ],
        ]);

        $this->actingAs($hr);

        Livewire::test(EmployersDashboard::class)
            ->assertSee('Snapshot Candidate')
            ->assertDontSee('Current Candidate');
    }
}
