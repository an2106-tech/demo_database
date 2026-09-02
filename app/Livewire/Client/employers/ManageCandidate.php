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
use Livewire\WithPagination;

class ManageCandidate extends Component
{
    use WithPagination;

    public string $search = '';

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    #[Layout('layouts.employer')]
    public function analyzeWithAi($submissionId, AiMatchingService $aiService)
    {
        $submission = CandidateJobSubmission::with(['candidate', 'job'])->findOrFail($submissionId);

        abort_unless($this->canManageSubmission(Auth::user(), $submission), 403);

        $success = $aiService->calculateMatch($submission);

        if ($success) {
            $this->dispatch('app-notify', message: 'Phân tích AI hoàn tất cho '.$submission->candidate->name);
        } else {
            $this->dispatch('app-notify', message: $aiService->getLastError() ?: 'Không thể phân tích AI. Vui lòng kiểm tra lại API key hoặc nội dung CV.', type: 'error');
        }
    }

    public function render()
    {
        /** @var User|null $user */
        $user = Auth::user();

        $candidatesQuery = Candidate::query()
            ->with([
                'user',
                'applications' => fn ($query) => $query
                    ->with('job.branch')
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
            ->when(filled($this->search), function (Builder $query) {
                $search = trim($this->search);
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('title', 'like', "%{$search}%");
                });
            })
            ->latest();

        $candidates = $candidatesQuery->paginate(9);

        return view('livewire.client.employers.manage_candidate', [
            'candidates' => $candidates,
        ]);
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
}
