<?php

namespace App\Livewire\Client\Employers;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Models\CandidateJobSubmission;
use App\Models\RecruitmentJob;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ApplicationPipeline extends Component
{
    public ?int $selectedJobId = null;

    public function mount(): void
    {
        // Optional: filter by first job if exists.
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

        $statuses = StatusApplicationEnum::cases();
        $statusValues = array_map(fn (StatusApplicationEnum $status) => $status->value, $statuses);

        $applications = Application::query()
            ->with(['candidate.user', 'job'])
            ->whereIn('status', $statusValues)
            ->when($this->selectedJobId, fn ($q) => $q->where('job_id', $this->selectedJobId))
            ->when($user->branchScopeId(), fn ($q, $id) => $q->where('branch_id', $id))
            ->when(! in_array($user->role, ['director', 'admin'], true) && ! $user->branchScopeId(), fn ($q) => $q->whereHas('job', fn ($jq) => $jq->where('created_by', $user->id)))
            ->latest()
            ->get();

        $latestSubmissionsByApplicationKey = $this->latestSubmissionsByApplicationKey($applications);

        $applicationsByStatus = [];
        foreach ($statuses as $status) {
            $applicationsByStatus[$status->value] = $applications
                ->filter(fn (Application $application): bool => $this->applicationStatusValue($application) === $status->value)
                ->values();
        }

        return view('livewire.client.employers.application-pipeline', [
            'jobs' => $jobs,
            'statuses' => $statuses,
            'applicationsByStatus' => $applicationsByStatus,
            'latestSubmissionsByApplicationKey' => $latestSubmissionsByApplicationKey,
        ]);
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
