<?php

namespace App\Livewire\Client;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Services\ApplicationPipelineService;
use App\Services\CandidateAccountService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $status = $this->application->status instanceof StatusApplicationEnum
            ? $this->application->status
            : StatusApplicationEnum::tryFrom((string) $this->application->status);

        if (in_array($status, [StatusApplicationEnum::REJECTED, StatusApplicationEnum::HIRED, StatusApplicationEnum::WITHDRAWN], true)) {
            session()->flash('error', 'Hồ sơ đã ở trạng thái không thể rút.');

            return;
        }

        $withdrawn = DB::transaction(function (): bool {
            $application = Application::query()->lockForUpdate()->findOrFail($this->application->id);
            $status = $application->status instanceof StatusApplicationEnum
                ? $application->status
                : StatusApplicationEnum::tryFrom((string) $application->status);

            if (in_array($status, [StatusApplicationEnum::REJECTED, StatusApplicationEnum::HIRED, StatusApplicationEnum::WITHDRAWN], true)) {
                return false;
            }

            app(ApplicationPipelineService::class)->transition(
                $application,
                StatusApplicationEnum::WITHDRAWN,
                Auth::user(),
                'Ứng viên chủ động rút hồ sơ.',
            );

            $application->forceFill([
                'withdrawn_at' => now(),
                'rejected_stage' => null,
                'rejected_reason' => null,
            ])->save();

            return true;
        });

        if (! $withdrawn) {
            session()->flash('error', 'Hồ sơ vừa được xử lý và không thể rút lúc này.');

            return;
        }

        session()->flash('status', 'Đã rút hồ sơ ứng tuyển.');

        $this->redirectRoute('candidates.manage_jobs');
    }

    #[Layout('layouts.client')]
    public function render()
    {
        return view('livewire.client.application-detail');
    }
}
