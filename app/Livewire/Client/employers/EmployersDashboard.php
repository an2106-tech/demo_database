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

        // Funnel statistics
        $pipelineMetrics = [
            'screening' => Application::whereIn('job_id', $jobIds)->whereIn('status', ['cv_reviewing', 'screening'])->count(),
            'interviewing' => Application::whereIn('job_id', $jobIds)->whereIn('status', ['interview_scheduled', 'interviewing'])->count(),
            'offered' => Application::whereIn('job_id', $jobIds)->where('status', 'offered')->count(),
            'hired' => Application::whereIn('job_id', $jobIds)->where('status', 'hired')->count(),
        ];

        // Upcoming interviews in the next 14 days
        $upcomingInterviews = \App\Models\Interview::whereHas('application', function ($q) use ($jobIds) {
            $q->whereIn('job_id', $jobIds);
        })
        ->where('scheduled_at', '>=', now()->startOfDay())
        ->with(['application.candidate.user', 'application.job', 'workplace', 'interviewer'])
        ->orderBy('scheduled_at', 'asc')
        ->take(5)
        ->get();

        // Recent applications
        $greeting = $this->getGreeting();
        $recentApplications = Application::whereIn('job_id', $jobIds)
            ->with(['job', 'candidate.user', 'cvAttachment'])
            ->latest('applied_at')
            ->latest('id')
            ->take(6)
            ->get();

        return view('livewire.client.employers.employers_dashboard', [
            'totalJobs' => $totalJobs,
            'totalApplications' => $totalApplications,
            'totalCandidates' => $totalCandidates,
            'pendingJobs' => $pendingJobs,
            'pipelineMetrics' => $pipelineMetrics,
            'upcomingInterviews' => $upcomingInterviews,
            'user' => $user,
            'isDirector' => $isDirector,
            'greeting' => $greeting,
            'recentApplications' => $recentApplications,
        ]);
    }

    private function getGreeting(): string
    {
        $hour = now()->hour;
        if ($hour < 12) return 'Chào buổi sáng';
        if ($hour < 18) return 'Chào buổi chiều';
        return 'Chào buổi tối';
    }
}
