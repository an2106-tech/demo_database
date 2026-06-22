<?php

namespace Tests\Feature;

use App\Livewire\Client\Register;
use App\Models\Branch;
use App\Models\Candidate;
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

    public function test_hr_can_activate_candidate_account_on_same_user(): void
    {
        $user = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'metadata' => ['account_types' => ['employer'], 'account_type' => 'employer'],
        ]);

        $this->actingAs($user);

        Livewire::test(Register::class)
            ->set('role', 'candidate')
            ->call('register')
            ->assertRedirect(route('candidates.candidate_dashboard'));

        $user->refresh();

        $this->assertContains('candidate', $user->metadata['account_types']);
        $this->assertDatabaseHas('candidates', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    public function test_candidate_can_activate_employer_account_on_same_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Candidate Owner',
            'email' => 'multi-account@example.com',
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate'], 'account_type' => 'candidate'],
        ]);

        $branch = Branch::query()->create([
            'name' => 'FPT Ho Chi Minh',
            'city' => 'ho_chi_minh',
            'is_active' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(Register::class)
            ->set('role', 'employer')
            ->set('name', 'Candidate Owner')
            ->set('phone', '0900000000')
            ->set('province', 'ho_chi_minh')
            ->set('branch_id', $branch->id)
            ->set('address', '123 Nguyen Van Cu')
            ->set('terms_accepted', true)
            ->call('register')
            ->assertRedirect(route('employers.dashboard'));

        $user->refresh();

        $this->assertSame('hr', $user->role);
        $this->assertSame($branch->id, $user->branch_id);
        $this->assertContains('candidate', $user->metadata['account_types']);
        $this->assertContains('employer', $user->metadata['account_types']);
        $this->assertSame('employer', $user->metadata['account_type']);
    }
}
