<?php

namespace Tests\Feature;

use App\Livewire\Client\Employers\ChangePassword;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_can_change_password(): void
    {
        $user = User::factory()->create([
            'role' => 'candidate',
            'is_active' => true,
            'password' => Hash::make('old-password'),
            'metadata' => ['account_types' => ['candidate'], 'account_type' => 'candidate'],
        ]);

        Candidate::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '0901234567',
            'cv_file' => 'candidates/demo/cv.pdf',
        ]);

        $this->actingAs($user);

        Livewire::test(ChangePassword::class)
            ->set('current_password', 'old-password')
            ->set('password', 'new-password123')
            ->set('password_confirmation', 'new-password123')
            ->call('updatePassword')
            ->assertHasNoErrors()
            ->assertSet('current_password', '')
            ->assertSet('password', '')
            ->assertSet('password_confirmation', '');

        $this->assertTrue(Hash::check('new-password123', $user->refresh()->password));

        Auth::logout();

        $this->assertTrue(Auth::attempt([
            'email' => $user->email,
            'password' => 'new-password123',
        ]));
    }

    public function test_change_password_rejects_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'role' => 'candidate',
            'is_active' => true,
            'password' => Hash::make('old-password'),
            'metadata' => ['account_types' => ['candidate'], 'account_type' => 'candidate'],
        ]);

        $this->actingAs($user);

        Livewire::test(ChangePassword::class)
            ->set('current_password', 'wrong-password')
            ->set('password', 'new-password123')
            ->set('password_confirmation', 'new-password123')
            ->call('updatePassword')
            ->assertHasErrors(['current_password']);

        $this->assertTrue(Hash::check('old-password', $user->refresh()->password));
    }

    public function test_change_password_requires_confirmation_and_different_password(): void
    {
        $user = User::factory()->create([
            'role' => 'candidate',
            'is_active' => true,
            'password' => Hash::make('old-password'),
            'metadata' => ['account_types' => ['candidate'], 'account_type' => 'candidate'],
        ]);

        $this->actingAs($user);

        Livewire::test(ChangePassword::class)
            ->set('current_password', 'old-password')
            ->set('password', 'new-password123')
            ->set('password_confirmation', 'different-password123')
            ->call('updatePassword')
            ->assertHasErrors(['password']);

        Livewire::test(ChangePassword::class)
            ->set('current_password', 'old-password')
            ->set('password', 'old-password')
            ->set('password_confirmation', 'old-password')
            ->call('updatePassword')
            ->assertHasErrors(['password']);

        $this->assertTrue(Hash::check('old-password', $user->refresh()->password));
    }

    public function test_employer_can_change_password_from_employer_settings(): void
    {
        $branch = Branch::query()->create([
            'name' => 'FPT Polytechnic Account Settings',
            'code' => 'AS',
            'city' => 'ho_chi_minh',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'role' => 'hr',
            'branch_id' => $branch->id,
            'is_active' => true,
            'password' => Hash::make('old-password'),
            'metadata' => ['account_types' => ['employer'], 'account_type' => 'employer'],
        ]);

        $this->actingAs($user);

        $this->get(route('employers.change_password'))
            ->assertOk()
            ->assertSee('wire:submit.prevent="updatePassword"', false)
            ->assertSee('wire:model.defer="current_password"', false);

        Livewire::test(ChangePassword::class)
            ->set('current_password', 'old-password')
            ->set('password', 'secure-password123')
            ->set('password_confirmation', 'secure-password123')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('secure-password123', $user->refresh()->password));
    }
}
