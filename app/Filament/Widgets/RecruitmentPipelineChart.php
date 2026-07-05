<?php

namespace App\Filament\Widgets;

use App\Models\Application;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RecruitmentPipelineChart extends ChartWidget
{
    protected static ?int $sort = -2;

    protected ?string $heading = 'Xu hướng hồ sơ ứng tuyển';

    protected ?string $description = 'Theo dõi lượng hồ sơ mới và kết quả tuyển dụng trong 14 ngày gần nhất.';

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'xl' => 1,
    ];

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $branchId = Auth::user()?->branchScopeId();
        $startDate = now()->subDays(13)->startOfDay();
        $endDate = now()->endOfDay();

        $labels = collect(range(0, 13))
            ->map(fn (int $offset): string => $startDate->copy()->addDays($offset)->format('d/m'))
            ->all();

        $newApplicationsByDay = $this->scopeApplications(Application::query(), $branchId)
            ->whereBetween('applied_at', [$startDate, $endDate])
            ->get(['applied_at'])
            ->groupBy(fn (Application $application): string => $application->applied_at?->format('Y-m-d') ?? '-')
            ->map->count();

        $hiredApplicationsByDay = $this->scopeApplications(Application::query(), $branchId)
            ->where('status', 'hired')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->get(['updated_at'])
            ->groupBy(fn (Application $application): string => $application->updated_at?->format('Y-m-d') ?? '-')
            ->map->count();

        $newApplicationsData = collect(range(0, 13))
            ->map(fn (int $offset): int => (int) ($newApplicationsByDay[$startDate->copy()->addDays($offset)->format('Y-m-d')] ?? 0))
            ->all();

        $hiredApplicationsData = collect(range(0, 13))
            ->map(fn (int $offset): int => (int) ($hiredApplicationsByDay[$startDate->copy()->addDays($offset)->format('Y-m-d')] ?? 0))
            ->all();

        return [
            'datasets' => [
                [
                    'label' => 'Hồ sơ mới',
                    'data' => $newApplicationsData,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.16)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Đã tuyển',
                    'data' => $hiredApplicationsData,
                    'borderColor' => '#16a34a',
                    'backgroundColor' => 'rgba(22, 163, 74, 0.12)',
                    'fill' => false,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function scopeApplications(Builder $query, ?int $branchId): Builder
    {
        return $branchId
            ? $query->whereHas('job', fn (Builder $jobQuery): Builder => $jobQuery->where('branch_id', $branchId))
            : $query;
    }
}
