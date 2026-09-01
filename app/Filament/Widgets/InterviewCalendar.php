<?php

namespace App\Filament\Widgets;

use App\Models\Interview;
use App\Services\InterviewSchedulePresentationService;
use App\Services\RecruitmentDashboardContext;
use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class InterviewCalendar extends CalendarWidget
{
    private ?string $focusDate = null;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 10;

    protected string|HtmlString|bool|null $heading = 'Đối chiếu khung giờ';

    protected int|string|array $columnSpan = 'full';

    protected CalendarViewType $calendarView = CalendarViewType::TimeGridWeek;

    protected ?string $locale = 'vi';

    protected bool $dayMaxEvents = true;

    protected bool $eventClickEnabled = false;

    protected array $options = [
        'allDaySlot' => false,
        'nowIndicator' => true,
        'slotDuration' => '00:15:00',
        'slotLabelInterval' => '01:00:00',
        'slotHeight' => 22,
        'slotEventOverlap' => false,
        'scrollTime' => '08:00:00',   // scroll to 8AM on load, events outside still visible
        'height' => 'clamp(360px, calc(100vh - 18rem), 480px)',
        'expandRows' => false,
        'pointer' => true,
        'stickyHeaderDates' => true,
        'noEventsContent' => 'Không có lịch trong khoảng này.',
        'buttonText' => [
            'today' => 'Hôm nay',
            'timeGridDay' => 'Ngày',
            'timeGridWeek' => 'Tuần',
            'listWeek' => 'Danh sách',
        ],
        'headerToolbar' => [
            'start' => 'prev,next today',
            'center' => 'title',
            'end' => 'timeGridWeek,timeGridDay,listWeek',
        ],
    ];

    public function getOptions(): array
    {
        return [
            ...parent::getOptions(),
            'date' => $this->focusDate ??= $this->resolveInitialDate(),
        ];
    }

    protected function getEvents(FetchInfo $info): Collection|array|Builder
    {
        return $this->getInterviewQuery($info)
            ->get()
            ->map(fn (Interview $interview): CalendarEvent => $this->toCalendarEvent($interview));
    }

    protected function getInterviewQuery(FetchInfo $info): Builder
    {
        return $this->visibleInterviewsQuery()
            ->with(['application.candidate', 'application.job.branch', 'interviewer', 'workplace'])
            ->whereBetween('scheduled_at', [$info->start->toDateTimeString(), $info->end->toDateTimeString()]);
    }

    protected function visibleInterviewsQuery(): Builder
    {
        $context = RecruitmentDashboardContext::current();
        $query = Interview::query();

        if ($context->branchId()) {
            $query->whereHas('application.job', function (Builder $jobQuery) use ($context): void {
                $jobQuery->where('branch_id', $context->branchId());
            });
        }

        if ($context->isPm()) {
            $userId = $context->user()?->getKey();

            $query->where(function (Builder $assignmentQuery) use ($userId): void {
                $assignmentQuery
                    ->where('interviewer_id', $userId)
                    ->orWhereHas(
                        'evaluators',
                        fn (Builder $evaluatorQuery): Builder => $evaluatorQuery->where('user_id', $userId),
                    );
            });
        }

        return $query;
    }

    protected function resolveInitialDate(): string
    {
        $timezone = config('app.interview_timezone', 'Asia/Ho_Chi_Minh');
        $now = now($timezone);
        $weekStart = $now->copy()->startOfWeek();
        $weekEnd = $now->copy()->endOfWeek();
        $query = $this->visibleInterviewsQuery();

        $previous = (clone $query)
            ->where('scheduled_at', '<=', $now->toDateTimeString())
            ->latest('scheduled_at')
            ->first(['scheduled_at']);
        $next = (clone $query)
            ->where('scheduled_at', '>', $now->toDateTimeString())
            ->oldest('scheduled_at')
            ->first(['scheduled_at']);

        if ($previous?->scheduled_at?->between($weekStart, $weekEnd)
            || $next?->scheduled_at?->between($weekStart, $weekEnd)) {
            return $now->toDateString();
        }

        $nearest = match (true) {
            ! $previous => $next,
            ! $next => $previous,
            $previous->scheduled_at->diffInSeconds($now) <= $next->scheduled_at->diffInSeconds($now) => $previous,
            default => $next,
        };

        return $nearest?->scheduled_at?->toDateString() ?? $now->toDateString();
    }

    protected function toCalendarEvent(Interview $interview): CalendarEvent
    {
        $start = $interview->scheduled_at;
        $end = $start->copy()->addMinutes(max(15, (int) ($interview->duration_minutes ?: 60)));
        $presentation = app(InterviewSchedulePresentationService::class)
            ->present($interview, RecruitmentDashboardContext::current()->user());

        $candidateName = $interview->application?->snapshotCandidateName() ?? 'Ứng viên';
        $jobTitle = $interview->application?->job?->title ?? 'Chưa có vị trí';
        $branchName = $interview->application?->job?->branch?->name ?? 'Chưa có chi nhánh';
        $roundLabel = 'Vòng '.max(1, (int) $interview->round_number);
        $compactRoundLabel = 'V'.max(1, (int) $interview->round_number);
        $compactStatus = $this->compactStatus($interview, $presentation['status']);
        $typeLabel = $interview->type === 'online' ? 'Trực tuyến' : 'Tại cơ sở';
        $interviewerName = $interview->interviewer?->name ?? 'Chưa phân công';

        // Keep the native title short; the custom event content carries the visible details.
        $blockTitle = $candidateName.' - '.$jobTitle;

        $event = CalendarEvent::make($interview)
            ->title($blockTitle)
            ->start($start)
            ->end($end)
            ->backgroundColor($presentation['background_color'])
            ->textColor($presentation['text_color'])
            ->classNames(['interview-calendar-event-block'])
            ->extendedProps([
                'candidate' => $candidateName,
                'job' => $jobTitle,
                'round' => $roundLabel,
                'compactRound' => $compactRoundLabel,
                'type' => $typeLabel,
                'status' => $presentation['status'],
                'compactStatus' => $compactStatus,
                'action' => $presentation['action'],
                'tooltip' => implode("\n", [
                    $candidateName,
                    $jobTitle,
                    $roundLabel.' · '.$typeLabel,
                    $presentation['status'].' · '.$presentation['action'],
                    $interviewerName,
                    $branchName,
                ]),
            ]);

        if ($interview->application) {
            $event->url(
                $presentation['url'],
                '_self',
            );
        }

        return $event;
    }

    protected function compactStatus(Interview $interview, string $status): string
    {
        return match ($interview->result) {
            'pass' => 'Đạt',
            'fail' => 'Không đạt',
            default => match ($status) {
                'Lịch nháp quá hạn' => 'Quá hạn',
                'Chưa gửi thư mời' => 'Chưa gửi',
                'Đến hạn đánh giá' => 'Chờ chấm',
                'Đã gửi lịch' => 'Đã gửi',
                default => $status,
            },
        };
    }

    protected function eventContent(): string
    {
        return view('filament.widgets.interview-calendar-event')->render();
    }
}
