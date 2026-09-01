<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Applications\ApplicationResource;
use App\Models\Interview;
use App\Services\InterviewSchedulePresentationService;
use App\Services\RecruitmentDashboardContext;
use Carbon\CarbonInterface;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class UpcomingInterviews extends Widget
{
    private const DISPLAY_LIMIT = 3;

    protected static ?int $sort = -5;

    protected string $view = 'filament.widgets.upcoming-interviews';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $timezone = config('app.interview_timezone', 'Asia/Ho_Chi_Minh');
        $now = now($timezone);
        $until = $now->copy()->addDays(7)->endOfDay();
        $query = $this->upcomingQuery($now, $until);

        $query->with([
            'application.candidate',
            'application.job.branch',
            'interviewer',
            'workplace',
        ]);
        $this->applySecondaryOrdering($query);

        $interviews = $query
            ->limit(self::DISPLAY_LIMIT + 1)
            ->get();

        $hasMore = $interviews->count() > self::DISPLAY_LIMIT;

        return [
            'interviews' => $interviews
                ->take(self::DISPLAY_LIMIT)
                ->map(fn (Interview $interview): array => $this->interviewRow($interview, $now))
                ->all(),
            'hasMore' => $hasMore,
            'kanbanUrl' => ApplicationResource::getUrl('kanban'),
            'scopeLabel' => $this->scopeLabel(),
        ];
    }

    protected function upcomingQuery(CarbonInterface $now, CarbonInterface $until): Builder
    {
        $query = Interview::query()
            ->where('result', 'pending')
            ->where('scheduled_at', '<=', $until->format('Y-m-d H:i:s'));

        $this->applyVisibilityScope($query);

        $nowValue = $now->format('Y-m-d H:i:s');

        return $query
            ->orderByRaw(
                'CASE WHEN invite_sent_at IS NULL AND scheduled_at < ? THEN 0 WHEN invite_sent_at IS NOT NULL AND scheduled_at < ? THEN 1 ELSE 2 END',
                [$nowValue, $nowValue],
            );
    }

    protected function applyVisibilityScope(Builder $query): void
    {
        $context = RecruitmentDashboardContext::current();

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
    }

    protected function applySecondaryOrdering(Builder $query): void
    {
        $query->orderBy('scheduled_at');
    }

    /**
     * @return array<string, mixed>
     */
    protected function interviewRow(Interview $interview, CarbonInterface $now): array
    {
        $scheduledAt = $interview->scheduled_at;
        $application = $interview->application;
        $dayDifference = (int) $now->copy()->startOfDay()->diffInDays($scheduledAt->copy()->startOfDay(), false);

        $dayLabel = match ($dayDifference) {
            0 => 'Hôm nay',
            1 => 'Ngày mai',
            default => $scheduledAt->format('d/m'),
        };

        $typeLabel = match ($interview->type) {
            'online' => 'Trực tuyến',
            'offline' => 'Tại cơ sở',
            default => 'Chưa xác định',
        };
        $presentation = app(InterviewSchedulePresentationService::class)
            ->present($interview, RecruitmentDashboardContext::current()->user());

        return [
            'dayLabel' => $dayLabel,
            'date' => $scheduledAt->format('d/m/Y'),
            'time' => $scheduledAt->format('H:i'),
            'candidate' => $application?->snapshotCandidateName() ?? 'Ứng viên',
            'job' => $application?->job?->title ?? 'Chưa có vị trí',
            'branch' => $application?->job?->branch?->name ?? 'Chưa có chi nhánh',
            'interviewer' => $interview->interviewer?->name ?? 'Chưa phân công',
            'round' => 'Vòng '.max(1, (int) $interview->round_number),
            'type' => $typeLabel,
            'status' => $presentation['status'],
            'action' => $presentation['action'],
            'statusColor' => $presentation['badge_color'],
            'url' => $presentation['url'],
        ];
    }

    protected function scopeLabel(): string
    {
        $context = RecruitmentDashboardContext::current();

        if ($context->isPm()) {
            return 'Ưu tiên lịch bạn cần xử lý, sau đó là lịch 7 ngày tới';
        }

        return $context->branchId()
            ? 'Ưu tiên lịch cần xử lý của chi nhánh, sau đó là lịch 7 ngày tới'
            : 'Ưu tiên lịch cần xử lý toàn hệ thống, sau đó là lịch 7 ngày tới';
    }
}
