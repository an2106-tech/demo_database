<?php

namespace App\Livewire\Client;

use App\Models\Application;
use App\Services\CandidateAccountService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ApplicationDetail extends Component
{
    public Application $application;

    public function mount(Application $application): void
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $candidate = app(CandidateAccountService::class)->resolveFor($user);

        $application->loadMissing(['job.branch', 'job.department', 'job.workplace', 'candidate']);

        abort_unless((int) $application->candidate_id === (int) $candidate->id, 403);

        $this->application = $application;
    }

    #[Layout('layouts.client')]
    public function render()
    {
        return view('livewire.client.application-detail');
    }
}
