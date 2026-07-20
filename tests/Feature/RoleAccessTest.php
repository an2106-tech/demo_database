<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
use App\Filament\Resources\Applications\ApplicationResource;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\RecruitmentJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
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

    public function test_admin_application_permissions_match_recruitment_roles(): void
    {
        $this->prepareApplicationPermissions();

        $branch = Branch::query()->create([
            'name' => 'Greenwich Việt Nam - Cần Thơ',
            'city' => 'can_tho',
            'is_active' => true,
        ]);

        $otherBranch = Branch::query()->create([
            'name' => 'Greenwich Việt Nam - Hồ Chí Minh',
            'city' => 'ho_chi_minh',
            'is_active' => true,
        ]);

        $hr = User::factory()->create(['role' => 'hr', 'branch_id' => $branch->id, 'is_active' => true]);
        $otherHr = User::factory()->create(['role' => 'hr', 'branch_id' => $otherBranch->id, 'is_active' => true]);
        $pm = User::factory()->create(['role' => 'pm', 'branch_id' => $branch->id, 'is_active' => true]);
        $director = User::factory()->create(['role' => 'director', 'branch_id' => $branch->id, 'is_active' => true]);

        $application = $this->makeApplicationForBranch($branch, $hr);

        $this->actingAs($hr);
        $this->assertTrue(ApplicationResource::canCreate());
        $this->assertTrue(ApplicationResource::canEdit($application));
        $this->assertFalse(ApplicationResource::canDelete($application));

        $this->actingAs($otherHr);
        $this->assertFalse(ApplicationResource::canEdit($application));

        foreach ([$pm, $director] as $user) {
            $this->actingAs($user);

            $this->assertFalse(ApplicationResource::canCreate());
            $this->assertFalse(ApplicationResource::canEdit($application));
            $this->assertFalse(ApplicationResource::canDelete($application));
            $this->assertTrue($user->can('view', $application));
        }
    }

    private function prepareApplicationPermissions(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'director', 'pm', 'hr'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        foreach ([
            'ViewAny:Application',
            'View:Application',
            'Create:Application',
            'Update:Application',
            'Delete:Application',
            'DeleteAny:Application',
        ] as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        Role::findByName('hr')->syncPermissions([
            'ViewAny:Application',
            'View:Application',
            'Create:Application',
            'Update:Application',
        ]);

        Role::findByName('pm')->syncPermissions(['ViewAny:Application', 'View:Application']);
        Role::findByName('director')->syncPermissions(['ViewAny:Application', 'View:Application']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function makeApplicationForBranch(Branch $branch, User $createdBy): Application
    {
        $job = RecruitmentJob::query()->create([
            'title' => 'Giảng viên Công nghệ thông tin',
            'slug' => 'giang-vien-cong-nghe-thong-tin-'.uniqid(),
            'description' => 'Tuyển giảng viên phụ trách các học phần lập trình và cơ sở dữ liệu.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'created_by' => $createdBy->id,
        ]);

        $candidate = Candidate::query()->create([
            'name' => 'Nguyễn Minh Khang',
            'email' => 'khang@example.test',
            'phone' => '0900000000',
            'cv_file' => 'cv/khang.pdf',
        ]);

        return Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'branch_id' => $branch->id,
            'cv_path' => 'cv/khang.pdf',
            'apply_method' => 'cv',
            'status' => StatusApplicationEnum::CV_REVIEWING->value,
            'source' => 'website',
        ]);
    }
}
