<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Applications\ApplicationResource;
use App\Models\Interview;
use App\Services\RecruitmentDashboardContext;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

class InterviewScheduleAgenda extends UpcomingInterviews
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = -10;

    protected string $view = 'filament.widgets.interview-schedule-agenda';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            ...parent::getViewData(),
            'kanbanUrl' => ApplicationResource::getUrl('kanban'),
        ];
    }

    protected function upcomingQuery(CarbonInterface $now, CarbonInterface $until): Builder
    {
        $recentFrom = $now->copy()->subDays(7)->startOfDay();
        $nowValue = $now->format('Y-m-d H:i:s');

        $query = Interview::query()
            ->where(function (Builder $scheduleQuery) use ($recentFrom, $now, $until): void {
                $scheduleQuery
                    ->where(function (Builder $pendingQuery) use ($until): void {
                        $pendingQuery
                            ->where('result', 'pending')
                            ->where('scheduled_at', '<=', $until->format('Y-m-d H:i:s'));
                    })
                    ->orWhere(function (Builder $completedQuery) use ($recentFrom, $now): void {
                        $completedQuery
                            ->whereIn('result', ['pass', 'fail'])
                            ->whereBetween('scheduled_at', [
                                $recentFrom->format('Y-m-d H:i:s'),
                                $now->format('Y-m-d H:i:s'),
                            ]);
                    });
            });

        $this->applyVisibilityScope($query);

        return $query->orderByRaw(
            'CASE WHEN result = ? AND invite_sent_at IS NULL AND scheduled_at < ? THEN 0 '
            .'WHEN result = ? AND invite_sent_at IS NOT NULL AND scheduled_at < ? THEN 1 '
            .'WHEN result = ? THEN 2 ELSE 3 END',
            ['pending', $nowValue, 'pending', $nowValue, 'pending'],
        );
    }

    protected function scopeLabel(): string
    {
        $context = RecruitmentDashboardContext::current();

        if ($context->isPm()) {
            return 'Việc bạn cần xử lý, lịch 7 ngày tới và kết quả gần đây';
        }

        return $context->branchId()
            ? 'Việc cần xử lý, lịch 7 ngày tới và kết quả gần đây của chi nhánh'
            : 'Việc cần xử lý, lịch 7 ngày tới và kết quả gần đây toàn hệ thống';
    }

    protected function applySecondaryOrdering(Builder $query): void
    {
        $query
            ->orderByRaw("CASE WHEN result = 'pending' THEN scheduled_at END ASC")
            ->orderByRaw("CASE WHEN result <> 'pending' THEN scheduled_at END DESC");
    }
}
