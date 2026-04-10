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
}
