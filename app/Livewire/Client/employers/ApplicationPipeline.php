<?php

namespace App\Livewire\Client\Employers;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Models\RecruitmentJob;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ApplicationPipeline extends Component
{
    public ?int $selectedJobId = null;

    public function mount(): void
    {
        // Optional: filter by first job if exists
    }

    public function updateStatus(int $applicationId, string $newStatus): void
    {
        $application = Application::findOrFail($applicationId);
        
        // Authorization check
        $user = Auth::user();
        if ($user->branch_id && $application->job->branch_id !== $user->branch_id) {
            abort(403);
        }

        $application->update(['status' => $newStatus]);
        
        session()->flash('message', 'Đã cập nhật trạng thái ứng viên thành công.');
    }

    #[Layout('layouts.employer')]
    public function render()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Jobs for filter
        $jobs = RecruitmentJob::query()
            ->when($user->branchScopeId(), fn($q, $id) => $q->where('branch_id', $id))
            ->when(!in_array($user->role, ['director', 'admin']) && !$user->branchScopeId(), fn($q) => $q->where('created_by', $user->id))
            ->orderBy('title')
            ->get();

        // Applications grouped by status
        $statuses = StatusApplicationEnum::cases();
        
        $applicationsByStatus = [];
        foreach ($statuses as $status) {
            $applicationsByStatus[$status->value] = Application::query()
                ->with(['candidate', 'job'])
                ->where('status', $status->value)
                ->when($this->selectedJobId, fn($q) => $q->where('job_id', $this->selectedJobId))
                ->when($user->branchScopeId(), fn($q, $id) => $q->where('branch_id', $id))
                ->when(!in_array($user->role, ['director', 'admin']) && !$user->branchScopeId(), fn($q) => $q->whereHas('job', fn($jq) => $jq->where('created_by', $user->id)))
                ->latest()
                ->get();
        }

        return view('livewire.client.employers.application-pipeline', [
            'jobs' => $jobs,
            'statuses' => $statuses,
            'applicationsByStatus' => $applicationsByStatus,
        ]);
    }
}
