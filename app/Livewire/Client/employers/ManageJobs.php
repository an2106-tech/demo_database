<?php

namespace App\Livewire\Client\Employers;

use App\Models\RecruitmentJob;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ManageJobs extends Component
{
    #[Layout('layouts.employer')]
    public function render()
    {
        $jobs = RecruitmentJob::query()
            ->with(['branch', 'department', 'workplace'])
            ->where('created_by', Auth::id())
            ->latest()
            ->get();

        return view('livewire.client.employers.manage_jobs', [
            'jobs' => $jobs,
        ]);
    }

    public function deleteJob($id)
    {
        $job = RecruitmentJob::where('id', $id)->where('created_by', Auth::id())->first();

        if ($job) {
            $job->skills()->detach();
            $job->delete();
            session()->flash('status', 'Xoa tin tuyen dung thanh cong!');
            $this->redirect(route('employers.manage_jobs'), navigate: true);
        } else {
            session()->flash('error', 'Khong tim thay tin tuyen dung hoac khong co quyen xoa.');
        }
    }
}
