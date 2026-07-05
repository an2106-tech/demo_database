<?php

namespace App\Filament\Widgets;

use App\Enums\StatusApplicationEnum;
use App\Enums\StatusRecruitmentJobsEnum;
use App\Models\Application;
use App\Models\Candidate;
use App\Models\Interview;
use App\Models\Offer;
use App\Models\RecruitmentJob;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RecruitmentOverviewStats extends StatsOverviewWidget
{
    protected static ?int $sort = -4;

    protected ?string $heading = 'Tổng quan vận hành tuyển dụng';

    protected ?string $description = 'Theo dõi nhanh nhu cầu tuyển dụng, nguồn ứng viên, lịch phỏng vấn và kết quả tuyển dụng.';

    protected int | array | null $columns = [
        'default' => 1,
        'md' => 2,
        'xl' => 3,
    ];

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $now = now();
        $branchId = $this->branchScopeId();

        $openJobs = $this->scopeRecruitmentJobs(RecruitmentJob::query(), $branchId)
            ->where('status', StatusRecruitmentJobsEnum::PUBLISHED->value)
            ->count();

        $newApplications = $this->scopeApplications(Application::query(), $branchId)
            ->where('applied_at', '>=', $now->copy()->subDays(7))
            ->count();

        $newCandidates = $this->scopeCandidates(Candidate::query(), $branchId)
            ->where('created_at', '>=', $now->copy()->subDays(7))
            ->count();

        $upcomingInterviews = $this->scopeInterviews(Interview::query(), $branchId)
            ->where('scheduled_at', '>=', $now)
            ->where('scheduled_at', '<=', $now->copy()->addDays(7))
            ->where('result', 'pending')
            ->count();

        $pendingOffers = $this->scopeOffers(Offer::query(), $branchId)
            ->where('status', 'awaiting_approval')
            ->count();

        $hiredThisMonth = $this->scopeApplications(Application::query(), $branchId)
            ->where('status', StatusApplicationEnum::HIRED->value)
            ->whereBetween('updated_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->count();

        return [
            Stat::make('Tin đang mở', number_format($openJobs))
                ->description('Nhu cầu tuyển dụng đang công khai')
                ->descriptionIcon('heroicon-o-megaphone')
                ->color('success'),
            Stat::make('Hồ sơ mới 7 ngày', number_format($newApplications))
                ->description('Lượng ứng tuyển mới')
                ->descriptionIcon('heroicon-o-inbox')
                ->color('info'),
            Stat::make('Ứng viên mới 7 ngày', number_format($newCandidates))
                ->description('Nguồn ứng viên được bổ sung')
                ->descriptionIcon('heroicon-o-user-plus')
                ->color('gray'),
            Stat::make('Phỏng vấn 7 ngày tới', number_format($upcomingInterviews))
                ->description('Lịch phỏng vấn cần theo dõi')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('warning'),
            Stat::make('Đề nghị chờ duyệt', number_format($pendingOffers))
                ->description('Đang chờ giám đốc chi nhánh')
                ->descriptionIcon('heroicon-o-hand-raised')
                ->color($pendingOffers > 0 ? 'warning' : 'gray'),
            Stat::make('Đã tuyển tháng này', number_format($hiredThisMonth))
                ->description('Kết quả tuyển dụng đã hoàn tất')
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('success'),
        ];
    }

    protected function branchScopeId(): ?int
    {
        return Auth::user()?->branchScopeId();
    }

    protected function scopeRecruitmentJobs(Builder $query, ?int $branchId): Builder
    {
        return $branchId ? $query->where('branch_id', $branchId) : $query;
    }

    protected function scopeApplications(Builder $query, ?int $branchId): Builder
    {
        return $branchId
            ? $query->whereHas('job', fn (Builder $jobQuery): Builder => $jobQuery->where('branch_id', $branchId))
            : $query;
    }

    protected function scopeCandidates(Builder $query, ?int $branchId): Builder
    {
        return $branchId
            ? $query->whereHas('applications.job', fn (Builder $jobQuery): Builder => $jobQuery->where('branch_id', $branchId))
            : $query;
    }

    protected function scopeInterviews(Builder $query, ?int $branchId): Builder
    {
        return $branchId
            ? $query->whereHas('application.job', fn (Builder $jobQuery): Builder => $jobQuery->where('branch_id', $branchId))
            : $query;
    }

    protected function scopeOffers(Builder $query, ?int $branchId): Builder
    {
        return $branchId
            ? $query->whereHas('application.job', fn (Builder $jobQuery): Builder => $jobQuery->where('branch_id', $branchId))
            : $query;
    }
}
