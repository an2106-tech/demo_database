<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
use App\Filament\Resources\Candidates\CandidateResource;
use App\Filament\Resources\Candidates\Pages\ViewCandidate;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\CandidateResume;
use App\Models\RecruitmentJob;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CandidateAdminPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_branch_hr_only_sees_scoped_application_count_latest_stage_and_history(): void
    {
        $this->travelTo(Carbon::parse('2026-09-01 09:00:00', config('app.interview_timezone')));
        [$hr, $candidate, $ownJob, $otherJob] = $this->branchCandidateContext();

        $this->createCandidateApplication($candidate, $ownJob, StatusApplicationEnum::SCREENING, '2026-08-30 09:00:00', 'own-branch.pdf');
        $this->createCandidateApplication($candidate, $otherJob, StatusApplicationEnum::HIRED, '2026-08-31 09:00:00', 'other-branch.pdf');

        $this->actingAs($hr)
            ->get(CandidateResource::getUrl('index'))
            ->assertOk()
            ->assertSee($candidate->name)
            ->assertSee($ownJob->title)
            ->assertSee('1 lượt')
            ->assertDontSee($otherJob->title);

        $this->actingAs($hr)
            ->get(CandidateResource::getUrl('view', ['record' => $candidate]))
            ->assertOk()
            ->assertSee($candidate->name)
            ->assertSee('1 lượt ứng tuyển')
            ->assertSee($ownJob->title)
            ->assertSee('Sơ tuyển')
            ->assertSee('CV hồ sơ hiện tại')
            ->assertSee('CV đã nộp')
            ->assertDontSee($otherJob->title)
            ->assertDontSee('Đã tuyển')
            ->assertDontSee('other-branch.pdf');
    }

    public function test_branch_hr_cannot_edit_candidate_master_profile_through_direct_url(): void
    {
        [$hr, $candidate, $ownJob] = $this->branchCandidateContext();
        $this->createCandidateApplication($candidate, $ownJob, StatusApplicationEnum::CV_REVIEWING, now()->toDateTimeString());

        $this->actingAs($hr)
            ->get(CandidateResource::getUrl('edit', ['record' => $candidate]))
            ->assertForbidden();
    }

    public function test_soft_deleted_candidate_cannot_be_opened_through_direct_url(): void
    {
        [$hr, $candidate, $ownJob] = $this->branchCandidateContext();
        $this->createCandidateApplication($candidate, $ownJob, StatusApplicationEnum::CV_REVIEWING, now()->toDateTimeString());
        $candidate->delete();

        $this->actingAs($hr)
            ->get(CandidateResource::getUrl('view', ['record' => $candidate->id]))
            ->assertNotFound();
    }

    public function test_super_admin_can_record_and_clear_recruitment_restriction_with_audit_data(): void
    {
        $admin = $this->adminUser('super_admin');
        $candidate = Candidate::query()->create([
            'name' => 'Ứng viên cần lưu ý',
            'email' => 'restricted-candidate@example.com',
        ]);

        Livewire::actingAs($admin)
            ->test(ViewCandidate::class, ['record' => $candidate->getRouteKey()])
            ->callAction('restrictRecruitment', ['reason' => 'Có sai lệch thông tin cần xác minh.'])
            ->assertHasNoErrors();

        $candidate->refresh();
        $this->assertTrue($candidate->blacklist);
        $this->assertSame('Có sai lệch thông tin cần xác minh.', $candidate->blacklist_reason);
        $this->assertSame($admin->id, $candidate->blacklisted_by);
        $this->assertNotNull($candidate->blacklisted_at);
        $this->assertSame('restricted', data_get($candidate->metadata, 'recruitment_restriction_history.0.action'));

        Livewire::actingAs($admin)
            ->test(ViewCandidate::class, ['record' => $candidate->getRouteKey()])
            ->callAction('clearRecruitmentRestriction', ['reason' => 'Đã xác minh thông tin hợp lệ.'])
            ->assertHasNoErrors();

        $candidate->refresh();
        $this->assertFalse($candidate->blacklist);
        $this->assertNull($candidate->blacklist_reason);
        $this->assertNull($candidate->blacklisted_by);
        $this->assertNull($candidate->blacklisted_at);
        $this->assertSame('cleared', data_get($candidate->metadata, 'recruitment_restriction_history.1.action'));
    }

    /** @return array{User, Candidate, RecruitmentJob, RecruitmentJob} */
    protected function branchCandidateContext(): array
    {
        $hr = $this->adminUser('hr');
        $ownBranch = Branch::query()->create([
            'name' => 'FPT Polytechnic Cần Thơ',
            'code' => 'CT-CANDIDATE-ADMIN',
            'city' => 'can_tho',
            'is_active' => true,
        ]);
        $otherBranch = Branch::query()->create([
            'name' => 'FPT Polytechnic Hà Nội',
            'code' => 'HN-CANDIDATE-ADMIN',
            'city' => 'ha_noi',
            'is_active' => true,
        ]);
        $hr->forceFill(['branch_id' => $ownBranch->id])->save();
        $candidate = Candidate::query()->create([
            'name' => 'Nguyễn Minh Khang',
            'email' => 'minh-khang@example.com',
            'phone' => '0909000111',
            'cv_file' => 'candidates/current-profile.pdf',
            'experience_years' => 3,
        ]);
        CandidateResume::query()->create([
            'candidate_id' => $candidate->id,
            'profile_title' => 'Giảng viên Lập trình Web',
            'career_objective' => 'Phát triển chuyên môn và năng lực giảng dạy.',
            'skills' => [['name' => 'PHP'], ['name' => 'Laravel']],
            'experiences' => [['company' => 'FPT']],
            'educations' => [['school' => 'FPT Polytechnic']],
        ]);
        $ownJob = $this->job($ownBranch, $hr, 'Giảng viên PHP tại Cần Thơ', 'candidate-own-job');
        $otherJob = $this->job($otherBranch, $hr, 'Giảng viên Java tại Hà Nội', 'candidate-other-job');

        return [$hr->fresh(), $candidate, $ownJob, $otherJob];
    }

    protected function adminUser(string $roleName): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permissions = collect([
            'ViewAny:Candidate',
            'View:Candidate',
            'Update:Candidate',
            'View:Application',
        ])->map(fn (string $name): Permission => Permission::firstOrCreate([
            'name' => $name,
            'guard_name' => 'web',
        ]));
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $role->syncPermissions($permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::factory()->create([
            'role' => $roleName === 'super_admin' ? 'admin' : $roleName,
            'is_active' => true,
        ]);
        $user->syncRoles([$role]);

        return $user->fresh();
    }

    protected function job(Branch $branch, User $creator, string $title, string $slug): RecruitmentJob
    {
        return RecruitmentJob::query()->create([
            'title' => $title,
            'slug' => $slug,
            'description' => 'Mô tả tuyển dụng.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $creator->id,
        ]);
    }

    protected function createCandidateApplication(
        Candidate $candidate,
        RecruitmentJob $job,
        StatusApplicationEnum $status,
        string $appliedAt,
        string $cvPath = 'candidates/submitted.pdf',
    ): Application {
        return Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'branch_id' => $job->branch_id,
            'cv_path' => $cvPath,
            'apply_method' => 'cv',
            'source' => 'website',
            'status' => $status,
            'applied_at' => $appliedAt,
            'profile_snapshot' => ['candidate' => ['name' => $candidate->name]],
        ]);
    }
}
