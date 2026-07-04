<?php

namespace App\Livewire\Client\Employers;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Models\CandidateJobSubmission;
use App\Models\RecruitmentJob;
use App\Models\User;
use App\Services\ApplicationPipelineService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ApplicationPipeline extends Component
{
    public ?int $selectedJobId = null;

    public function mount(): void
    {
        // Optional: filter by first job if exists.
    }

    public function markAsViewed(int $applicationId): void
    {
        $application = $this->findManageableApplication($applicationId);

        $application->forceFill([
            'is_viewed' => true,
            'viewed_at' => $application->viewed_at ?: now(),
        ])->save();

        $this->dispatch('app-notify', message: 'Đã đánh dấu hồ sơ là đã xem.');
    }

    public function advanceApplication(int $applicationId, ApplicationPipelineService $pipelineService): void
    {
        $application = $this->findManageableApplication($applicationId);
        $nextStatus = $this->nextActionStatus($application, $pipelineService);

        if (! $nextStatus) {
            $this->dispatch('app-notify', message: 'Hồ sơ này chưa có bước kế tiếp phù hợp.', type: 'warning');

            return;
        }

        try {
            $pipelineService->transition(
                $application,
                $nextStatus,
                Auth::user(),
                'HR chuyển nhanh từ Pipeline.'
            );
        } catch (ValidationException $exception) {
            $this->dispatch('app-notify', message: $exception->getMessage(), type: 'error');

            return;
        }

        $this->dispatch('app-notify', message: 'Đã chuyển hồ sơ sang: '.$this->statusLabel($nextStatus).'.');
    }

    public function rejectApplication(int $applicationId, ApplicationPipelineService $pipelineService): void
    {
        $application = $this->findManageableApplication($applicationId);

        try {
            $pipelineService->transition(
                $application,
                StatusApplicationEnum::REJECTED,
                Auth::user(),
                'HR từ chối nhanh từ Pipeline.'
            );
        } catch (ValidationException $exception) {
            $this->dispatch('app-notify', message: $exception->getMessage(), type: 'error');

            return;
        }

        $this->dispatch('app-notify', message: 'Đã chuyển hồ sơ sang trạng thái Từ chối.', type: 'warning');
    }

    #[Layout('layouts.employer')]
    public function render()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $jobs = RecruitmentJob::query()
            ->when($user->branchScopeId(), fn ($q, $id) => $q->where('branch_id', $id))
            ->when(! in_array($user->role, ['director', 'admin'], true) && ! $user->branchScopeId(), fn ($q) => $q->where('created_by', $user->id))
            ->orderBy('title')
            ->get();

        $stages = StatusApplicationEnum::pipelineStages();
        $statuses = StatusApplicationEnum::cases();
        $statusValues = array_map(fn (StatusApplicationEnum $status) => $status->value, $statuses);

        $applications = Application::query()
            ->with(['candidate.user', 'job.branch', 'cvAttachment', 'latestInterview', 'latestOffer', 'latestScorecard'])
            ->whereIn('status', $statusValues)
            ->when($this->selectedJobId, fn ($q) => $q->where('job_id', $this->selectedJobId))
            ->when($user->branchScopeId(), function (Builder $query, int $branchId): void {
                $query->where(function (Builder $query) use ($branchId): void {
                    $query
                        ->where('branch_id', $branchId)
                        ->orWhereHas('job', fn (Builder $jobQuery) => $jobQuery->where('branch_id', $branchId));
                });
            })
            ->when(! in_array($user->role, ['director', 'admin'], true) && ! $user->branchScopeId(), fn ($q) => $q->whereHas('job', fn ($jq) => $jq->where('created_by', $user->id)))
            ->latest()
            ->get();

        $latestSubmissionsByApplicationKey = $this->latestSubmissionsByApplicationKey($applications);
        $nextActionStatusesByApplicationId = $this->nextActionStatusesByApplicationId($applications);

        $applicationsByStage = [];
        foreach ($stages as $stageKey => $stage) {
            $stageStatusValues = array_map(fn (StatusApplicationEnum $status): string => $status->value, $stage['statuses']);

            $applicationsByStage[$stageKey] = $applications
                ->filter(fn (Application $application): bool => in_array($this->applicationStatusValue($application), $stageStatusValues, true))
                ->values();
        }

        return view('livewire.client.employers.application-pipeline', [
            'jobs' => $jobs,
            'stages' => $stages,
            'applicationsByStage' => $applicationsByStage,
            'latestSubmissionsByApplicationKey' => $latestSubmissionsByApplicationKey,
            'nextActionStatusesByApplicationId' => $nextActionStatusesByApplicationId,
        ]);
    }

    private function findManageableApplication(int $applicationId): Application
    {
        $application = Application::query()
            ->with(['job'])
            ->findOrFail($applicationId);

        abort_unless($this->canManageApplication(Auth::user(), $application), 403);

        return $application;
    }

    private function canManageApplication(?User $user, Application $application): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin() || in_array($user->role, ['admin', 'director'], true)) {
            $branchId = $user->branchScopeId();

            return ! $branchId || (int) ($application->branch_id ?: $application->job?->branch_id) === (int) $branchId;
        }

        $branchId = $user->branchScopeId();

        if ($branchId) {
            return (int) ($application->branch_id ?: $application->job?->branch_id) === (int) $branchId;
        }

        return (int) $application->job?->created_by === (int) $user->id;
    }

    private function nextActionStatus(Application $application, ApplicationPipelineService $pipelineService): ?StatusApplicationEnum
    {
        return collect($pipelineService->allowedTransitions($application->status))
            ->first(fn (StatusApplicationEnum $status): bool => $status !== StatusApplicationEnum::REJECTED);
    }

    private function nextActionStatusesByApplicationId(Collection $applications): array
    {
        $pipelineService = app(ApplicationPipelineService::class);

        return $applications
            ->mapWithKeys(function (Application $application) use ($pipelineService): array {
                $status = $this->nextActionStatus($application, $pipelineService);

                return [
                    $application->id => $status ? [
                        'value' => $status->value,
                        'label' => $this->statusLabel($status),
                    ] : null,
                ];
            })
            ->all();
    }

    private function statusLabel(StatusApplicationEnum $status): string
    {
        return match ($status) {
            StatusApplicationEnum::CV_REVIEWING => 'Chá» sÃ ng lá»c CV',
            StatusApplicationEnum::SCREENING => 'SÆ¡ tuyá»ƒn',
            StatusApplicationEnum::INTERVIEW_SCHEDULED => 'ÄÃ£ lÃªn lá»‹ch phá»ng váº¥n',
            StatusApplicationEnum::INTERVIEWING => 'Chá» Ä‘Ã¡nh giÃ¡ phá»ng váº¥n',
            StatusApplicationEnum::OFFERED => 'Äá» nghá»‹ tuyá»ƒn dá»¥ng',
            StatusApplicationEnum::HIRED => 'ÄÃ£ tuyá»ƒn',
            StatusApplicationEnum::REJECTED => 'Tá»« chá»‘i',
        };
    }

    private function latestSubmissionsByApplicationKey(Collection $applications): array
    {
        $candidateIds = $applications->pluck('candidate_id')->filter()->unique()->values();
        $jobIds = $applications->pluck('job_id')->filter()->unique()->values();

        if ($candidateIds->isEmpty() || $jobIds->isEmpty()) {
            return [];
        }

        return CandidateJobSubmission::query()
            ->whereIn('candidate_id', $candidateIds->all())
            ->whereIn('job_id', $jobIds->all())
            ->latest()
            ->get()
            ->unique(fn (CandidateJobSubmission $submission): string => $this->submissionKey($submission->candidate_id, $submission->job_id))
            ->keyBy(fn (CandidateJobSubmission $submission): string => $this->submissionKey($submission->candidate_id, $submission->job_id))
            ->all();
    }

    private function applicationStatusValue(Application $application): string
    {
        return $application->status instanceof StatusApplicationEnum
            ? $application->status->value
            : (string) $application->status;
    }

    private function submissionKey(int $candidateId, int $jobId): string
    {
        return $candidateId.':'.$jobId;
    }
}


