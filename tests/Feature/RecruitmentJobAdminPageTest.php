<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
use App\Enums\StatusRecruitmentJobsEnum;
use App\Filament\Resources\RecruitmentJobs\Pages\ListRecruitmentJobs;
use App\Filament\Resources\RecruitmentJobs\Pages\ViewRecruitmentJob;
use App\Filament\Resources\RecruitmentJobs\RecruitmentJobResource;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\Department;
use App\Models\RecruitmentJob;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\Workplace;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RecruitmentJobAdminPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_list_prioritizes_operational_context_and_application_count(): void
    {
        $this->travelTo(Carbon::parse('2026-09-01 09:00:00', config('app.interview_timezone')));
        [$hr, $job] = $this->recruitmentJobContext();
        $this->createApplications($job, 3);

        DB::enableQueryLog();

        $response = $this->actingAs($hr)
            ->get(RecruitmentJobResource::getUrl('index'));

        $response
            ->assertOk()
            ->assertSee('Quản lý tin tuyển dụng')
            ->assertSee($job->title)
            ->assertSee('Bộ môn Công nghệ thông tin')
            ->assertSee('3 hồ sơ')
            ->assertSee('Còn 2 ngày')
            ->assertSee('Thao tác')
            ->assertSee('Xem tin công khai')
            ->assertDontSee('Xem trang ứng viên');

        $statusCountQueries = collect(DB::getQueryLog())
            ->filter(fn (array $query): bool => str_contains(strtolower($query['query']), 'count(*) as aggregate')
                && str_contains(strtolower($query['query']), 'group by "status"'));

        $this->assertCount(1, $statusCountQueries);
    }

    public function test_job_detail_is_a_complete_read_only_review_surface(): void
    {
        $this->travelTo(Carbon::parse('2026-09-01 09:00:00', config('app.interview_timezone')));
        [$hr, $job] = $this->recruitmentJobContext();
        $this->createApplications($job, 2);
        $this->storeInterviewProcessSnapshot($job);

        $this->actingAs($hr)
            ->get(RecruitmentJobResource::getUrl('view', ['record' => $job]))
            ->assertOk()
            ->assertSee('Chi tiết tin tuyển dụng')
            ->assertSee($job->title)
            ->assertSee('Tình trạng tuyển dụng')
            ->assertSee('2 hồ sơ')
            ->assertSee('Đã cố định khi nhận hồ sơ')
            ->assertSee('Nội dung công khai')
            ->assertSee('Thông tin quản lý')
            ->assertSee('Xem tin công khai')
            ->assertDontSee('Xem trang ứng viên')
            ->assertSee('Xây dựng chương trình đào tạo PHP/Laravel')
            ->assertSee('Quy trình phỏng vấn giảng viên')
            ->assertSee('Chuyên môn và giảng dạy')
            ->assertSee('Phù hợp đơn vị')
            ->assertSee('Quản lý chuyên môn')
            ->assertSee('Giám đốc chi nhánh')
            ->assertSee('Scorecard giảng viên');
    }

    public function test_branch_scoped_hr_cannot_open_another_branch_job(): void
    {
        [$hr] = $this->recruitmentJobContext();
        $otherBranch = Branch::query()->create([
            'name' => 'FPT Polytechnic Hà Nội',
            'code' => 'FPTHN-JOB-ADMIN',
            'city' => 'ha_noi',
            'is_active' => true,
        ]);
        $otherCreator = User::factory()->create([
            'role' => 'hr',
            'branch_id' => $otherBranch->id,
            'is_active' => true,
        ]);
        $otherJob = RecruitmentJob::query()->create([
            'title' => 'Tin ngoài phạm vi chi nhánh',
            'slug' => 'tin-ngoai-pham-vi-chi-nhanh',
            'description' => 'Không được phép truy cập.',
            'status' => 'published',
            'branch_id' => $otherBranch->id,
            'positions_count' => 1,
            'created_by' => $otherCreator->id,
        ]);

        $this->actingAs($hr)
            ->get(RecruitmentJobResource::getUrl('view', ['record' => $otherJob]))
            ->assertNotFound();
    }

    public function test_pending_job_cannot_be_edited_through_a_direct_url(): void
    {
        [$hr, $job] = $this->recruitmentJobContext();
        $job->update(['status' => StatusRecruitmentJobsEnum::PENDING]);

        $this->actingAs($hr)
            ->get(RecruitmentJobResource::getUrl('edit', ['record' => $job]))
            ->assertForbidden();
    }

    public function test_published_job_cannot_be_edited_through_a_direct_url(): void
    {
        [$hr, $job] = $this->recruitmentJobContext();

        $this->actingAs($hr)
            ->get(RecruitmentJobResource::getUrl('edit', ['record' => $job]))
            ->assertForbidden();
    }

    public function test_only_an_unused_draft_can_be_deleted(): void
    {
        [$hr, $job] = $this->recruitmentJobContext();
        $this->actingAs($hr);
        $job->update(['status' => StatusRecruitmentJobsEnum::DRAFT]);

        $this->assertTrue(RecruitmentJobResource::canDelete($job));

        $this->createApplications($job, 1);
        $this->assertFalse(RecruitmentJobResource::canDelete($job->fresh()));

        $job->applications()->delete();
        $job->update(['status' => StatusRecruitmentJobsEnum::PUBLISHED]);
        $this->assertFalse(RecruitmentJobResource::canDelete($job->fresh()));
    }

    public function test_hr_uses_recruitment_actions_instead_of_selecting_an_arbitrary_status(): void
    {
        [$hr, $job] = $this->recruitmentJobContext();

        Livewire::actingAs($hr)
            ->test(ListRecruitmentJobs::class)
            ->callTableAction('closeRecruitment', $job)
            ->assertHasNoErrors();

        $this->assertSame(StatusRecruitmentJobsEnum::CLOSED, $job->fresh()->status);

        Livewire::actingAs($hr)
            ->test(ListRecruitmentJobs::class)
            ->callTableAction('reopenRecruitment', $job->fresh())
            ->assertHasNoErrors();

        $this->assertSame(StatusRecruitmentJobsEnum::PUBLISHED, $job->fresh()->status);
    }

    public function test_closed_job_returns_to_draft_before_its_content_can_be_edited(): void
    {
        [$hr, $job] = $this->recruitmentJobContext();
        $job->update(['status' => StatusRecruitmentJobsEnum::CLOSED]);

        $this->actingAs($hr);
        $this->assertFalse(RecruitmentJobResource::canEdit($job));

        Livewire::test(ListRecruitmentJobs::class)
            ->callTableAction('returnToDraft', $job)
            ->assertHasNoErrors();

        $job->refresh();
        $this->assertSame(StatusRecruitmentJobsEnum::DRAFT, $job->status);
        $this->assertTrue(RecruitmentJobResource::canEdit($job));
    }

    public function test_submitting_a_draft_notifies_the_branch_director(): void
    {
        [$hr, $job] = $this->recruitmentJobContext();
        $job->update(['status' => StatusRecruitmentJobsEnum::DRAFT]);
        $director = User::factory()->create([
            'role' => 'director',
            'branch_id' => $job->branch_id,
            'is_active' => true,
        ]);

        Livewire::actingAs($hr)
            ->test(ListRecruitmentJobs::class)
            ->callTableAction('submitForApproval', $job)
            ->assertHasNoErrors();

        $this->assertSame(StatusRecruitmentJobsEnum::PENDING, $job->fresh()->status);
        $notification = UserNotification::query()
            ->where('user_id', $director->id)
            ->where('type', 'job_pending_approval')
            ->first();

        $this->assertNotNull($notification);
        $this->assertSame($job->id, data_get($notification?->data, 'job_id'));
        $this->assertSame('Xem và phê duyệt', data_get($notification?->data, 'action_label'));
    }

    public function test_only_an_approver_can_publish_a_pending_job(): void
    {
        [$hr, $job] = $this->recruitmentJobContext();
        $job->update(['status' => StatusRecruitmentJobsEnum::PENDING]);

        Livewire::actingAs($hr)
            ->test(ListRecruitmentJobs::class)
            ->assertDontSee('Duyệt và đăng');
    }

    public function test_director_can_approve_and_publish_a_pending_job(): void
    {
        [$director, $job] = $this->recruitmentJobContext();
        $job->update(['status' => StatusRecruitmentJobsEnum::PENDING]);

        $director->forceFill(['role' => 'director'])->save();
        $directorRole = Role::firstOrCreate(['name' => 'director', 'guard_name' => 'web']);
        $directorRole->syncPermissions(Permission::query()
            ->whereIn('name', [
                'ViewAny:RecruitmentJob',
                'View:RecruitmentJob',
                'Update:RecruitmentJob',
            ])
            ->get());
        $director->syncRoles([$directorRole]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Livewire::actingAs($director->fresh())
            ->test(ViewRecruitmentJob::class, ['record' => $job->getRouteKey()])
            ->callAction('approveAndPublish')
            ->assertHasNoErrors();

        $this->assertSame(StatusRecruitmentJobsEnum::PUBLISHED, $job->fresh()->status);
    }

    public function test_expired_job_requires_a_new_deadline_when_reopened(): void
    {
        $this->travelTo(Carbon::parse('2026-09-01 09:00:00', config('app.interview_timezone')));
        [$hr, $job] = $this->recruitmentJobContext();
        $job->update([
            'status' => StatusRecruitmentJobsEnum::EXPIRED,
            'deadline' => '2026-08-31',
        ]);

        Livewire::actingAs($hr)
            ->test(ListRecruitmentJobs::class)
            ->callTableAction('extendAndReopen', $job, [
                'deadline' => '2026-09-15',
            ])
            ->assertHasNoErrors();

        $job->refresh();
        $this->assertSame(StatusRecruitmentJobsEnum::PUBLISHED, $job->status);
        $this->assertSame('2026-09-15', $job->deadline?->toDateString());
    }

    /** @return array{User, RecruitmentJob} */
    private function recruitmentJobContext(): array
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permissions = collect([
            'ViewAny:RecruitmentJob',
            'View:RecruitmentJob',
            'Update:RecruitmentJob',
            'Delete:RecruitmentJob',
        ])->map(fn (string $name): Permission => Permission::firstOrCreate([
            'name' => $name,
            'guard_name' => 'web',
        ]));
        $role = Role::firstOrCreate(['name' => 'hr', 'guard_name' => 'web']);
        $role->syncPermissions($permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $branch = Branch::query()->create([
            'name' => 'FPT Polytechnic Cần Thơ',
            'code' => 'FPTCT-JOB-ADMIN',
            'city' => 'can_tho',
            'is_active' => true,
        ]);
        $hr = User::factory()->create([
            'name' => 'HR Cần Thơ',
            'role' => 'hr',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $hr->assignRole($role);
        $department = Department::query()->create([
            'name' => 'Bộ môn Công nghệ thông tin',
            'code' => 'IT-JOB-ADMIN',
            'branch_id' => $branch->id,
        ]);
        $workplace = Workplace::query()->create([
            'branch_id' => $branch->id,
            'name' => 'Tòa nhà FPT Cần Thơ',
            'type' => 'office',
            'is_active' => true,
        ]);
        $job = RecruitmentJob::query()->create([
            'title' => 'Giảng viên Lập trình Web PHP/Laravel',
            'slug' => 'giang-vien-lap-trinh-web-admin-review',
            'description' => '<p>Xây dựng chương trình đào tạo PHP/Laravel và hướng dẫn sinh viên.</p>',
            'status' => 'published',
            'salary_range' => ['min' => 12000000, 'max' => 15000000, 'currency' => 'VND'],
            'deadline' => '2026-09-03',
            'positions_count' => 2,
            'branch_id' => $branch->id,
            'department_id' => $department->id,
            'workplace_id' => $workplace->id,
            'created_by' => $hr->id,
        ]);

        return [$hr, $job];
    }

    private function createApplications(RecruitmentJob $job, int $count): void
    {
        foreach (range(1, $count) as $index) {
            $candidate = Candidate::query()->create([
                'name' => 'Ứng viên '.$index,
                'email' => "job-admin-candidate-{$index}@example.com",
            ]);

            Application::query()->create([
                'job_id' => $job->id,
                'candidate_id' => $candidate->id,
                'cv_path' => "candidates/job-admin-{$index}.pdf",
                'apply_method' => 'cv',
                'source' => 'website',
                'status' => StatusApplicationEnum::CV_REVIEWING,
                'branch_id' => $job->branch_id,
                'applied_at' => now(),
                'profile_snapshot' => ['candidate' => ['name' => $candidate->name]],
            ]);
        }
    }

    private function storeInterviewProcessSnapshot(RecruitmentJob $job): void
    {
        DB::table('recruitment_jobs')
            ->where('id', $job->id)
            ->update([
                'interview_process_snapshot' => json_encode([
                    'version' => 1,
                    'name' => 'Quy trình phỏng vấn giảng viên',
                    'round_count' => 2,
                    'rounds' => [
                        [
                            'round_number' => 1,
                            'name' => 'Chuyên môn và giảng dạy',
                            'objective' => 'Đánh giá kiến thức chuyên môn và năng lực đứng lớp.',
                            'evaluator_roles' => ['pm'],
                            'scorecard_template' => ['name' => 'Scorecard giảng viên'],
                        ],
                        [
                            'round_number' => 2,
                            'name' => 'Phù hợp đơn vị',
                            'objective' => 'Thống nhất khả năng phối hợp và định hướng tuyển dụng.',
                            'evaluator_roles' => ['director'],
                            'scorecard_template' => ['name' => 'Scorecard phù hợp đơn vị'],
                        ],
                    ],
                ], JSON_UNESCAPED_UNICODE),
            ]);
    }
}
