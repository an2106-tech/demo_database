<?php

namespace App\Livewire\Client;

use App\Models\Application;
use App\Models\CandidateJobSubmission;
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

    public function withdraw(): void
    {
        abort_if($this->application->trashed(), 404);

        $status = $this->application->status instanceof \App\Enums\StatusApplicationEnum
            ? $this->application->status
            : \App\Enums\StatusApplicationEnum::tryFrom((string) $this->application->status);

        if (in_array($status?->value, ['rejected', 'hired'], true)) {
            session()->flash('error', 'Hồ sơ đã ở trạng thái không thể rút.');

            return;
        }

        CandidateJobSubmission::query()
            ->where('job_id', $this->application->job_id)
            ->where('candidate_id', $this->application->candidate_id)
            ->delete();

        $this->application->delete();

        session()->flash('status', 'Đã rút hồ sơ ứng tuyển.');

        $this->redirectRoute('candidates.manage_jobs');
    }

    #[Layout('layouts.client')]
    public function render()
    {
        return view('livewire.client.application-detail');
    }
}
