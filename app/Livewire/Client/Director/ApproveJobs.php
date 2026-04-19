<?php

namespace App\Livewire\Client\Director;

use App\Enums\StatusRecruitmentJobsEnum;
use App\Models\RecruitmentJob;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.employer')]
class ApproveJobs extends Component
{
    use WithPagination;

    public function approve(RecruitmentJob $job): void
    {
        $user = Auth::user();

        if (! in_array($user->role, ['director', 'admin'])) {
            $this->dispatch('notify', ['message' => 'Bạn không có quyền duyệt tin.', 'type' => 'error']);
            return;
        }

        $jobTitle = $job->title;
        $job->update(['status' => StatusRecruitmentJobsEnum::PUBLISHED]);
        $this->dispatch('notify', ['message' => "✅ Đã duyệt tin \"{$jobTitle}\"!", 'type' => 'success']);
    }

    public function reject(RecruitmentJob $job): void
    {
        $user = Auth::user();

        if (! in_array($user->role, ['director', 'admin'])) {
            $this->dispatch('notify', ['message' => 'Bạn không có quyền duyệt tin.', 'type' => 'error']);
            return;
        }

        $jobTitle = $job->title;
        $job->update(['status' => StatusRecruitmentJobsEnum::DRAFT]);
        $this->dispatch('notify', ['message' => "❌ Đã từ chối tin \"{$jobTitle}\".", 'type' => 'error']);
    }

    public function render()
    {
        $pendingJobs = RecruitmentJob::where('status', StatusRecruitmentJobsEnum::PENDING)
            ->with(['branch', 'workplace', 'department', 'creator'])
            ->latest()
            ->paginate(10);

        return view('livewire.client.director.approve-jobs', [
            'pendingJobs' => $pendingJobs,
        ]);
    }
}