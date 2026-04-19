<?php

namespace App\Livewire\Client\Employers;

use App\Models\Application;
use App\Models\RecruitmentJob;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class EmployersDashboard extends Component
{
    #[Layout('layouts.employer')]
    public function render()
    {
        $user = Auth::user();
        $isDirector = in_array($user->role, ['director', 'admin']);

        // Director xem tất cả tin trong chi nhánh, HR chỉ xem tin của mình
        $jobQuery = RecruitmentJob::query();
        if ($isDirector && $user->branch_id) {
            $jobQuery->where('branch_id', $user->branch_id);
        } else {
            $jobQuery->where('created_by', $user->id);
        }

        $jobIds = $jobQuery->pluck('id');

        $totalJobs = $jobIds->count();
        $totalApplications = Application::whereIn('job_id', $jobIds)->count();
        $totalCandidates = Application::whereIn('job_id', $jobIds)
            ->distinct('candidate_id')
            ->count('candidate_id');

        $pendingJobs = 0;
        if ($isDirector) {
            $pendingJobs = RecruitmentJob::where('status', 'pending')
                ->when($user->branch_id, fn($q) => $q->where('branch_id', $user->branch_id))
                ->count();
        }

        return view('livewire.client.employers.employers_dashboard', [
            'totalJobs' => $totalJobs,
            'totalApplications' => $totalApplications,
            'totalCandidates' => $totalCandidates,
            'pendingJobs' => $pendingJobs,
            'user' => $user,
            'isDirector' => $isDirector,
        ]);
    }
}
