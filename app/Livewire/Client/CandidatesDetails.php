<?php

namespace App\Livewire\Client;

use App\Models\Candidate;
use App\Models\CandidateJobSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class CandidatesDetails extends Component
{
    public $candidate;

    public $latestSubmission;

    public function mount()
    {
        $id = request()->query('id');

        if (! $id) {
            return redirect()->route('home');
        }

        $user = Auth::user();

        if (! $user) {
            return redirect()->route('candidates.login');
        }

        $this->candidate = Candidate::with(['resume', 'user', 'applications.job', 'submissions.job'])->findOrFail($id);

        abort_unless($this->canViewCandidate($user, $this->candidate), 403);

        $this->latestSubmission = CandidateJobSubmission::query()
            ->where('candidate_id', $this->candidate->id)
            ->when($user->branchScopeId(), fn (Builder $query, int $branchId) => $query->whereHas(
                'job',
                fn (Builder $jobQuery) => $jobQuery->where('branch_id', $branchId)
            ))
            ->latest()
            ->first();
    }

    #[Layout('layouts.client')]
    public function render()
    {
        return view('livewire.client.candidates-details');
    }

    private function canViewCandidate(User $user, Candidate $candidate): bool
    {
        if ((int) $candidate->user_id === (int) $user->id) {
            return true;
        }

        if ($user->isSuperAdmin() || $user->role === 'admin') {
            return true;
        }

        $branchId = $user->branchScopeId();

        if (! $branchId) {
            return false;
        }

        return $candidate->applications()
            ->whereHas('job', fn (Builder $query) => $query->where('branch_id', $branchId))
            ->exists()
            || $candidate->submissions()
                ->whereHas('job', fn (Builder $query) => $query->where('branch_id', $branchId))
                ->exists();
    }
}
