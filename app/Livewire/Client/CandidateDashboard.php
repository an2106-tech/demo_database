<?php

namespace App\Livewire\Client;

use App\Models\CandidateJobSubmission;
use App\Models\RecruitmentJob;
use App\Services\CandidateAccountService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class CandidateDashboard extends Component
{
    public string $userName = '';

    public int $publishedJobsCount = 0;

    public int $appliedCount = 0;

    public bool $hasCv = false;

    public function mount(): void
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $this->userName = (string) ($user->name ?? '');

        $candidate = app(CandidateAccountService::class)->resolveFor($user);

        $this->publishedJobsCount = RecruitmentJob::query()
            ->where('status', 'published')
            ->count();

        $this->appliedCount = CandidateJobSubmission::query()
            ->where('candidate_id', $candidate->id)
            ->count();

        $this->hasCv = (bool) $candidate->cv_file
            || $candidate->attachments()->where('type', 'cv')->exists();
    }

    #[Layout('layouts.client')]
    public function render()
    {
        return view('livewire.client.candidate-dashboard');
    }
}
