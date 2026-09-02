<?php

namespace App\Livewire\Client\Employers;

use App\Models\Candidate;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class BrowseCandidates extends Component
{
    use WithPagination;

    public string $search = '';
    public string $location = '';
    public string $experience = '';

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingLocation()
    {
        $this->resetPage();
    }

    public function updatingExperience()
    {
        $this->resetPage();
    }

    #[Layout('layouts.employer')]
    public function render()
    {
        /** @var User|null $user */
        $user = Auth::user();

        $candidates = Candidate::query()
            ->with(['user', 'applications.job.branch'])
            ->when(
                $user?->branchScopeId(),
                fn (Builder $query, int $branchId) => $query->whereHas(
                    'applications.job',
                    fn (Builder $jobQuery) => $jobQuery->where('branch_id', $branchId)
                )
            )
            ->when(filled($this->search), function (Builder $query) {
                $search = trim($this->search);
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('title', 'like', "%{$search}%");
                });
            })
            ->when(filled($this->location), function (Builder $query) {
                $query->where('address', 'like', "%{$this->location}%");
            })
            ->when(filled($this->experience), function (Builder $query) {
                if ($this->experience === '0') {
                    $query->where('experience_years', '<=', 1);
                } elseif ($this->experience === '1-3') {
                    $query->whereBetween('experience_years', [1, 3]);
                } elseif ($this->experience === '3-5') {
                    $query->whereBetween('experience_years', [3, 5]);
                } elseif ($this->experience === '5+') {
                    $query->where('experience_years', '>=', 5);
                }
            })
            ->latest()
            ->paginate(10);

        return view('livewire.client.employers.browse_candidates', [
            'candidates' => $candidates,
        ]);
    }
}
