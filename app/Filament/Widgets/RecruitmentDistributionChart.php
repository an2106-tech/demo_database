<?php

namespace App\Filament\Widgets;

use App\Models\Application;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class RecruitmentDistributionChart extends ChartWidget
{
    protected static ?int $sort = -2;

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'xl' => 1,
    ];

    protected ?string $maxHeight = '300px';

    public function getHeading(): ?string
    {
        return Auth::user()?->branchScopeId()
            ? 'Vị trí có nhiều hồ sơ'
            : 'Hồ sơ theo chi nhánh';
    }

    public function getDescription(): ?string
    {
        return Auth::user()?->branchScopeId()
            ? 'Top vị trí đang có nhiều ứng viên trong chi nhánh.'
            : 'So sánh lượng hồ sơ ứng tuyển giữa các chi nhánh.';
    }

    protected function getData(): array
    {
        $branchId = Auth::user()?->branchScopeId();

        $rows = $branchId
            ? $this->applicationsByJob($branchId)
            : $this->applicationsByBranch();

        return [
            'datasets' => [
                [
                    'label' => 'Hồ sơ',
                    'data' => $rows->pluck('aggregate')->map(fn ($value): int => (int) $value)->all(),
                    'backgroundColor' => '#f59e0b',
                    'borderColor' => '#d97706',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $rows->pluck('label')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function applicationsByBranch()
    {
        return Application::query()
            ->join('recruitment_jobs', 'applications.job_id', '=', 'recruitment_jobs.id')
            ->leftJoin('branches', 'recruitment_jobs.branch_id', '=', 'branches.id')
            ->whereNull('applications.deleted_at')
            ->selectRaw("COALESCE(branches.name, 'Chưa xác định') as label, COUNT(*) as aggregate")
            ->groupBy('branches.id', 'branches.name')
            ->orderByDesc('aggregate')
            ->limit(8)
            ->get();
    }

    protected function applicationsByJob(int $branchId)
    {
        return Application::query()
            ->join('recruitment_jobs', 'applications.job_id', '=', 'recruitment_jobs.id')
            ->whereNull('applications.deleted_at')
            ->where('recruitment_jobs.branch_id', $branchId)
            ->selectRaw("COALESCE(recruitment_jobs.title, 'Chưa xác định') as label, COUNT(*) as aggregate")
            ->groupBy('recruitment_jobs.id', 'recruitment_jobs.title')
            ->orderByDesc('aggregate')
            ->limit(8)
            ->get();
    }
}
