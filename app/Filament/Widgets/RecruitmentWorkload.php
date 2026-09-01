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
use App\Services\RecruitmentDashboardContext;
use Carbon\CarbonInterface;
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

    protected static ?int $sort = -2;

    protected string $view = 'filament.widgets.recruitment-workload';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $now = now();
        $context = RecruitmentDashboardContext::current();
        $branchId = $context->branchId();
        $counts = $this->workloadCounts($branchId, $now);

        $items = [
            [
                'key' => 'pending_jobs',
                'label' => 'Tin chờ duyệt',
                'description' => 'Kiểm tra và công khai tin tuyển dụng.',
                'count' => $counts['pending_jobs'],
                'icon' => 'heroicon-o-clock',
                'color' => 'warning',
                'priority' => 'Theo dõi',
                'sort' => 60,
                'roles' => ['super_admin', 'director'],
                'url' => RecruitmentJobResource::getUrl('index'),
            ],
            [
                'key' => 'cv_reviewing',
                'label' => 'Chờ sàng lọc CV',
                'description' => 'Xem CV và quyết định bước tiếp theo.',
                'count' => $counts['cv_reviewing'],
                'icon' => 'heroicon-o-document-magnifying-glass',
                'color' => 'gray',
                'priority' => 'Cần xử lý',
                'sort' => 20,
                'roles' => ['super_admin', 'hr'],
                'url' => ApplicationResource::getUrl('kanban'),
            ],
            [
                'key' => 'unsent_interviews',
                'label' => 'Lịch chưa gửi',
                'description' => 'Gửi thư mời và file lịch cho người liên quan.',
                'count' => $counts['unsent_interviews'],
                'icon' => 'heroicon-o-paper-airplane',
                'color' => 'info',
                'priority' => 'Cần gửi',
                'sort' => 30,
                'roles' => ['super_admin', 'hr'],
                'url' => ApplicationResource::getUrl('kanban'),
            ],
            [
                'key' => 'overdue_interviews',
                'label' => 'Chưa chấm phỏng vấn',
                'description' => 'Ghi scorecard để chốt kết quả phỏng vấn.',
                'count' => $counts['overdue_interviews'],
                'icon' => 'heroicon-o-clipboard-document-check',
                'color' => 'danger',
                'priority' => 'Quá hạn',
                'sort' => 10,
                'roles' => ['super_admin', 'hr', 'pm'],
                'url' => ApplicationResource::getUrl('kanban'),
            ],
            [
                'key' => 'draft_offers',
                'label' => 'Đề nghị nháp',
                'description' => 'Kiểm tra nội dung và gửi giám đốc duyệt.',
                'count' => $counts['draft_offers'],
                'icon' => 'heroicon-o-document-text',
                'color' => 'warning',
                'priority' => 'Cần gửi',
                'sort' => 40,
                'roles' => ['super_admin', 'hr'],
                'url' => ApplicationResource::getUrl('kanban'),
            ],
            [
                'key' => 'pending_offers',
                'label' => 'Đề nghị chờ duyệt',
                'description' => 'Theo dõi quyết định của giám đốc chi nhánh.',
                'count' => $counts['pending_offers'],
                'icon' => 'heroicon-o-hand-raised',
                'color' => 'warning',
                'priority' => 'Chờ duyệt',
                'sort' => 50,
                'roles' => ['super_admin', 'director'],
                'url' => OfferResource::getUrl('index'),
            ],
            [
                'key' => 'expiring_offers',
                'label' => 'Đề nghị sắp hết hạn',
                'description' => 'Ứng viên chưa phản hồi, cần theo dõi hạn.',
                'count' => $counts['expiring_offers'],
                'icon' => 'heroicon-o-exclamation-triangle',
                'color' => 'danger',
                'priority' => 'Sắp hết hạn',
                'sort' => 15,
                'roles' => ['super_admin', 'hr', 'director'],
                'url' => ApplicationResource::getUrl('kanban'),
            ],
        ];

        $visibleItems = collect($items)
            ->filter(fn (array $item): bool => in_array($context->role(), $item['roles'], true))
            ->values();

        $activeItems = $visibleItems
            ->filter(fn (array $item): bool => (int) $item['count'] > 0)
            ->sortBy('sort')
            ->values();

        return [
            'items' => $visibleItems->all(),
            'activeItems' => $activeItems->all(),
            'totalOpenItems' => $visibleItems->sum('count'),
            'scopeLabel' => $context->scopeLabel(),
        ];
    }

    /**
     * @return array{pending_jobs: int, cv_reviewing: int, unsent_interviews: int, overdue_interviews: int, draft_offers: int, pending_offers: int, expiring_offers: int}
     */
    protected function workloadCounts(?int $branchId, CarbonInterface $now): array
    {
        $context = RecruitmentDashboardContext::current();
        $counts = [
            'pending_jobs' => 0,
            'cv_reviewing' => 0,
            'unsent_interviews' => 0,
            'overdue_interviews' => 0,
            'draft_offers' => 0,
            'pending_offers' => 0,
            'expiring_offers' => 0,
        ];

        if ($context->is('super_admin', 'director')) {
            $jobCounts = $this->scopeRecruitmentJobs(RecruitmentJob::query(), $branchId)
                ->selectRaw('COUNT(CASE WHEN status = ? THEN 1 END) as pending_jobs', [StatusRecruitmentJobsEnum::PENDING->value])
                ->first();
            $counts['pending_jobs'] = (int) ($jobCounts?->pending_jobs ?? 0);
        }

        if ($context->is('super_admin', 'hr')) {
            $applicationCounts = $this->scopeApplications(Application::query(), $branchId)
                ->selectRaw('COUNT(CASE WHEN status = ? THEN 1 END) as cv_reviewing', [StatusApplicationEnum::CV_REVIEWING->value])
                ->first();
            $counts['cv_reviewing'] = (int) ($applicationCounts?->cv_reviewing ?? 0);
        }

        if ($context->is('super_admin', 'hr', 'pm')) {
            $interviewCounts = $this->scopeInterviews(Interview::query(), $branchId)
                ->selectRaw(
                    'COUNT(CASE WHEN invite_sent_at IS NULL AND scheduled_at >= ? AND result = ? THEN 1 END) as unsent_interviews, COUNT(CASE WHEN scheduled_at < ? AND result = ? THEN 1 END) as overdue_interviews',
                    [$now, 'pending', $now, 'pending'],
                )
                ->first();
            $counts['unsent_interviews'] = (int) ($interviewCounts?->unsent_interviews ?? 0);
            $counts['overdue_interviews'] = (int) ($interviewCounts?->overdue_interviews ?? 0);
        }

        if ($context->is('super_admin', 'hr', 'director')) {
            $offerDeadline = $now->copy()->addDays(2);
            $offerCounts = $this->scopeOffers(Offer::query(), $branchId)
                ->selectRaw(
                    'COUNT(CASE WHEN status = ? THEN 1 END) as draft_offers, COUNT(CASE WHEN status = ? THEN 1 END) as pending_offers, COUNT(CASE WHEN status = ? AND expires_at IS NOT NULL AND expires_at BETWEEN ? AND ? THEN 1 END) as expiring_offers',
                    ['draft', 'awaiting_approval', 'pending', $now, $offerDeadline],
                )
                ->first();
            $counts['draft_offers'] = (int) ($offerCounts?->draft_offers ?? 0);
            $counts['pending_offers'] = (int) ($offerCounts?->pending_offers ?? 0);
            $counts['expiring_offers'] = (int) ($offerCounts?->expiring_offers ?? 0);
        }

        return $counts;
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
        if (! collect($this->getViewData()['items'])->contains('key', $key)) {
            return [];
        }

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
            'url' => $application ? ApplicationResource::getUrl('view', ['record' => $application]) : ApplicationResource::getUrl('kanban'),
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
            'url' => $application ? ApplicationResource::getUrl('view', ['record' => $application]) : ApplicationResource::getUrl('kanban'),
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
        $query = $branchId
            ? $query->whereHas('application.job', fn (Builder $jobQuery): Builder => $jobQuery->where('branch_id', $branchId))
            : $query;

        $context = RecruitmentDashboardContext::current();

        if ($context->isPm()) {
            $query->where('interviewer_id', $context->user()?->getKey());
        }

        return $query;
    }

    protected function scopeOffers(Builder $query, ?int $branchId): Builder
    {
        return $branchId
            ? $query->whereHas('application.job', fn (Builder $jobQuery): Builder => $jobQuery->where('branch_id', $branchId))
            : $query;
    }
}
