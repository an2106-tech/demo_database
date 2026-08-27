<?php

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\User;
use App\Services\AdminUserManagementGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AdminUserManagementGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_branch_role_requires_an_active_branch(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        try {
            app(AdminUserManagementGuard::class)->normalize($admin, [
                'role' => 'hr',
                'branch_id' => null,
                'is_active' => true,
            ]);
            $this->fail('Expected branch validation to fail.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('branch_id', $exception->errors());
        }
    }

    public function test_director_can_only_assign_hr_or_pm_in_their_branch(): void
    {
        $ownBranch = $this->makeBranch('CT', 'Cần Thơ');
        $otherBranch = $this->makeBranch('HCM', 'Hồ Chí Minh');
        $director = User::factory()->create([
            'role' => 'director',
            'branch_id' => $ownBranch->id,
            'is_active' => true,
        ]);

        $data = app(AdminUserManagementGuard::class)->normalize($director, [
            'role' => 'hr',
            'branch_id' => $otherBranch->id,
            'is_active' => true,
        ]);

        $this->assertSame($ownBranch->id, $data['branch_id']);
        $this->assertSame(['hr', 'pm'], array_keys(
            app(AdminUserManagementGuard::class)->roleOptions($director)
        ));
    }

    public function test_director_cannot_assign_an_administrator_role(): void
    {
        $branch = $this->makeBranch('DN', 'Đà Nẵng');
        $director = User::factory()->create([
            'role' => 'director',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        $this->expectException(ValidationException::class);

        app(AdminUserManagementGuard::class)->normalize($director, [
            'role' => 'admin',
            'branch_id' => null,
            'is_active' => true,
        ]);
    }

    public function test_user_cannot_change_their_own_role_branch_or_active_state(): void
    {
        $branch = $this->makeBranch('HN', 'Hà Nội');
        $otherBranch = $this->makeBranch('CT2', 'Cần Thơ');
        $director = User::factory()->create([
            'role' => 'director',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        $data = app(AdminUserManagementGuard::class)->normalize($director, [
            'role' => 'admin',
            'branch_id' => $otherBranch->id,
            'is_active' => false,
        ], $director);

        $this->assertSame('director', $data['role']);
        $this->assertSame($branch->id, $data['branch_id']);
        $this->assertTrue($data['is_active']);
    }

    public function test_admin_can_open_branch_options_when_editing_a_branch_user(): void
    {
        $activeBranch = $this->makeBranch('CT3', 'Cần Thơ');
        $inactiveBranch = $this->makeBranch('HN2', 'Hà Nội');
        $inactiveBranch->forceFill(['is_active' => false])->save();
        $admin = User::factory()->create([
            'role' => 'admin',
            'branch_id' => null,
            'is_active' => true,
        ]);
        $target = User::factory()->create([
            'role' => 'hr',
            'branch_id' => $inactiveBranch->id,
            'is_active' => true,
        ]);

        $options = app(AdminUserManagementGuard::class)->branchOptions($admin, $target);

        $this->assertArrayHasKey($activeBranch->id, $options);
        $this->assertArrayHasKey($inactiveBranch->id, $options);
    }

    private function makeBranch(string $code, string $city): Branch
    {
        return Branch::query()->create([
            'name' => "FPT Education {$city}",
            'code' => $code,
            'city' => $city,
            'is_active' => true,
        ]);
    }
}
