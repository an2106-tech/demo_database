<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_can_access_candidate_dashboard(): void
    {
        $candidate = User::factory()->create([
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate']],
        ]);

        $this->actingAs($candidate)
            ->get(route('candidates.candidate_dashboard'))
            ->assertOk();
    }

    public function test_candidate_cannot_access_employer_dashboard(): void
    {
        $candidate = User::factory()->create([
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate']],
        ]);

        $this->actingAs($candidate)
            ->get(route('employers.dashboard'))
            ->assertRedirect(route('employers.portal'));
    }

    public function test_candidate_with_employer_metadata_still_cannot_access_employer_dashboard(): void
    {
        $candidate = User::factory()->create([
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate', 'employer'], 'account_type' => 'candidate'],
        ]);

        $this->actingAs($candidate)
            ->get(route('employers.dashboard'))
            ->assertRedirect(route('employers.portal'));
    }

    public function test_inactive_hr_cannot_access_employer_dashboard(): void
    {
        $hr = User::factory()->create([
            'role' => 'hr',
            'is_active' => false,
            'metadata' => ['account_types' => ['employer'], 'account_type' => 'employer', 'approval_status' => 'pending'],
        ]);

        $this->actingAs($hr)
            ->get(route('employers.dashboard'))
            ->assertRedirect(route('employers.login'));
    }

    public function test_hr_without_candidate_account_is_sent_to_candidate_activation(): void
    {
        $hr = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'metadata' => ['account_types' => ['employer'], 'account_type' => 'employer'],
        ]);

        $this->actingAs($hr)
            ->get(route('candidates.candidate_dashboard'))
            ->assertRedirect(route('candidates.register', ['next_route' => 'candidates.candidate_dashboard']));
    }

    public function test_hr_director_and_pm_can_access_employer_dashboard(): void
    {
        $branch = Branch::query()->create([
            'name' => 'FPT Ho Chi Minh',
            'city' => 'ho_chi_minh',
            'is_active' => true,
        ]);

        foreach (['hr', 'director', 'pm'] as $role) {
            $user = User::factory()->create([
                'role' => $role,
                'branch_id' => $branch->id,
                'is_active' => true,
                'metadata' => ['account_types' => ['employer'], 'account_type' => 'employer'],
            ]);

            $this->actingAs($user)
                ->get(route('employers.dashboard'))
                ->assertOk();
        }
    }
}
