<?php

namespace App\Livewire\Client\Employers;

use App\Models\Candidate;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

class ManageCandidate extends Component
{
    #[Layout('layouts.client')]
    public function render()
    {
        /** @var User|null $user */
        $user = Auth::user();

        $candidates = Candidate::query()
            ->when(
                $user?->branchScopeId(),
                fn (Builder $query, int $branchId) => $query->whereHas(
                    'applications.job',
                    fn (Builder $jobQuery) => $jobQuery->where('branch_id', $branchId)
                )
            )
            ->latest()
            ->get();

        return view('livewire.client.employers.manage_candidate', ['candidates' => $candidates]);
    }
}
