<?php

namespace App\Filament\Widgets;

use App\Enums\StatusApplicationEnum;
use App\Enums\StatusRecruitmentJobsEnum;
use App\Filament\Resources\Applications\ApplicationResource;
use App\Filament\Resources\OfferResource;
use App\Filament\Resources\RecruitmentJobs\RecruitmentJobResource;
use App\Models\Application;
use App\Models\Interview;
use App\Models\Offer;
use App\Models\RecruitmentJob;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RecruitmentWorkload extends Widget
{
    protected static ?int $sort = -3;

    protected string $view = 'filament.widgets.recruitment-workload';

    protected int | string | array $columnSpan = 'full';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $now = now();
        $branchId = Auth::user()?->branchScopeId();

        $items = [
            [
                'label' => 'Tin chờ duyệt',
                'description' => 'Kiểm tra và công khai tin tuyển dụng.',
                'count' => $this->scopeRecruitmentJobs(RecruitmentJob::query(), $branchId)
                    ->where('status', StatusRecruitmentJobsEnum::PENDING->value)
                    ->count(),
                'icon' => 'heroicon-o-clock',
                'color' => 'warning',
                'priority' => 'Theo dõi',
                'url' => RecruitmentJobResource::getUrl('index'),
            ],
            [
                'label' => 'Chờ sàng lọc CV',
                'description' => 'Xem CV và quyết định bước tiếp theo.',
                'count' => $this->scopeApplications(Application::query(), $branchId)
                    ->where('status', StatusApplicationEnum::CV_REVIEWING->value)
                    ->count(),
                'icon' => 'heroicon-o-document-magnifying-glass',
                'color' => 'gray',
                'priority' => 'Cần xử lý',
                'url' => ApplicationResource::getUrl('index'),
            ],
            [
                'label' => 'Lịch chưa gửi',
                'description' => 'Gửi thư mời và file lịch cho người liên quan.',
                'count' => $this->scopeInterviews(Interview::query(), $branchId)
                    ->whereNull('invite_sent_at')
                    ->where('scheduled_at', '>=', $now)
                    ->where('result', 'pending')
                    ->count(),
                'icon' => 'heroicon-o-paper-airplane',
                'color' => 'info',
                'priority' => 'Cần gửi',
                'url' => ApplicationResource::getUrl('index'),
            ],
            [
                'label' => 'Chưa chấm phỏng vấn',
                'description' => 'Ghi scorecard để chốt kết quả phỏng vấn.',
                'count' => $this->scopeInterviews(Interview::query(), $branchId)
                    ->where('scheduled_at', '<', $now)
                    ->where('result', 'pending')
                    ->count(),
                'icon' => 'heroicon-o-clipboard-document-check',
                'color' => 'danger',
                'priority' => 'Quá hạn',
                'url' => ApplicationResource::getUrl('index'),
            ],
            [
                'label' => 'Đề nghị nháp',
                'description' => 'Kiểm tra nội dung và gửi giám đốc duyệt.',
                'count' => $this->scopeOffers(Offer::query(), $branchId)
                    ->where('status', 'draft')
                    ->count(),
                'icon' => 'heroicon-o-document-text',
                'color' => 'warning',
                'priority' => 'Cần gửi',
                'url' => ApplicationResource::getUrl('index'),
            ],
            [
                'label' => 'Đề nghị chờ duyệt',
                'description' => 'Theo dõi quyết định của giám đốc chi nhánh.',
                'count' => $this->scopeOffers(Offer::query(), $branchId)
                    ->where('status', 'awaiting_approval')
                    ->count(),
                'icon' => 'heroicon-o-hand-raised',
                'color' => 'warning',
                'priority' => 'Chờ duyệt',
                'url' => OfferResource::getUrl('index'),
            ],
            [
                'label' => 'Đề nghị sắp hết hạn',
                'description' => 'Ứng viên chưa phản hồi, cần theo dõi hạn.',
                'count' => $this->scopeOffers(Offer::query(), $branchId)
                    ->where('status', 'pending')
                    ->whereNotNull('expires_at')
                    ->whereBetween('expires_at', [$now, $now->copy()->addDays(2)])
                    ->count(),
                'icon' => 'heroicon-o-exclamation-triangle',
                'color' => 'danger',
                'priority' => 'Sắp hết hạn',
                'url' => ApplicationResource::getUrl('index'),
            ],
        ];

        return [
            'items' => $items,
            'activeItems' => collect($items)->filter(fn (array $item): bool => (int) $item['count'] > 0)->values()->all(),
            'idleCount' => collect($items)->filter(fn (array $item): bool => (int) $item['count'] === 0)->count(),
            'totalOpenItems' => collect($items)->sum('count'),
            'scopeLabel' => $branchId ? 'Dữ liệu trong chi nhánh của tài khoản hiện tại' : 'Dữ liệu toàn hệ thống',
        ];
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
