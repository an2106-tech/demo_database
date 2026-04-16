<?php
namespace App\Livewire\Client\Employers;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class BrowseCandidates extends Component
{
    #[Layout('layouts.employer')]

    public function mount(): void
    {
        // Nếu candidate đang đăng nhập, chuyển hướng đến login của ứng viên
        if (Auth::check() && Auth::user()->role === 'candidate') {
            redirect()->route('auth.login', ['role' => 'candidate'])->send();
        }
    }

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

        return view('livewire.client.employers.browse_candidates', [
            'candidates' => $candidates,
        ]);
    }
}
