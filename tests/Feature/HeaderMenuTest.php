<?php

namespace Tests\Feature;

use App\Livewire\Header;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class HeaderMenuTest extends TestCase
{
    public function test_hr_can_render_employer_header(): void
    {
        $user = new User([
            'name' => 'HR',
            'email' => 'hr@example.com',
            'role' => 'hr',
        ]);
        $user->id = 1;

        $this->actingAs($user);

        Livewire::test(Header::class, ['type' => 'employer'])
            ->assertSet('type', 'employer')
            ->assertViewHas('canEmployerAccess', true);
    }

    public function test_pm_can_render_employer_header(): void
    {
        $user = new User([
            'name' => 'PM',
            'email' => 'pm@example.com',
            'role' => 'pm',
        ]);
        $user->id = 6;

        $this->actingAs($user);

        Livewire::test(Header::class, ['type' => 'employer'])
            ->assertSet('type', 'employer')
            ->assertViewHas('canEmployerAccess', true);
    }

    public function test_candidate_can_render_candidate_header(): void
    {
        $user = new User([
            'name' => 'Candidate',
            'email' => 'candidate@example.com',
            'role' => 'candidate',
        ]);
        $user->id = 2;

        $this->actingAs($user);

        Livewire::test(Header::class, ['type' => 'candidate'])
            ->assertSet('type', 'candidate')
            ->assertViewHas('canCandidateAccess', true);
    }

    public function test_candidate_does_not_get_employer_access_by_switching_type(): void
    {
        $user = new User([
            'name' => 'Candidate',
            'email' => 'candidate-employer@example.com',
            'role' => 'candidate',
        ]);
        $user->id = 3;

        $this->actingAs($user);

        Livewire::test(Header::class, ['type' => 'employer'])
            ->assertSet('type', 'employer')
            ->assertViewHas('canEmployerAccess', false);
    }

    public function test_multi_account_user_can_render_candidate_header(): void
    {
        $user = new User([
            'name' => 'HR Candidate',
            'email' => 'hr-candidate@example.com',
            'role' => 'hr',
            'metadata' => ['account_types' => ['candidate', 'employer']],
        ]);
        $user->id = 4;

        $this->actingAs($user);

        Livewire::test(Header::class, ['type' => 'candidate'])
            ->assertSet('type', 'candidate')
            ->assertViewHas('canCandidateAccess', true);
    }

    public function test_invalid_header_type_falls_back_to_candidate(): void
    {
        $user = new User([
            'name' => 'Candidate',
            'email' => 'candidate-fallback@example.com',
            'role' => 'candidate',
        ]);
        $user->id = 5;

        $this->actingAs($user);

        Livewire::test(Header::class, ['type' => 'unknown'])
            ->assertSet('type', 'candidate')
            ->assertViewHas('canCandidateAccess', true);
    }
}
