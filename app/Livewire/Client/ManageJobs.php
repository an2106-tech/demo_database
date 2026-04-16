<?php

namespace App\Livewire\Client;

use App\Models\Application;
use App\Services\CandidateAccountService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ManageJobs extends Component
{
    #[Layout('layouts.client')]

    public function mount(): void
    {
        // Nếu employer (HR) đang đăng nhập, chuyển hướng đến login của nhà tuyển dụng
        if (Auth::check() && Auth::user()->role === 'hr') {
            redirect()->route('auth.login', ['role' => 'employer'])->send();
        }
    }

    public function render()
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $candidate = app(CandidateAccountService::class)->resolveFor($user);

        $applications = Application::query()
            ->with(['job.branch', 'job.department', 'job.workplace'])
            ->where('candidate_id', $candidate->id)
            ->latest('applied_at')
            ->latest()
            ->get();

        return view('livewire.client.manage-jobs', [
            'applications' => $applications,
        ]);
    }
}
