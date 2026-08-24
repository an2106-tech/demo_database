<?php

namespace Tests\Feature;

use App\Filament\Pages\InterviewSchedule;
use App\Filament\Widgets\RecruitmentDistributionChart;
use App\Filament\Widgets\RecruitmentPipelineChart;
use App\Filament\Widgets\RecruitmentRoleOverviewStats;
use App\Filament\Widgets\RecruitmentWorkload;
use App\Filament\Widgets\UpcomingInterviews;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDashboardInterviewScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_the_interview_schedule_page(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(InterviewSchedule::getUrl())
            ->assertOk()
            ->assertSee('Lịch phỏng vấn');
    }

    public function test_upcoming_interviews_widget_has_a_compact_empty_state(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(UpcomingInterviews::class)
            ->assertSee('Lịch phỏng vấn sắp tới')
            ->assertSee('Chưa có lịch phỏng vấn nào trong 7 ngày tới.')
            ->assertSee('Xem toàn bộ lịch');
    }

    public function test_hr_dashboard_prioritizes_operational_recruitment_work(): void
    {
        $user = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(RecruitmentRoleOverviewStats::class)
            ->assertSee('Tổng quan tuyển dụng')
            ->assertSee('Chờ sàng lọc CV')
            ->assertSee('Đề nghị đang soạn')
            ->assertDontSee('Tin chờ duyệt')
            ->assertDontSee('Đề nghị chờ duyệt');

        $this->assertSame(
            ['cv_reviewing', 'unsent_interviews', 'overdue_interviews', 'draft_offers', 'expiring_offers'],
            array_column((new RecruitmentWorkloadProbe)->dashboardData()['items'], 'key'),
        );
    }

    public function test_director_dashboard_focuses_on_approval_decisions(): void
    {
        $user = User::factory()->create([
            'role' => 'director',
            'is_active' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(RecruitmentRoleOverviewStats::class)
            ->assertSee('Tổng quan cần phê duyệt')
            ->assertSee('Tin chờ duyệt')
            ->assertSee('Đề nghị chờ duyệt')
            ->assertDontSee('Chờ sàng lọc CV')
            ->assertDontSee('Đề nghị đang soạn');

        $this->assertSame(
            ['pending_jobs', 'pending_offers', 'expiring_offers'],
            array_column((new RecruitmentWorkloadProbe)->dashboardData()['items'], 'key'),
        );
    }

    public function test_pm_dashboard_only_shows_assigned_interview_work(): void
    {
        $user = User::factory()->create([
            'role' => 'pm',
            'is_active' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(RecruitmentRoleOverviewStats::class)
            ->assertSee('Tổng quan phỏng vấn của bạn')
            ->assertSee('Lịch được phân công')
            ->assertSee('Chờ chấm phỏng vấn')
            ->assertSee('Đã hoàn tất tháng này')
            ->assertDontSee('Tin chờ duyệt')
            ->assertDontSee('Chờ sàng lọc CV');

        $this->assertSame(
            ['overdue_interviews'],
            array_column((new RecruitmentWorkloadProbe)->dashboardData()['items'], 'key'),
        );
        $this->assertFalse(RecruitmentPipelineChart::canView());
        $this->assertFalse(RecruitmentDistributionChart::canView());
    }

    public function test_dashboard_resolves_an_assigned_panel_role_without_exposing_system_stats(): void
    {
        Role::query()->create([
            'name' => 'hr',
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'role' => 'candidate',
            'is_active' => true,
        ]);
        $user->assignRole('hr');

        $this->actingAs($user);

        Livewire::test(RecruitmentRoleOverviewStats::class)
            ->assertSee('Chờ sàng lọc CV')
            ->assertDontSee('Ứng viên mới 7 ngày');
    }
}

class RecruitmentWorkloadProbe extends RecruitmentWorkload
{
    /** @return array<string, mixed> */
    public function dashboardData(): array
    {
        return $this->getViewData();
    }
}
