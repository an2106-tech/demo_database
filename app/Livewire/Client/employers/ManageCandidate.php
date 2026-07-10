<?php

namespace App\Livewire\Client\Employers;

use App\Models\Candidate;
use App\Models\CandidateJobSubmission;
use App\Models\User;
use App\Services\AiMatchingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ManageCandidate extends Component
{
    #[Layout('layouts.employer')]
    public function analyzeWithAi($submissionId, AiMatchingService $aiService)
    {
        $submission = CandidateJobSubmission::with(['candidate', 'job'])->findOrFail($submissionId);

        abort_unless($this->canManageSubmission(Auth::user(), $submission), 403);

        $success = $aiService->calculateMatch($submission);

        if ($success) {
            session()->flash('message', 'Phân tích AI hoàn tất cho '.$submission->candidate->name);
        } else {
            session()->flash('error', $aiService->getLastError() ?: 'Không thể phân tích AI. Vui lòng kiểm tra lại API key hoặc nội dung CV.');
        }
    }

    public function deleteCandidate($candidateId)
    {
        $candidate = Candidate::with(['applications.job', 'submissions.job'])->findOrFail($candidateId);

        abort_unless($this->canManageCandidate(Auth::user(), $candidate), 403);

        $candidate->delete();

        session()->flash('message', 'Da xoa ung vien thanh cong.');
    }

    public function render()
    {
        /** @var User|null $user */
        $user = Auth::user();

        $candidates = Candidate::query()
            ->with([
                'user',
                'applications' => fn ($query) => $query
                    ->with('job')
                    ->when($user?->branchScopeId(), function (Builder $query, int $branchId) {
                        $query->where(function (Builder $query) use ($branchId) {
                            $query
                                ->where('branch_id', $branchId)
                                ->orWhereHas('job', fn (Builder $jobQuery) => $jobQuery->where('branch_id', $branchId));
                        });
                    })
                    ->latest('applied_at')
                    ->latest(),
                'submissions' => fn ($query) => $query
                    ->with('job')
                    ->when($user?->branchScopeId(), function ($query, int $branchId) {
                        $query->whereHas('job', fn (Builder $jobQuery) => $jobQuery->where('branch_id', $branchId));
                    })
                    ->latest(),
            ])
            ->when($user?->branchScopeId(), function (Builder $query, int $branchId) {
                $query->where(function (Builder $query) use ($branchId) {
                    $query
                        ->whereHas('applications', function (Builder $applicationQuery) use ($branchId) {
                            $applicationQuery
                                ->where('branch_id', $branchId)
                                ->orWhereHas('job', fn (Builder $jobQuery) => $jobQuery->where('branch_id', $branchId));
                        })
                        ->orWhereHas('submissions.job', fn (Builder $jobQuery) => $jobQuery->where('branch_id', $branchId));
                });
            })
            ->latest()
            ->get();

        return view('livewire.client.employers.manage_candidate', ['candidates' => $candidates]);
    }

    private function canManageSubmission(?User $user, CandidateJobSubmission $submission): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin() || $user->role === 'admin') {
            return true;
        }

        $branchId = $user->branchScopeId();

        if (! $branchId) {
            return false;
        }

        return (int) $submission->job?->branch_id === $branchId;
    }

    private function canManageCandidate(?User $user, Candidate $candidate): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin() || $user->role === 'admin') {
            return true;
        }

        $branchId = $user->branchScopeId();

        if (! $branchId) {
            return false;
        }

        return $candidate->applications
            ->contains(fn ($application) => (int) $application->job?->branch_id === $branchId)
            || $candidate->submissions
                ->contains(fn ($submission) => (int) $submission->job?->branch_id === $branchId);
    }
}
