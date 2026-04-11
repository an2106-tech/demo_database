<?php

namespace App\Livewire\Client\Employers;

use App\Models\RecruitmentJob;
use App\Models\Application;
use App\Models\Candidate;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class EmployersDashboard extends Component
{
    #[Layout('layouts.employer')]
    public function render()
    {
        $user = Auth::user();
        
        $jobIds = RecruitmentJob::where('created_by', $user->id)->pluck('id');
        
        $totalJobs = $jobIds->count();
        $totalApplications = Application::whereIn('job_id', $jobIds)->count();
        $totalCandidates = Application::whereIn('job_id', $jobIds)
            ->distinct('candidate_id')
            ->count('candidate_id');

        return view('livewire.client.employers.employers_dashboard', [
            'totalJobs' => $totalJobs,
            'totalApplications' => $totalApplications,
            'totalCandidates' => $totalCandidates,
            'user' => $user,
        ]);
    }
}
