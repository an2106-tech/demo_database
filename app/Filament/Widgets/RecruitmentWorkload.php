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
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RecruitmentWorkload extends Widget implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

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
                'key' => 'pending_jobs',
                'label' => 'Tin chờ duyệt',
                'description' => 'Kiểm tra và công khai tin tuyển dụng.',
                'count' => $this->scopeRecruitmentJobs(RecruitmentJob::query(), $branchId)
                    ->where('status', StatusRecruitmentJobsEnum::PENDING->value)
                    ->count(),
                'icon' => 'heroicon-o-clock',
                'color' => 'warning',
                'priority' => 'Theo dõi',
                'sort' => 60,
                'url' => RecruitmentJobResource::getUrl('index'),
            ],
            [
                'key' => 'cv_reviewing',
                'label' => 'Chờ sàng lọc CV',
                'description' => 'Xem CV và quyết định bước tiếp theo.',
                'count' => $this->scopeApplications(Application::query(), $branchId)
                    ->where('status', StatusApplicationEnum::CV_REVIEWING->value)
                    ->count(),
                'icon' => 'heroicon-o-document-magnifying-glass',
                'color' => 'gray',
                'priority' => 'Cần xử lý',
                'sort' => 20,
                'url' => ApplicationResource::getUrl('index'),
            ],
            [
                'key' => 'unsent_interviews',
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
                'sort' => 30,
                'url' => ApplicationResource::getUrl('index'),
            ],
            [
                'key' => 'overdue_interviews',
                'label' => 'Chưa chấm phỏng vấn',
                'description' => 'Ghi scorecard để chốt kết quả phỏng vấn.',
                'count' => $this->scopeInterviews(Interview::query(), $branchId)
                    ->where('scheduled_at', '<', $now)
                    ->where('result', 'pending')
                    ->count(),
                'icon' => 'heroicon-o-clipboard-document-check',
                'color' => 'danger',
                'priority' => 'Quá hạn',
                'sort' => 10,
                'url' => ApplicationResource::getUrl('index'),
            ],
            [
                'key' => 'draft_offers',
                'label' => 'Đề nghị nháp',
                'description' => 'Kiểm tra nội dung và gửi giám đốc duyệt.',
                'count' => $this->scopeOffers(Offer::query(), $branchId)
                    ->where('status', 'draft')
                    ->count(),
                'icon' => 'heroicon-o-document-text',
                'color' => 'warning',
                'priority' => 'Cần gửi',
                'sort' => 40,
                'url' => ApplicationResource::getUrl('index'),
            ],
            [
                'key' => 'pending_offers',
                'label' => 'Đề nghị chờ duyệt',
                'description' => 'Theo dõi quyết định của giám đốc chi nhánh.',
                'count' => $this->scopeOffers(Offer::query(), $branchId)
                    ->where('status', 'awaiting_approval')
                    ->count(),
                'icon' => 'heroicon-o-hand-raised',
                'color' => 'warning',
                'priority' => 'Chờ duyệt',
                'sort' => 50,
                'url' => OfferResource::getUrl('index'),
            ],
            [
                'key' => 'expiring_offers',
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
                'sort' => 15,
                'url' => ApplicationResource::getUrl('index'),
            ],
        ];

        $activeItems = collect($items)
            ->filter(fn (array $item): bool => (int) $item['count'] > 0)
            ->sortBy('sort')
            ->values();

        return [
            'items' => $items,
            'activeItems' => $activeItems->all(),
            'primaryItem' => $activeItems->first(),
            'secondaryItems' => $activeItems->slice(1)->values()->all(),
            'idleCount' => collect($items)->filter(fn (array $item): bool => (int) $item['count'] === 0)->count(),
            'totalOpenItems' => collect($items)->sum('count'),
            'scopeLabel' => $branchId ? 'Dữ liệu trong chi nhánh của tài khoản hiện tại' : 'Dữ liệu toàn hệ thống',
        ];
    }

    public function viewWorkloadAction(): Action
    {
        return Action::make('viewWorkload')
            ->label('Xem nhanh')
            ->modalHeading(fn (array $arguments): string => $this->workloadItem($arguments['key'] ?? null)['label'] ?? 'Việc cần xử lý')
            ->modalDescription(fn (array $arguments): ?string => $this->workloadItem($arguments['key'] ?? null)['description'] ?? null)
            ->modalWidth('4xl')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Đóng')
            ->modalContent(fn (array $arguments): View => view('filament.widgets.partials.recruitment-workload-modal', [
                'item' => $this->workloadItem($arguments['key'] ?? null),
                'previewRows' => $this->workloadPreviewRows($arguments['key'] ?? null),
            ]));
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function workloadItem(?string $key): ?array
    {
        if (! is_string($key) || $key === '') {
            return null;
        }

        return collect($this->getViewData()['items'])->firstWhere('key', $key);
    }

    /**
     * @return array<int, array{title: string, description: string, meta: string, status: string, url: string}>
     */
    protected function workloadPreviewRows(?string $key): array
    {
        $now = now();
        $branchId = Auth::user()?->branchScopeId();
        $timezone = config('app.interview_timezone', 'Asia/Ho_Chi_Minh');

        return match ($key) {
            'pending_jobs' => $this->scopeRecruitmentJobs(RecruitmentJob::query(), $branchId)
                ->with(['branch', 'department'])
                ->where('status', StatusRecruitmentJobsEnum::PENDING->value)
                ->latest('updated_at')
                ->limit(6)
                ->get()
                ->map(fn (RecruitmentJob $job): array => [
                    'title' => $job->title,
                    'description' => trim(($job->branch?->name ?? 'Chưa có chi nhánh').' · '.($job->department?->name ?? 'Chưa có phòng ban')),
                    'meta' => $job->deadline ? 'Hạn tuyển: '.$job->deadline->format('d/m/Y') : 'Chưa đặt hạn tuyển',
                    'status' => 'Chờ duyệt',
                    'url' => RecruitmentJobResource::getUrl('view', ['record' => $job]),
                ])
                ->all(),

            'cv_reviewing' => $this->applicationPreviewQuery($branchId)
                ->where('status', StatusApplicationEnum::CV_REVIEWING->value)
                ->latest('applied_at')
                ->limit(6)
                ->get()
                ->map(fn (Application $application): array => $this->applicationPreviewRow($application, 'Chờ sàng lọc CV', $timezone))
                ->all(),

            'unsent_interviews' => $this->interviewPreviewQuery($branchId)
                ->whereNull('invite_sent_at')
                ->where('scheduled_at', '>=', $now)
                ->where('result', 'pending')
                ->orderBy('scheduled_at')
                ->limit(6)
                ->get()
                ->map(fn (Interview $interview): array => $this->interviewPreviewRow($interview, 'Chưa gửi email lịch', $timezone))
                ->all(),

            'overdue_interviews' => $this->interviewPreviewQuery($branchId)
                ->where('scheduled_at', '<', $now)
                ->where('result', 'pending')
                ->orderByDesc('scheduled_at')
                ->limit(6)
                ->get()
                ->map(fn (Interview $interview): array => $this->interviewPreviewRow($interview, 'Quá hạn chấm', $timezone))
                ->all(),

            'draft_offers' => $this->offerPreviewQuery($branchId)
                ->where('status', 'draft')
                ->latest('updated_at')
                ->limit(6)
                ->get()
                ->map(fn (Offer $offer): array => $this->offerPreviewRow($offer, 'Đề nghị nháp', $timezone))
                ->all(),

            'pending_offers' => $this->offerPreviewQuery($branchId)
                ->where('status', 'awaiting_approval')
                ->latest('approval_requested_at')
                ->limit(6)
                ->get()
                ->map(fn (Offer $offer): array => $this->offerPreviewRow($offer, 'Chờ duyệt', $timezone))
                ->all(),

            'expiring_offers' => $this->offerPreviewQuery($branchId)
                ->where('status', 'pending')
                ->whereNotNull('expires_at')
                ->whereBetween('expires_at', [$now, $now->copy()->addDays(2)])
                ->orderBy('expires_at')
                ->limit(6)
                ->get()
                ->map(fn (Offer $offer): array => $this->offerPreviewRow($offer, 'Sắp hết hạn', $timezone))
                ->all(),

            default => [],
        };
    }

    protected function applicationPreviewQuery(?int $branchId): Builder
    {
        return $this->scopeApplications(
            Application::query()->with(['candidate', 'job.branch', 'job.department']),
            $branchId
        );
    }

    protected function interviewPreviewQuery(?int $branchId): Builder
    {
        return $this->scopeInterviews(
            Interview::query()->with(['application.candidate', 'application.job.branch', 'application.job.department']),
            $branchId
        );
    }

    protected function offerPreviewQuery(?int $branchId): Builder
    {
        return $this->scopeOffers(
            Offer::query()->with(['application.candidate', 'application.job.branch', 'application.job.department']),
            $branchId
        );
    }

    /**
     * @return array{title: string, description: string, meta: string, status: string, url: string}
     */
    protected function applicationPreviewRow(Application $application, string $status, string $timezone): array
    {
        return [
            'title' => $application->snapshotCandidateName(),
            'description' => ($application->job?->title ?? 'Chưa có vị trí').' · '.($application->job?->branch?->name ?? 'Chưa có chi nhánh'),
            'meta' => $application->applied_at
                ? 'Ứng tuyển: '.$application->applied_at->copy()->setTimezone($timezone)->format('H:i d/m/Y')
                : 'Chưa có thời gian ứng tuyển',
            'status' => $status,
            'url' => ApplicationResource::getUrl('view', ['record' => $application]),
        ];
    }

    /**
     * @return array{title: string, description: string, meta: string, status: string, url: string}
     */
    protected function interviewPreviewRow(Interview $interview, string $status, string $timezone): array
    {
        $application = $interview->application;

        return [
            'title' => $application?->snapshotCandidateName() ?? 'Ứng viên',
            'description' => ($application?->job?->title ?? 'Chưa có vị trí').' · '.($application?->job?->branch?->name ?? 'Chưa có chi nhánh'),
            'meta' => $interview->scheduled_at
                ? 'Lịch phỏng vấn: '.$interview->scheduled_at->copy()->setTimezone($timezone)->format('H:i d/m/Y')
                : 'Chưa có thời gian phỏng vấn',
            'status' => $status,
            'url' => $application ? ApplicationResource::getUrl('view', ['record' => $application]) : ApplicationResource::getUrl('index'),
        ];
    }

    /**
     * @return array{title: string, description: string, meta: string, status: string, url: string}
     */
    protected function offerPreviewRow(Offer $offer, string $status, string $timezone): array
    {
        $application = $offer->application;
        $date = $offer->expires_at ?: $offer->approval_requested_at ?: $offer->updated_at;

        return [
            'title' => $application?->snapshotCandidateName() ?? 'Ứng viên',
            'description' => ($application?->job?->title ?? 'Chưa có vị trí').' · '.($application?->job?->branch?->name ?? 'Chưa có chi nhánh'),
            'meta' => $date ? 'Cập nhật: '.$date->copy()->setTimezone($timezone)->format('H:i d/m/Y') : 'Chưa có thời gian cập nhật',
            'status' => $status,
            'url' => $application ? ApplicationResource::getUrl('view', ['record' => $application]) : ApplicationResource::getUrl('index'),
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
