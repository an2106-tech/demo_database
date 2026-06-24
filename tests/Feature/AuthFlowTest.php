<?php

namespace Tests\Feature;

use App\Livewire\Client\Register;
use App\Livewire\Client\ForgotPassword as ForgotPasswordPage;
use App\Livewire\Client\ResetPassword as ResetPasswordPage;
use App\Models\Branch;
use App\Models\Candidate;
use App\Livewire\Client\Login;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
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

    public function test_pm_login_redirects_to_employer_dashboard(): void
    {
        $branch = Branch::query()->create([
            'name' => 'FPT PM Branch',
            'city' => 'ho_chi_minh',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'email' => 'pm-login@example.com',
            'password' => Hash::make('password123'),
            'role' => 'pm',
            'branch_id' => $branch->id,
            'is_active' => true,
            'metadata' => ['account_types' => ['employer']],
        ]);

        Livewire::test(Login::class)
            ->set('email', 'pm-login@example.com')
            ->set('password', 'password123')
            ->call('login')
            ->assertRedirect(route('employers.dashboard'));

        $this->actingAs($user)
            ->get(route('employers.dashboard'))
            ->assertOk();
    }

    public function test_admin_login_redirects_to_admin_panel(): void
    {
        User::factory()->create([
            'email' => 'admin-login@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        Livewire::test(Login::class)
            ->set('email', 'admin-login@example.com')
            ->set('password', 'password123')
            ->call('login')
            ->assertRedirect('/admin');
    }

    public function test_authenticated_admin_is_redirected_away_from_client_login(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin-current-login@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(Login::class)
            ->call('login')
            ->assertRedirect('/admin');
    }

    public function test_legacy_md5_password_is_migrated_on_login(): void
    {
        $user = User::factory()->create([
            'email' => 'legacy-login@example.com',
            'password' => Hash::make('temporary-password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        DB::table('users')
            ->where('id', $user->id)
            ->update(['password' => md5('123456')]);

        Livewire::test(Login::class)
            ->set('email', 'legacy-login@example.com')
            ->set('password', '123456')
            ->call('login')
            ->assertRedirect('/admin');

        $this->assertTrue(Hash::check('123456', $user->refresh()->password));
    }

    public function test_inactive_hr_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'inactive-hr@example.com',
            'password' => Hash::make('password123'),
            'role' => 'hr',
            'is_active' => false,
            'metadata' => ['account_types' => ['employer'], 'approval_status' => 'pending'],
        ]);

        Livewire::test(Login::class)
            ->set('email', 'inactive-hr@example.com')
            ->set('password', 'password123')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_forgot_password_sends_reset_link(): void
    {
        $user = User::factory()->create([
            'email' => 'forgot-password@example.com',
            'password' => Hash::make('old-password'),
            'role' => 'candidate',
            'is_active' => true,
        ]);

        Notification::fake();

        Livewire::test(ForgotPasswordPage::class)
            ->set('email', 'forgot-password@example.com')
            ->call('sendResetLink')
            ->assertHasNoErrors();

        Notification::assertSentTo($user, ResetPasswordNotification::class);

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'forgot-password@example.com',
        ]);
    }

    public function test_reset_password_updates_password_and_redirects_to_login(): void
    {
        $user = User::factory()->create([
            'email' => 'reset-password@example.com',
            'password' => Hash::make('old-password'),
            'role' => 'candidate',
            'is_active' => true,
        ]);

        $token = Password::createToken($user);

        Livewire::test(ResetPasswordPage::class, ['token' => $token])
            ->set('email', 'reset-password@example.com')
            ->set('password', 'new-password123')
            ->set('password_confirmation', 'new-password123')
            ->call('resetPassword')
            ->assertRedirect(route('candidates.login'));

        $this->assertTrue(Hash::check('new-password123', $user->refresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'reset-password@example.com',
        ]);
    }

    public function test_reset_password_rejects_invalid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'invalid-token@example.com',
            'password' => Hash::make('old-password'),
            'role' => 'candidate',
            'is_active' => true,
        ]);

        Livewire::test(ResetPasswordPage::class, ['token' => 'invalid-token'])
            ->set('email', 'invalid-token@example.com')
            ->set('password', 'new-password123')
            ->set('password_confirmation', 'new-password123')
            ->call('resetPassword')
            ->assertHasErrors('email');

        $this->assertTrue(Hash::check('old-password', $user->refresh()->password));
    }

    public function test_employer_registration_waits_for_admin_approval(): void
    {
        $branch = Branch::query()->create([
            'name' => 'FPT Employer Branch',
            'city' => 'ho_chi_minh',
            'is_active' => true,
        ]);

        Livewire::test(Register::class)
            ->set('role', 'employer')
            ->set('name', 'New Employer')
            ->set('email', 'new-employer@example.com')
            ->set('phone', '0900000001')
            ->set('province', 'ho_chi_minh')
            ->set('branch_id', $branch->id)
            ->set('address', '123 Nguyen Trai')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('terms_accepted', true)
            ->call('register')
            ->assertRedirect(route('employers.login'));

        $user = User::query()->where('email', 'new-employer@example.com')->firstOrFail();

        $this->assertSame('hr', $user->role);
        $this->assertFalse($user->is_active);
        $this->assertSame('employer', $user->metadata['account_type']);
        $this->assertSame('pending', $user->metadata['approval_status']);
        $this->assertNotEmpty($user->metadata['requested_at']);
        $this->assertGuest();
    }

    public function test_authenticated_admin_cannot_use_client_registration(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin-current-register@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
            'metadata' => ['account_types' => []],
        ]);

        $this->actingAs($admin);

        Livewire::test(Register::class)
            ->set('role', 'candidate')
            ->call('register')
            ->assertRedirect('/admin');

        $admin->refresh();

        $this->assertSame('admin', $admin->role);
        $this->assertDatabaseMissing('candidates', [
            'user_id' => $admin->id,
        ]);
    }

    public function test_authenticated_admin_opening_register_page_is_sent_to_admin_panel(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin-open-register@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('candidates.register'))
            ->assertRedirect('/admin');
    }

    public function test_registration_rejects_invalid_phone_number(): void
    {
        Livewire::test(Register::class)
            ->set('role', 'candidate')
            ->set('name', 'Invalid Phone')
            ->set('email', 'invalid-phone@example.com')
            ->set('phone', '12345')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('terms_accepted', true)
            ->call('register')
            ->assertHasErrors('phone');
    }

    public function test_employer_registration_rejects_branch_from_different_province(): void
    {
        $branch = Branch::query()->create([
            'name' => 'FPT Ha Noi',
            'city' => 'ha_noi',
            'is_active' => true,
        ]);

        Livewire::test(Register::class)
            ->set('role', 'employer')
            ->set('name', 'Wrong Branch')
            ->set('email', 'wrong-branch@example.com')
            ->set('phone', '0900000002')
            ->set('province', 'ho_chi_minh')
            ->set('branch_id', $branch->id)
            ->set('address', '123 Nguyen Trai')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('terms_accepted', true)
            ->call('register')
            ->assertHasErrors('branch_id');
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
            ->assertRedirect(route('candidates.candidate_profile'));

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
            ->assertRedirect(route('employers.login'));

        $user->refresh();

        $this->assertSame('hr', $user->role);
        $this->assertSame($branch->id, $user->branch_id);
        $this->assertFalse($user->is_active);
        $this->assertContains('candidate', $user->metadata['account_types']);
        $this->assertContains('employer', $user->metadata['account_types']);
        $this->assertSame('employer', $user->metadata['account_type']);
        $this->assertSame('pending', $user->metadata['approval_status']);
        $this->assertNotEmpty($user->metadata['requested_at']);
        $this->assertGuest();
    }
}
