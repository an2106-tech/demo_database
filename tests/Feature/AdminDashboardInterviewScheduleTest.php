<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
use App\Filament\Pages\InterviewSchedule;
use App\Filament\Widgets\InterviewCalendar;
use App\Filament\Widgets\InterviewScheduleAgenda;
use App\Filament\Widgets\RecruitmentDistributionChart;
use App\Filament\Widgets\RecruitmentPipelineChart;
use App\Filament\Widgets\RecruitmentRoleOverviewStats;
use App\Filament\Widgets\RecruitmentWorkload;
use App\Filament\Widgets\UpcomingInterviews;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\Interview;
use App\Models\InterviewEvaluator;
use App\Models\RecruitmentJob;
use App\Models\User;
use App\Services\InterviewSchedulePresentationService;
use Carbon\Carbon;
use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDashboardInterviewScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_interview_schedule_page_is_hidden_from_navigation_but_remains_available_by_direct_url(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(InterviewSchedule::getUrl())
            ->assertOk()
            ->assertSee('Lịch phỏng vấn');

        $this->assertFalse(InterviewSchedule::shouldRegisterNavigation());
    }

    public function test_upcoming_interviews_widget_has_a_compact_empty_state(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(UpcomingInterviews::class)
            ->assertSee('Lịch phỏng vấn cần theo dõi')
            ->assertSee('Chưa có lịch phỏng vấn nào trong 7 ngày tới.')
            ->assertSee('Mở Kanban')
            ->assertDontSee('Xem toàn bộ lịch');
    }

    public function test_calendar_event_content_and_toolbar_labels_are_serialized_as_text(): void
    {
        $calendar = new InterviewCalendarProbe;
        $eventContent = $calendar->getEventContentJs();

        $this->assertIsString($eventContent['_default']);
        $this->assertStringContainsString('event.extendedProps.candidate', $eventContent['_default']);
        $this->assertStringContainsString('event.extendedProps.compactRound', $eventContent['_default']);
        $this->assertStringContainsString('event.extendedProps.compactStatus', $eventContent['_default']);
        $this->assertStringContainsString('event.extendedProps.action', $eventContent['_default']);
        $this->assertStringNotContainsString('x-show="view.type', $eventContent['_default']);
        $this->assertSame([
            'today' => 'Hôm nay',
            'timeGridDay' => 'Ngày',
            'timeGridWeek' => 'Tuần',
            'listWeek' => 'Danh sách',
        ], $calendar->getOptions()['buttonText']);
        $this->assertSame(CalendarViewType::TimeGridWeek, $calendar->getCalendarView());
        $this->assertSame('timeGridWeek,timeGridDay,listWeek', $calendar->getOptions()['headerToolbar']['end']);
        $this->assertSame('Không có lịch trong khoảng này.', $calendar->getOptions()['noEventsContent']);
        $this->assertSame('00:15:00', $calendar->getOptions()['slotDuration']);
        $this->assertSame('01:00:00', $calendar->getOptions()['slotLabelInterval']);
        $this->assertSame(22, $calendar->getOptions()['slotHeight']);
        $this->assertFalse($calendar->getOptions()['slotEventOverlap']);
        $this->assertTrue($calendar->getOptions()['pointer']);
        $this->assertSame(-5, UpcomingInterviews::getSort());
        $this->assertSame(
            [InterviewScheduleAgenda::class, InterviewCalendar::class],
            (new InterviewScheduleProbe)->headerWidgetClasses(),
        );
    }

    public function test_pm_sees_co_evaluator_interviews_in_both_schedule_views_with_one_limited_root_query(): void
    {
        $this->travelTo(Carbon::parse('2026-08-31 10:00:00', config('app.interview_timezone')));

        $branch = Branch::query()->create([
            'name' => 'Trường Cao đẳng FPT Polytechnic - Cần Thơ',
            'code' => 'FPTCT-SCHEDULE',
            'city' => 'can_tho',
            'is_active' => true,
        ]);
        $pm = User::factory()->create([
            'name' => 'Người cùng đánh giá',
            'role' => 'pm',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $lead = User::factory()->create([
            'name' => 'Người phụ trách phỏng vấn',
            'role' => 'pm',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $candidate = Candidate::query()->create([
            'name' => 'Ứng viên kiểm thử lịch',
            'email' => 'schedule-candidate@example.com',
        ]);
        $job = RecruitmentJob::query()->create([
            'title' => 'Giảng viên Lập trình Web',
            'slug' => 'giang-vien-lap-trinh-web-schedule-test',
            'description' => 'Kiểm thử lịch phỏng vấn trên dashboard.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $lead->id,
        ]);
        $application = Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'cv_path' => 'candidates/schedule-test.pdf',
            'apply_method' => 'cv',
            'source' => 'website',
            'status' => StatusApplicationEnum::INTERVIEWING,
            'branch_id' => $branch->id,
            'applied_at' => now()->subDay(),
            'profile_snapshot' => ['candidate' => ['name' => $candidate->name]],
        ]);

        $createdInterviews = collect();

        foreach (range(1, 4) as $round) {
            $isDue = $round === 1;
            $interview = Interview::query()->create([
                'application_id' => $application->id,
                'interviewer_id' => $lead->id,
                'round_number' => $round,
                'round_name' => 'Vòng '.$round,
                'scheduled_at' => $isDue
                    ? now(config('app.interview_timezone'))->subHour()
                    : now(config('app.interview_timezone'))->addDay()->addHours($round),
                'duration_minutes' => 60,
                'type' => $round % 2 === 0 ? 'offline' : 'online',
                'invite_sent_at' => $isDue ? now()->subDay() : null,
                'result' => 'pending',
            ]);
            InterviewEvaluator::query()->create([
                'interview_id' => $interview->id,
                'user_id' => $pm->id,
                'role' => 'member',
                'is_required' => true,
                'assigned_at' => now(),
            ]);
            $createdInterviews->push($interview);
        }

        $this->actingAs($pm);
        DB::enableQueryLog();

        $data = (new UpcomingInterviewsProbe)->dashboardData();
        $rootInterviewQueries = collect(DB::getQueryLog())
            ->filter(fn (array $query): bool => str_contains($query['query'], 'from "interviews"'));

        $this->assertCount(3, $data['interviews']);
        $this->assertTrue($data['hasMore']);
        $this->assertSame('Vòng 1', $data['interviews'][0]['round']);
        $this->assertSame('Đến hạn đánh giá', $data['interviews'][0]['status']);
        $this->assertSame('Đánh giá', $data['interviews'][0]['action']);
        $this->assertStringContainsString('queue=interview_overdue', $data['interviews'][0]['url']);
        $this->assertSame('Chưa gửi thư mời', $data['interviews'][1]['status']);
        $this->assertSame('Chờ HR gửi lịch', $data['interviews'][1]['action']);
        $this->assertCount(1, $rootInterviewQueries);
        $this->assertCount(4, (new InterviewCalendarProbe)->visibleInterviewIds());

        $hr = User::factory()->create([
            'role' => 'hr',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $this->actingAs($hr);
        $hrAction = app(InterviewSchedulePresentationService::class)
            ->present($createdInterviews[1]->load('application'), $hr);

        $this->assertSame('Gửi thư mời', $hrAction['action']);
        $this->assertStringContainsString('queue=interview_invite_unsent', $hrAction['url']);

        $pendingCalendarEvent = (new InterviewCalendarProbe)->calendarEvent($createdInterviews[1]);
        $this->assertSame(60, (int) $pendingCalendarEvent->getStart()->diffInMinutes($pendingCalendarEvent->getEnd()));
        $this->assertSame('Chưa gửi', $pendingCalendarEvent->getExtendedProps()['compactStatus']);

        $createdInterviews->each(function (Interview $interview): void {
            $scheduledAt = now(config('app.interview_timezone'))->subDay();

            $interview->update([
                'scheduled_at' => $scheduledAt,
                'actual_ended_at' => $scheduledAt->copy()->addMinutes(10),
                'result' => 'pass',
                'finalized_at' => now(),
            ]);
        });

        $agendaData = (new InterviewScheduleAgendaProbe)->dashboardData();

        $this->assertCount(3, $agendaData['interviews']);
        $this->assertSame('Đã đạt vòng', $agendaData['interviews'][0]['status']);
        $this->assertSame('Xem kết quả', $agendaData['interviews'][0]['action']);
        $calendarOptions = (new InterviewCalendarProbe)->getOptions();

        $this->assertSame('2026-08-30', $calendarOptions['date']);
        $this->assertArrayNotHasKey('initialDate', $calendarOptions);

        $calendarEvent = (new InterviewCalendarProbe)->calendarEvent($createdInterviews->first()->fresh());
        $this->assertSame(60, (int) $calendarEvent->getStart()->diffInMinutes($calendarEvent->getEnd()));
        $this->assertSame('V1', $calendarEvent->getExtendedProps()['compactRound']);
        $this->assertSame('Đạt', $calendarEvent->getExtendedProps()['compactStatus']);
        $this->assertSame('interview-calendar-event-block', $calendarEvent->getClassNames());
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

class UpcomingInterviewsProbe extends UpcomingInterviews
{
    /** @return array<string, mixed> */
    public function dashboardData(): array
    {
        return $this->getViewData();
    }
}

class InterviewCalendarProbe extends InterviewCalendar
{
    public function calendarEvent(Interview $interview): CalendarEvent
    {
        return $this->toCalendarEvent($interview);
    }

    /** @return array<int, int> */
    public function visibleInterviewIds(): array
    {
        $timezone = config('app.interview_timezone', 'Asia/Ho_Chi_Minh');
        $info = new FetchInfo([
            'startStr' => now($timezone)->subDay()->toIso8601String(),
            'endStr' => now($timezone)->addDays(7)->toIso8601String(),
        ]);

        return $this->getInterviewQuery($info)->pluck('id')->all();
    }
}

class InterviewScheduleAgendaProbe extends InterviewScheduleAgenda
{
    /** @return array<string, mixed> */
    public function dashboardData(): array
    {
        return $this->getViewData();
    }
}

class InterviewScheduleProbe extends InterviewSchedule
{
    /** @return array<int, class-string> */
    public function headerWidgetClasses(): array
    {
        return $this->getHeaderWidgets();
    }
}
