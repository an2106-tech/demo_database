<?php

namespace Tests\Feature;

use App\Livewire\Client\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_login_redirects_to_candidate_dashboard(): void
    {
        User::factory()->create([
            'email' => 'candidate-login@example.com',
            'password' => Hash::make('password123'),
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate']],
        ]);

        Livewire::test(Login::class)
            ->set('email', 'candidate-login@example.com')
            ->set('password', 'password123')
            ->call('login')
            ->assertRedirect(route('candidates.candidate_dashboard'));
    }

    public function test_hr_login_redirects_to_employer_dashboard(): void
    {
        User::factory()->create([
            'email' => 'hr-login@example.com',
            'password' => Hash::make('password123'),
            'role' => 'hr',
            'is_active' => true,
            'metadata' => ['account_types' => ['employer']],
        ]);

        Livewire::test(Login::class)
            ->set('email', 'hr-login@example.com')
            ->set('password', 'password123')
            ->call('login')
            ->assertRedirect(route('employers.dashboard'));
    }

    public function test_inactive_hr_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'inactive-hr@example.com',
            'password' => Hash::make('password123'),
            'role' => 'hr',
            'is_active' => false,
            'metadata' => ['account_types' => ['employer']],
        ]);

        Livewire::test(Login::class)
            ->set('email', 'inactive-hr@example.com')
            ->set('password', 'password123')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }
}
