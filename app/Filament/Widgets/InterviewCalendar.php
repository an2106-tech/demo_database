<?php

namespace App\Filament\Widgets;

use App\Models\Interview;
use App\Models\User;
use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class InterviewCalendar extends CalendarWidget
{
    protected string | HtmlString | bool | null $heading = 'Lich phong van';

    protected int | string | array $columnSpan = 'full';

    protected CalendarViewType $calendarView = CalendarViewType::TimeGridWeek;

    protected ?string $locale = 'vi';

    protected bool $dayMaxEvents = true;

    protected bool $eventClickEnabled = false;

    protected array $options = [
        'allDaySlot'     => false,
        'nowIndicator'   => true,
        'slotDuration'   => '00:30:00',
        'scrollTime'     => '08:00:00',   // scroll to 8AM on load, events outside still visible
        'height'         => 'auto',
        'eventMinHeight' => 60,
        'expandRows'     => true,
    ];

    protected function getEvents(FetchInfo $info): Collection | array | Builder
    {
        return $this->getInterviewQuery($info)
            ->get()
            ->map(fn (Interview $interview): CalendarEvent => $this->toCalendarEvent($interview));
    }

    protected function getInterviewQuery(FetchInfo $info): Builder
    {
        /** @var User|null $user */
        $user = Auth::user();

        $query = Interview::query()
            ->with(['application.candidate', 'application.job', 'interviewer', 'workplace'])
            ->whereBetween('scheduled_at', [$info->start->toDateTimeString(), $info->end->toDateTimeString()]);

        if ($user?->branchScopeId()) {
            $query->whereHas('application.job', function (Builder $jobQuery) use ($user): void {
                $jobQuery->where('branch_id', $user->branchScopeId());
            });
        }

        return $query;
    }

    protected function toCalendarEvent(Interview $interview): CalendarEvent
    {
        // Pass UTC Carbon directly – FullCalendar displays in browser local timezone (ICT).
        $start = $interview->scheduled_at;
        $end = $start->copy()->addMinutes(max(15, (int) ($interview->duration_minutes ?: 60)));
        [$backgroundColor, $textColor] = $this->resolveEventColors($interview);

        $candidateName = $interview->application?->candidate?->name ?? 'Ứng viên';
        $jobTitle      = $interview->application?->job?->title ?? 'Chưa có vị trí';

        // Shorter title = less truncation in the fixed-height TimeGrid block.
        // The hover tooltip (injected via JS renderHook) shows the full details.
        $blockTitle = $candidateName . ' - ' . $jobTitle;

        return CalendarEvent::make($interview)
            ->title($blockTitle)
            ->start($start)
            ->end($end)
            ->backgroundColor($backgroundColor)
            ->textColor($textColor);
    }

    protected function resolveEventColors(Interview $interview): array
    {
        return match ($interview->result) {
            'pass' => ['#16a34a', '#f0fdf4'],
            'fail' => ['#dc2626', '#fef2f2'],
            default => $interview->type === 'online'
                ? ['#2563eb', '#eff6ff']
                : ['#475569', '#f8fafc'],
        };
    }
}
