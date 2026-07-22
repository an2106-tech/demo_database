<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Enums\StatusApplicationEnum;
use App\Filament\Resources\Applications\ApplicationResource;
use App\Models\Application;
use App\Services\ApplicationWorkflowSummaryService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;

class KanbanApplications extends Page
{
    protected static string $resource = ApplicationResource::class;

    protected string $view = 'filament.resources.applications.pages.kanban-applications';

    protected ?string $heading = 'Kanban ứng tuyển';

    protected ?string $subheading = null;

    protected Width | string | null $maxContentWidth = Width::Full;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'queue')]
    public string $quickFilter = 'all';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('table')
                ->label('Dạng bảng')
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->url(ApplicationResource::getUrl('index')),
            CreateAction::make(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $summaryService = app(ApplicationWorkflowSummaryService::class);
        $applications = ApplicationResource::getEloquentQuery()
            ->latest('updated_at')
            ->get();

        $searchFilteredApplications = $this->filterBySearch($applications, $summaryService);
        $filteredApplications = $this->filterByWorkQueue($searchFilteredApplications);
        $workQueues = $this->workQueues($searchFilteredApplications);

        return [
            'columns' => $this->buildColumns($filteredApplications, $summaryService),
            'workQueues' => $workQueues,
            'activeQueue' => $workQueues[$this->quickFilter] ?? $workQueues['all'],
            'search' => $this->search,
            'quickFilter' => $this->quickFilter,
            'totalApplications' => $filteredApplications->count(),
            'unfilteredApplications' => $applications->count(),
        ];
    }

    /**
     * @param  Collection<int, Application>  $applications
     * @return array<int, array<string, mixed>>
     */
    private function buildColumns(Collection $applications, ApplicationWorkflowSummaryService $summaryService): array
    {
        return collect(StatusApplicationEnum::pipelineStages())
            ->map(function (array $stage, string $stageKey) use ($applications, $summaryService): array {
                $statusValues = StatusApplicationEnum::statusValuesForPipelineStage($stageKey);
                $stageApplications = $applications
                    ->filter(fn (Application $application): bool => in_array($this->statusValue($application), $statusValues, true))
                    ->values();

                return [
                    'key' => $stageKey,
                    'label' => $stage['label'],
                    'count' => $stageApplications->count(),
                    'cards' => $stageApplications
                        ->map(fn (Application $application): array => $this->buildCard($application, $summaryService))
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Application>  $applications
     * @return Collection<int, Application>
     */
    private function filterBySearch(Collection $applications, ApplicationWorkflowSummaryService $summaryService): Collection
    {
        $search = $this->normalizeSearchText($this->search);

        return $applications
            ->filter(function (Application $application) use ($search, $summaryService): bool {
                $summary = $summaryService->summarize($application);

                return $this->matchesSearch($application, $summary, $search);
            })
            ->values();
    }

    /**
     * @param  Collection<int, Application>  $applications
     * @return Collection<int, Application>
     */
    private function filterByWorkQueue(Collection $applications): Collection
    {
        return $applications
            ->filter(fn (Application $application): bool => $this->matchesWorkQueue($application, $this->quickFilter))
            ->values();
    }

    /**
     * @param  array<string, mixed>  $summary
     */
    private function matchesSearch(Application $application, array $summary, string $search): bool
    {
        if ($search === '') {
            return true;
        }

        $haystack = $this->normalizeSearchText(implode(' ', array_filter([
            (string) $application->id,
            'HS'.$application->id,
            'hoso '.$application->id,
            'ho so '.$application->id,
            $application->snapshotCandidateName(),
            $application->snapshotCandidateEmail(),
            $application->snapshotCandidatePhone(),
            $application->job?->title,
            $application->job?->branch?->name ?? $application->branch?->name,
            $application->job?->department?->name,
            $summary['stage_label'] ?? null,
            $summary['status_label'] ?? null,
            $summary['description'] ?? null,
        ])));

        return collect(explode(' ', $search))
            ->filter()
            ->every(fn (string $token): bool => str_contains($haystack, $token));
    }

    private function normalizeSearchText(?string $value): string
    {
        $value = Str::ascii((string) $value);
        $value = Str::lower($value);
        $value = preg_replace('/[^a-z0-9]+/u', ' ', $value) ?? '';

        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
    }

    private function matchesWorkQueue(Application $application, string $queue): bool
    {
        $status = $this->statusValue($application);
        $interview = $application->latestInterview;
        $offer = $application->latestOffer;

        return match ($queue) {
            'cv_reviewing' => $status === StatusApplicationEnum::CV_REVIEWING->value,
            'interview_schedule_needed' => $status === StatusApplicationEnum::SCREENING->value && ! $interview,
            'interview_invite_unsent' => in_array($status, [
                StatusApplicationEnum::SCREENING->value,
                StatusApplicationEnum::INTERVIEW_SCHEDULED->value,
                StatusApplicationEnum::INTERVIEWING->value,
            ], true) && $interview && blank($interview->invite_sent_at),
            'interview_overdue' => in_array($status, [
                StatusApplicationEnum::INTERVIEW_SCHEDULED->value,
                StatusApplicationEnum::INTERVIEWING->value,
            ], true)
                && $interview
                && ($interview->result ?? 'pending') === 'pending'
                && $interview->scheduled_at?->isPast(),
            'offer_needed' => $status === StatusApplicationEnum::OFFERED->value && ! $offer,
            'offer_awaiting_approval' => $status === StatusApplicationEnum::OFFERED->value
                && $offer?->status === 'awaiting_approval',
            'offer_expiring' => $status === StatusApplicationEnum::OFFERED->value
                && $offer?->status === 'pending'
                && $offer->expires_at
                && $offer->expires_at->isFuture()
                && $offer->expires_at->lte(now()->addDays(2)),
            default => true,
        };
    }

    /**
     * @param  Collection<int, Application>  $applications
     * @return array<string, array{label: string, description: string, count: int}>
     */
    private function workQueues(Collection $applications): array
    {
        $queues = [
            'all' => [
                'label' => 'Tất cả',
                'description' => 'Toàn bộ hồ sơ đang theo dõi.',
            ],
            'cv_reviewing' => [
                'label' => 'Chờ sàng lọc',
                'description' => 'Hồ sơ mới cần xem CV và quyết định bước tiếp theo.',
            ],
            'interview_schedule_needed' => [
                'label' => 'Cần lên lịch',
                'description' => 'Ứng viên đã qua sơ tuyển nhưng chưa có lịch phỏng vấn.',
            ],
            'interview_invite_unsent' => [
                'label' => 'Chưa gửi thư mời',
                'description' => 'Lịch đã tạo nhưng chưa gửi email cho ứng viên.',
            ],
            'interview_overdue' => [
                'label' => 'Cần chấm phỏng vấn',
                'description' => 'Buổi phỏng vấn đã đến hạn và cần ghi nhận scorecard.',
            ],
            'offer_needed' => [
                'label' => 'Cần tạo đề nghị',
                'description' => 'Ứng viên đã qua đánh giá, cần tạo đề nghị tuyển dụng.',
            ],
            'offer_awaiting_approval' => [
                'label' => 'Chờ duyệt đề nghị',
                'description' => 'Đề nghị đã gửi giám đốc chi nhánh duyệt.',
            ],
            'offer_expiring' => [
                'label' => 'Sắp hết hạn phản hồi',
                'description' => 'Đề nghị đã gửi ứng viên và sắp hết hạn phản hồi.',
            ],
        ];

        return collect($queues)
            ->map(function (array $queue, string $key) use ($applications): array {
                $queue['count'] = $key === 'all'
                    ? $applications->count()
                    : $applications->filter(fn (Application $application): bool => $this->matchesWorkQueue($application, $key))->count();

                return $queue;
            })
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCard(Application $application, ApplicationWorkflowSummaryService $summaryService): array
    {
        $summary = $summaryService->summarize($application);
        $analysis = $application->latestScreeningAiAnalysis;

        return [
            'id' => $application->id,
            'candidate' => $application->snapshotCandidateName() ?: 'Hồ sơ #'.$application->id,
            'job' => $application->job?->title ?? 'Chưa có vị trí',
            'branch' => $application->job?->branch?->name ?? $application->branch?->name,
            'department' => $application->job?->department?->name,
            'status' => $summary['status_label'] ?? $this->statusLabel($application),
            'description' => $summary['description'] ?? null,
            'color' => $summary['color'] ?? 'gray',
            'applied_at' => $application->applied_at?->format('d/m/Y H:i'),
            'ai_score' => $analysis?->status === 'completed' ? $analysis->score : null,
            'has_ai' => $analysis?->status === 'completed',
            'url' => ApplicationResource::getUrl('view', ['record' => $application]),
        ];
    }

    private function statusValue(Application $application): ?string
    {
        return $application->status instanceof StatusApplicationEnum
            ? $application->status->value
            : $application->status;
    }

    private function statusLabel(Application $application): string
    {
        if ($application->status instanceof StatusApplicationEnum) {
            return (string) $application->status->getLabel();
        }

        return StatusApplicationEnum::tryFrom((string) $application->status)?->getLabel()
            ?? 'Chưa xác định';
    }
}
