<?php

namespace Tests\Feature;

use App\Livewire\Header;
use App\Models\User;
use App\Services\CandidateAccountService;
use App\Models\Candidate;
use Livewire\Livewire;
use Tests\TestCase;

class HeaderMenuTest extends TestCase
{
    public function test_hr_sees_employer_menu_even_if_candidate_type_is_passed(): void
    {
        $user = new User([
            'name' => 'HR',
            'email' => 'hr@example.com',
            'role' => 'hr',
        ]);
        $user->id = 1;

        $this->actingAs($user);

        Livewire::test(Header::class, ['type' => 'candidate'])
            ->assertViewHas('isEmployerHeader', true);
    }

    public function test_candidate_sees_candidate_menu_even_if_employer_type_is_passed(): void
    {
        $user = new User([
            'name' => 'Candidate',
            'email' => 'candidate@example.com',
            'role' => 'candidate',
        ]);
        $user->id = 2;

        $this->actingAs($user);

        Livewire::test(Header::class, ['type' => 'employer'])
            ->assertViewHas('isEmployerHeader', false);
    }

    public function test_pm_sees_candidate_menu(): void
    {
        $user = new User([
            'name' => 'PM',
            'email' => 'pm@example.com',
            'role' => 'pm',
        ]);
        $user->id = 3;

        $this->actingAs($user);

        Livewire::test(Header::class, ['type' => 'employer'])
            ->assertViewHas('isEmployerHeader', false);
    }

    public function test_hr_can_switch_to_candidate_menu_via_session_preference(): void
    {
        $user = new User([
            'name' => 'HR',
            'email' => 'hr2@example.com',
            'role' => 'hr',
            'metadata' => ['account_types' => ['candidate']],
        ]);
        $user->id = 4;

        $this->actingAs($user);
        $this->withSession(['client_menu_type' => 'candidate']);

        Livewire::test(Header::class, ['type' => 'employer'])
            ->assertViewHas('isEmployerHeader', false)
            ->assertViewHas('showRoleSwitcher', true);
    }

    public function test_hr_switching_role_redirects_to_expected_page(): void
    {
        $this->app->instance(CandidateAccountService::class, new class
        {
            public function hasCandidateAccount(User $user): bool
            {
                return true;
            }

            public function activateFor(User $user): Candidate
            {
                return new Candidate();
            }
        });

        $user = new User([
            'name' => 'HR',
            'email' => 'hr3@example.com',
            'role' => 'hr',
            'metadata' => ['account_types' => ['candidate']],
        ]);
        $user->id = 5;

        $this->actingAs($user);

        Livewire::test(Header::class, ['type' => 'employer'])
            ->call('switchTo', 'candidate')
            ->assertRedirect(route('candidates.browse_job'));

        Livewire::test(Header::class, ['type' => 'candidate'])
            ->call('switchTo', 'employer')
            ->assertRedirect(route('auth.post_jobs'));
    }
}
