<?php

namespace App\Livewire\Client\Employers;

use App\Models\RecruitmentJob;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class ManageJobs extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    #[Layout('layouts.employer')]
    public function render()
    {
        $user = Auth::user();
        $isDirector = in_array($user->role, ['director', 'admin']);

        $baseQuery = RecruitmentJob::query()
            ->when($isDirector && $user->branch_id, fn($q) => $q->where('branch_id', $user->branch_id))
            ->when(! $isDirector, fn($q) => $q->where('created_by', $user->id));

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'published' => (clone $baseQuery)->where('status', 'published')->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'closed' => (clone $baseQuery)->whereIn('status', ['expired', 'closed'])->count(),
        ];

        $jobs = (clone $baseQuery)
            ->with(['branch', 'department', 'workplace', 'applications'])
            ->when(filled($this->search), function ($q) {
                $q->where('title', 'like', "%{$this->search}%");
            })
            ->when(filled($this->statusFilter), function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.client.employers.manage_jobs', [
            'jobs' => $jobs,
            'stats' => $stats,
            'isDirector' => $isDirector,
        ]);
    }

    public function deleteJob($id)
    {
        $user = Auth::user();
        $isDirector = in_array($user->role, ['director', 'admin']);

        $jobQuery = RecruitmentJob::where('id', $id);
        if ($isDirector && $user->branch_id) {
            $jobQuery->where('branch_id', $user->branch_id);
        } else {
            $jobQuery->where('created_by', $user->id);
        }

        $job = $jobQuery->first();

        if ($job) {
            $job->skills()->detach();
            $job->delete();
            $this->dispatch('app-notify', message: 'Xóa tin tuyển dụng thành công.');
        } else {
            $this->dispatch('app-notify', message: 'Không tìm thấy tin tuyển dụng hoặc bạn không có quyền xóa.', type: 'error');
        }
    }
}
