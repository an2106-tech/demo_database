<?php

namespace App\Filament\Widgets;

use App\Enums\StatusApplicationEnum;
use App\Enums\StatusRecruitmentJobsEnum;
use App\Filament\Pages\InterviewSchedule;
use App\Filament\Resources\Applications\ApplicationResource;
use App\Filament\Resources\OfferResource;
use App\Filament\Resources\RecruitmentJobs\RecruitmentJobResource;
use App\Models\Application;
use App\Models\Candidate;
use App\Models\Interview;
use App\Models\Offer;
use App\Models\RecruitmentJob;
use App\Services\RecruitmentDashboardContext;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class RecruitmentRoleOverviewStats extends StatsOverviewWidget
{
    protected static ?int $sort = -4;

    protected ?string $pollingInterval = null;

    protected function getHeading(): ?string
    {
        return match (RecruitmentDashboardContext::current()->role()) {
            'director' => 'Tổng quan cần phê duyệt',
            'pm' => 'Tổng quan phỏng vấn của bạn',
            default => 'Tổng quan tuyển dụng',
        };
    }

    protected function getDescription(): ?string
    {
        return RecruitmentDashboardContext::current()->scopeLabel().'.';
    }

    /** @return array<Stat> */
    protected function getStats(): array
    {
        $context = RecruitmentDashboardContext::current();

        return match ($context->role()) {
            'super_admin' => $this->systemStats($context),
            'director' => $this->directorStats($context),
            'pm' => $this->pmStats($context),
            'hr' => $this->hrStats($context),
            default => [],
        };
    }

    /** @return array<Stat> */
    private function systemStats(RecruitmentDashboardContext $context): array
    {
        $counts = $this->sharedCounts($context);

        return [
            $this->openJobsStat($counts['open_jobs']),
            $this->newApplicationsStat($counts['new_applications']),
            Stat::make('Ứng viên mới 7 ngày', number_format($counts['new_candidates']))
                ->description('Nguồn ứng viên được bổ sung')
                ->descriptionIcon('heroicon-o-user-plus')
                ->color('gray'),
            $this->upcomingInterviewsStat($counts['upcoming_interviews']),
            $this->pendingOffersStat($counts['pending_offers']),
            $this->hiredStat($counts['hired_this_month']),
        ];
    }

    /** @return array<Stat> */
    private function hrStats(RecruitmentDashboardContext $context): array
    {
        $now = now();
        $branchId = $context->branchId();
        $applicationCounts = $this->scopeApplications(Application::query(), $branchId)
            ->selectRaw(
                'COUNT(CASE WHEN applied_at >= ? THEN 1 END) as new_applications, COUNT(CASE WHEN status = ? THEN 1 END) as cv_reviewing',
                [$now->copy()->subDays(7), StatusApplicationEnum::CV_REVIEWING->value],
            )
            ->first();
        $upcomingInterviews = $this->scopeInterviews(Interview::query(), $branchId)
            ->whereBetween('scheduled_at', [$now, $now->copy()->addDays(7)])
            ->where('result', 'pending')
            ->count();
        $draftOffers = $this->scopeOffers(Offer::query(), $context->branchId())
            ->where('status', 'draft')
            ->count();
        $newApplications = (int) ($applicationCounts?->new_applications ?? 0);
        $cvReviewing = (int) ($applicationCounts?->cv_reviewing ?? 0);

        return [
            $this->newApplicationsStat($newApplications),
            Stat::make('Chờ sàng lọc CV', number_format($cvReviewing))
                ->description('Hồ sơ cần xem và xử lý')
                ->descriptionIcon('heroicon-o-document-magnifying-glass')
                ->color($cvReviewing > 0 ? 'warning' : 'gray')
                ->url(ApplicationResource::getUrl('kanban')),
            $this->upcomingInterviewsStat($upcomingInterviews),
            Stat::make('Đề nghị đang soạn', number_format($draftOffers))
                ->description('Bản nháp cần kiểm tra và gửi duyệt')
                ->descriptionIcon('heroicon-o-document-text')
                ->color($draftOffers > 0 ? 'warning' : 'gray')
                ->url(ApplicationResource::getUrl('kanban')),
        ];
    }

    /** @return array<Stat> */
    private function directorStats(RecruitmentDashboardContext $context): array
    {
        $now = now();
        $branchId = $context->branchId();
        $pendingJobs = $this->scopeRecruitmentJobs(RecruitmentJob::query(), $branchId)
            ->where('status', StatusRecruitmentJobsEnum::PENDING->value)
            ->count();
        $pendingOffers = $this->scopeOffers(Offer::query(), $branchId)
            ->where('status', 'awaiting_approval')
            ->count();
        $upcomingInterviews = $this->scopeInterviews(Interview::query(), $branchId)
            ->whereBetween('scheduled_at', [$now, $now->copy()->addDays(7)])
            ->where('result', 'pending')
            ->count();
        $hiredThisMonth = $this->scopeApplications(Application::query(), $branchId)
            ->where('status', StatusApplicationEnum::HIRED->value)
            ->whereBetween('updated_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->count();

        return [
            Stat::make('Tin chờ duyệt', number_format($pendingJobs))
                ->description('Tin cần xem xét trước khi công khai')
                ->descriptionIcon('heroicon-o-clock')
                ->color($pendingJobs > 0 ? 'warning' : 'gray')
                ->url(RecruitmentJobResource::getUrl('index')),
            $this->pendingOffersStat($pendingOffers),
            $this->upcomingInterviewsStat($upcomingInterviews),
            $this->hiredStat($hiredThisMonth),
        ];
    }

    /** @return array<Stat> */
    private function pmStats(RecruitmentDashboardContext $context): array
    {
        $now = now();
        $counts = Interview::query()
            ->where('interviewer_id', $context->user()?->getKey())
            ->selectRaw(
                'COUNT(CASE WHEN scheduled_at BETWEEN ? AND ? AND result = ? THEN 1 END) as upcoming, COUNT(CASE WHEN scheduled_at < ? AND result = ? THEN 1 END) as overdue, COUNT(CASE WHEN actual_ended_at BETWEEN ? AND ? AND result IN (?, ?) THEN 1 END) as completed',
                [
                    $now,
                    $now->copy()->addDays(7),
                    'pending',
                    $now,
                    'pending',
                    $now->copy()->startOfMonth(),
                    $now->copy()->endOfMonth(),
                    'pass',
                    'fail',
                ],
            )
            ->first();

        $upcoming = (int) ($counts?->upcoming ?? 0);
        $overdue = (int) ($counts?->overdue ?? 0);
        $completed = (int) ($counts?->completed ?? 0);

        return [
            Stat::make('Lịch được phân công', number_format($upcoming))
                ->description('Phỏng vấn trong 7 ngày tới')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('info')
                ->url(InterviewSchedule::getUrl()),
            Stat::make('Chờ chấm phỏng vấn', number_format($overdue))
                ->description('Buổi đã đến hạn nhưng chưa có kết quả')
                ->descriptionIcon('heroicon-o-clipboard-document-check')
                ->color($overdue > 0 ? 'danger' : 'gray')
                ->url(ApplicationResource::getUrl('index')),
            Stat::make('Đã hoàn tất tháng này', number_format($completed))
                ->description('Buổi phỏng vấn đã ghi nhận kết quả')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success')
                ->url(ApplicationResource::getUrl('index')),
        ];
    }

    /**
     * @return array{open_jobs: int, new_applications: int, new_candidates: int, upcoming_interviews: int, pending_offers: int, hired_this_month: int}
     */
    private function sharedCounts(RecruitmentDashboardContext $context): array
    {
        $now = now();
        $branchId = $context->branchId();
        $applicationCounts = $this->scopeApplications(Application::query(), $branchId)
            ->selectRaw(
                'COUNT(CASE WHEN applied_at >= ? THEN 1 END) as new_applications, COUNT(CASE WHEN status = ? AND updated_at BETWEEN ? AND ? THEN 1 END) as hired_this_month',
                [
                    $now->copy()->subDays(7),
                    StatusApplicationEnum::HIRED->value,
                    $now->copy()->startOfMonth(),
                    $now->copy()->endOfMonth(),
                ],
            )
            ->first();

        return [
            'open_jobs' => $this->scopeRecruitmentJobs(RecruitmentJob::query(), $branchId)
                ->where('status', StatusRecruitmentJobsEnum::PUBLISHED->value)
                ->count(),
            'new_applications' => (int) ($applicationCounts?->new_applications ?? 0),
            'new_candidates' => $this->scopeCandidates(Candidate::query(), $branchId)
                ->where('created_at', '>=', $now->copy()->subDays(7))
                ->count(),
            'upcoming_interviews' => $this->scopeInterviews(Interview::query(), $branchId)
                ->whereBetween('scheduled_at', [$now, $now->copy()->addDays(7)])
                ->where('result', 'pending')
                ->count(),
            'pending_offers' => $this->scopeOffers(Offer::query(), $branchId)
                ->where('status', 'awaiting_approval')
                ->count(),
            'hired_this_month' => (int) ($applicationCounts?->hired_this_month ?? 0),
        ];
    }

    private function openJobsStat(int $count): Stat
    {
        return Stat::make('Tin đang mở', number_format($count))
            ->description('Vị trí đang công khai tuyển dụng')
            ->descriptionIcon('heroicon-o-megaphone')
            ->color('success')
            ->url(RecruitmentJobResource::getUrl('index'));
    }

    private function newApplicationsStat(int $count): Stat
    {
        return Stat::make('Hồ sơ mới 7 ngày', number_format($count))
            ->description('Lượng ứng tuyển mới tiếp nhận')
            ->descriptionIcon('heroicon-o-inbox')
            ->color('info')
            ->url(ApplicationResource::getUrl('kanban'));
    }

    private function upcomingInterviewsStat(int $count): Stat
    {
        return Stat::make('Phỏng vấn 7 ngày tới', number_format($count))
            ->description('Lịch cần theo dõi')
            ->descriptionIcon('heroicon-o-calendar-days')
            ->color('warning')
            ->url(InterviewSchedule::getUrl());
    }

    private function pendingOffersStat(int $count): Stat
    {
        return Stat::make('Đề nghị chờ duyệt', number_format($count))
            ->description('Đang chờ quyết định của giám đốc')
            ->descriptionIcon('heroicon-o-hand-raised')
            ->color($count > 0 ? 'warning' : 'gray')
            ->url(OfferResource::getUrl('index'));
    }

    private function hiredStat(int $count): Stat
    {
        return Stat::make('Đã tuyển tháng này', number_format($count))
            ->description('Hồ sơ đã hoàn tất tuyển dụng')
            ->descriptionIcon('heroicon-o-check-badge')
            ->color('success')
            ->url(ApplicationResource::getUrl('index'));
    }

    private function scopeRecruitmentJobs(Builder $query, ?int $branchId): Builder
    {
        return $branchId ? $query->where('branch_id', $branchId) : $query;
    }

    private function scopeApplications(Builder $query, ?int $branchId): Builder
    {
        return $branchId
            ? $query->whereHas('job', fn (Builder $jobQuery): Builder => $jobQuery->where('branch_id', $branchId))
            : $query;
    }

    private function scopeCandidates(Builder $query, ?int $branchId): Builder
    {
        return $branchId
            ? $query->whereHas('applications.job', fn (Builder $jobQuery): Builder => $jobQuery->where('branch_id', $branchId))
            : $query;
    }

    private function scopeInterviews(Builder $query, ?int $branchId): Builder
    {
        return $branchId
            ? $query->whereHas('application.job', fn (Builder $jobQuery): Builder => $jobQuery->where('branch_id', $branchId))
            : $query;
    }

    private function scopeOffers(Builder $query, ?int $branchId): Builder
    {
        return $branchId
            ? $query->whereHas('application.job', fn (Builder $jobQuery): Builder => $jobQuery->where('branch_id', $branchId))
            : $query;
    }
}
