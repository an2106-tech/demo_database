<?php

namespace App\Livewire\Client;

use App\Models\Application;
use App\Models\RecruitmentJob;
use App\Services\CandidateAccountService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class CandidateDashboard extends Component
{
    public bool $hasCv = false;
    public string $greeting = '';
    public int $profileCompletion = 0;
    public $recentApplications = [];
    public int $publishedJobsCount = 0;
    public int $appliedCount = 0;
    public string $userName = '';

    public function mount(): void
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $this->userName = (string) ($user->name ?? '');

        $candidate = app(CandidateAccountService::class)->resolveFor($user);
        $candidateService = app(CandidateAccountService::class);

        $this->publishedJobsCount = RecruitmentJob::query()
            ->where('status', 'published')
            ->count();

        $this->appliedCount = Application::query()
            ->where('candidate_id', $candidate->id)
            ->count();

        $this->hasCv = $candidateService->candidateHasCv($candidate);

        // New data for premium dashboard
        $this->greeting = $this->getGreeting();
        $this->profileCompletion = $candidateService->profileCompletion($candidate);
        $this->recentApplications = Application::query()
            ->where('candidate_id', $candidate->id)
            ->with('job')
            ->latest('applied_at')
            ->latest()
            ->take(5)
            ->get();
    }

    private function getGreeting(): string
    {
        $hour = now()->hour;
        if ($hour < 12) return 'Chào buổi sáng';
        if ($hour < 18) return 'Chào buổi chiều';
        return 'Chào buổi tối';
    }

    #[Layout('layouts.client')]
    public function render()
    {
        return view('livewire.client.candidate-dashboard');
    }
}
