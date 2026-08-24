<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\InterviewSchedule;
use App\Filament\Resources\Applications\ApplicationResource;
use App\Models\Interview;
use App\Services\RecruitmentDashboardContext;
use Carbon\CarbonInterface;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class UpcomingInterviews extends Widget
{
    protected static ?int $sort = -2;

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
        $total = (clone $query)->count();

        $interviews = $query
            ->with([
                'application.candidate',
                'application.job.branch',
                'interviewer',
                'workplace',
            ])
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get();

        return [
            'interviews' => $interviews->map(fn (Interview $interview): array => $this->interviewRow($interview, $now))->all(),
            'total' => $total,
            'calendarUrl' => InterviewSchedule::getUrl(),
            'scopeLabel' => $this->scopeLabel(),
        ];
    }

    protected function upcomingQuery(CarbonInterface $now, CarbonInterface $until): Builder
    {
        $context = RecruitmentDashboardContext::current();

        $query = Interview::query()
            ->where('result', 'pending')
            ->whereBetween('scheduled_at', [
                $now->format('Y-m-d H:i:s'),
                $until->format('Y-m-d H:i:s'),
            ]);

        if ($context->branchId()) {
            $query->whereHas('application.job', function (Builder $jobQuery) use ($context): void {
                $jobQuery->where('branch_id', $context->branchId());
            });
        }

        if ($context->isPm()) {
            $query->where('interviewer_id', $context->user()?->getKey());
        }

        return $query;
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

        return [
            'dayLabel' => $dayLabel,
            'date' => $scheduledAt->format('d/m/Y'),
            'time' => $scheduledAt->format('H:i'),
            'candidate' => $application?->snapshotCandidateName() ?? 'Ứng viên',
            'job' => $application?->job?->title ?? 'Chưa có vị trí',
            'branch' => $application?->job?->branch?->name ?? 'Chưa có chi nhánh',
            'interviewer' => $interview->interviewer?->name ?? 'Chưa phân công',
            'type' => $typeLabel,
            'inviteStatus' => $interview->invite_sent_at ? 'Đã gửi lịch' : 'Chưa gửi lịch',
            'inviteColor' => $interview->invite_sent_at ? 'success' : 'warning',
            'url' => $application
                ? ApplicationResource::getUrl('view', ['record' => $application])
                : ApplicationResource::getUrl('index'),
        ];
    }

    protected function scopeLabel(): string
    {
        $context = RecruitmentDashboardContext::current();

        if ($context->isPm()) {
            return 'Lịch được phân công cho bạn trong 7 ngày tới';
        }

        return $context->branchId()
            ? 'Lịch của chi nhánh trong 7 ngày tới'
            : 'Lịch toàn hệ thống trong 7 ngày tới';
    }
}
