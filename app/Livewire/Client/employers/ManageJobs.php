<?php

namespace App\Livewire\Client\Employers;

use App\Models\RecruitmentJob;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ManageJobs extends Component
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
            session()->flash('status', 'Xoá tin tuyển dụng thành công!');
            $this->redirect(route('employers.manage_jobs'), navigate: true);
        } else {
            session()->flash('error', 'Không tìm thấy tin tuyển dụng hoặc không có quyền xoá.');
        }
    }
}
