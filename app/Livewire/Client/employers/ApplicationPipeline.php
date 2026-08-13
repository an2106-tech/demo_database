<?php

namespace App\Livewire\Client\Employers;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Models\CandidateJobSubmission;
use App\Models\RecruitmentJob;
use App\Models\User;
use App\Models\Workplace;
use App\Services\ApplicationPipelineService;
use App\Services\ApplicationWorkflowGuard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ApplicationPipeline extends Component
{
    public ?int $selectedJobId = null;

    public bool $showInterviewModal = false;

    public ?int $interviewApplicationId = null;

    public bool $showEvaluationModal = false;

    public ?int $evaluationApplicationId = null;

    public bool $showRejectModal = false;

    public ?int $rejectionApplicationId = null;

    public string $rejectionReason = '';

    public array $interviewForm = [
        'round_name' => '',
        'scheduled_at' => '',
        'duration_minutes' => 60,
        'type' => 'online',
        'meeting_link' => '',
        'workplace_id' => '',
        'interviewer_id' => '',
        'notes' => '',
    ];

    public bool $showMessageModal = false;
    public ?int $messageApplicationId = null;
    public string $messageContent = '';

    public function mount(): void
    {
        $scheduleInterviewApplicationId = request()->integer('schedule_interview');

        if ($scheduleInterviewApplicationId) {
            $this->openInterviewScheduler($scheduleInterviewApplicationId);
        }

        $evaluateInterviewApplicationId = request()->integer('evaluate_interview');

        if ($evaluateInterviewApplicationId) {
            $this->openInterviewEvaluation($evaluateInterviewApplicationId);
        }
    }

    public function openInterviewEvaluation(int $applicationId): void
    {
        $application = Application::query()
            ->with(['job', 'latestInterview'])
            ->findOrFail($applicationId);

        abort_unless($this->canEvaluateInterview($application), 403);

        $this->evaluationApplicationId = $application->id;
        $this->showEvaluationModal = true;
    }

    public function openInterviewScheduler(int $applicationId): void
    {
        $application = $this->findManageableApplication($applicationId);

        abort_unless($this->canScheduleInterview($application), 403);

        $interview = $application->interviews()->latest('id')->first();
        $defaultScheduledAt = now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))
            ->addDay()
            ->setTime(9, 0);

        $this->interviewApplicationId = $application->id;
        $this->interviewForm = [
            'round_name' => $interview?->round_name ?: 'Phỏng vấn vòng '.((int) ($interview?->round_number ?: 1)),
            'scheduled_at' => ($interview?->scheduled_at ?? $defaultScheduledAt)->format('Y-m-d\TH:i'),
            'duration_minutes' => (int) ($interview?->duration_minutes ?: 60),
            'type' => $interview?->type ?: 'online',
            'meeting_link' => (string) ($interview?->meeting_link ?: ''),
            'workplace_id' => $interview?->workplace_id ? (string) $interview->workplace_id : '',
            'interviewer_id' => $interview?->interviewer_id ? (string) $interview->interviewer_id : (string) Auth::id(),
            'notes' => (string) ($interview?->notes ?: ''),
        ];
        $this->showInterviewModal = true;
    }

    public function closeInterviewScheduler(): void
    {
        $this->showInterviewModal = false;
        $this->interviewApplicationId = null;
        $this->resetValidation();
    }

    public function openMessageModal($applicationId)
    {
        $this->messageApplicationId = $applicationId;
        $this->messageContent = '';
        $this->showMessageModal = true;
    }

    public function closeMessageModal()
    {
        $this->showMessageModal = false;
        $this->messageApplicationId = null;
        $this->messageContent = '';
    }

    public function sendMessage()
    {
        $this->validate(['messageContent' => 'required|string|max:1000'], [], ['messageContent' => 'Nội dung tin nhắn']);

        if (! $this->messageApplicationId) {
            $this->dispatch('app-notify', message: 'Không tìm thấy hồ sơ ứng viên.', type: 'error');
            return;
        }

        $application = Application::query()->with(['job'])->find((int) $this->messageApplicationId);

        if (! $application) {
            $this->dispatch('app-notify', message: 'Hồ sơ ứng viên không tồn tại.', type: 'error');
            return;
        }

        if (! $this->canManageApplication(Auth::user(), $application)) {
            $this->dispatch('app-notify', message: 'Bạn không có quyền nhắn tin cho ứng viên này.', type: 'error');
            $this->closeMessageModal();
            return;
        }

        $chat = \App\Models\Chat::firstOrCreate([
            'employer_id' => Auth::id(),
            'candidate_id' => $application->candidate_id,
            'job_id' => $application->recruitment_job_id,
            'type' => 'employer_candidate',
        ], ['status' => 'active']);

        \App\Models\ChatMessage::create([
            'chat_id' => $chat->id,
            'sender_type' => 'employer',
            'sender_id' => Auth::id(),
            'content' => $this->messageContent,
        ]);

        $this->closeMessageModal();
        $this->dispatch('app-notify', message: 'Tin nhắn đã được gửi đến ứng viên.');
    }
    public function markAsViewed(int $applicationId): void
    {
        $application = $this->findManageableApplication($applicationId);

        $application->forceFill([
            'is_viewed' => true,
            'viewed_at' => $application->viewed_at ?: now(),
        ])->save();

        $this->dispatch('app-notify', message: 'Đã đánh dấu hồ sơ là đã xem.');
    }

    public function advanceApplication(int $applicationId): void
    {
        $pipelineService = app(ApplicationPipelineService::class);
        $application = $this->findManageableApplication($applicationId);
        $nextStatus = $this->nextActionStatus($application, $pipelineService);

        if (! $nextStatus) {
            $currentStatus = $application->status instanceof StatusApplicationEnum
                ? $application->status
                : StatusApplicationEnum::tryFrom((string) $application->status);

            $message = $currentStatus === StatusApplicationEnum::SCREENING
                ? 'Vui lòng dùng nút Lên lịch PV để chuyển hồ sơ sang vòng phỏng vấn.'
                : 'Hồ sơ này chưa có bước kế tiếp phù hợp.';

            $this->dispatch('app-notify', message: $message, type: 'warning');

            return;
        }

        try {
            $pipelineService->transition(
                $application,
                $nextStatus,
                Auth::user(),
                'HR chuyển nhanh từ Pipeline.'
            );
        } catch (ValidationException $exception) {
            $this->dispatch('app-notify', message: $exception->getMessage(), type: 'error');

            return;
        }

        $this->dispatch('app-notify', message: 'Đã chuyển hồ sơ sang: '.$this->statusLabel($nextStatus).'.');
    }

    public function openRejectionModal(int $applicationId): void
    {
        $application = $this->findManageableApplication($applicationId);

        abort_unless(app(ApplicationWorkflowGuard::class)->canRejectApplication(Auth::user(), $application), 403);

        $this->rejectionApplicationId = $application->id;
        $this->rejectionReason = '';
        $this->showRejectModal = true;
        $this->resetValidation('rejectionReason');
    }

    public function closeRejectionModal(): void
    {
        $this->showRejectModal = false;
        $this->rejectionApplicationId = null;
        $this->rejectionReason = '';
        $this->resetValidation('rejectionReason');
    }

    public function rejectApplication(): void
    {
        $this->validate([
            'rejectionReason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [], [
            'rejectionReason' => 'lý do từ chối',
        ]);

        $pipelineService = app(ApplicationPipelineService::class);
        $application = $this->findManageableApplication((int) $this->rejectionApplicationId);

        abort_unless(app(ApplicationWorkflowGuard::class)->canRejectApplication(Auth::user(), $application), 403);

        $reason = trim($this->rejectionReason);
        $application->forceFill(['rejected_reason' => $reason])->save();

        try {
            $pipelineService->transition(
                $application,
                StatusApplicationEnum::REJECTED,
                Auth::user(),
                'Từ chối ứng viên. Lý do: '.$reason,
            );
        } catch (ValidationException $exception) {
            $this->dispatch('app-notify', message: $exception->errors()['status'][0] ?? $exception->getMessage(), type: 'error');

            return;
        }

        $this->closeRejectionModal();
        $this->dispatch('app-notify', message: 'Đã từ chối hồ sơ và ghi nhận lý do.', type: 'warning');
    }

    #[Layout('layouts.employer')]
    public function render()
    {
        /** @var User $user */
        $user = Auth::user();

        $jobs = RecruitmentJob::query()
            ->when($user->branchScopeId(), fn ($q, $id) => $q->where('branch_id', $id))
            ->when(! in_array($user->role, ['director', 'admin'], true) && ! $user->branchScopeId(), fn ($q) => $q->where('created_by', $user->id))
            ->orderBy('title')
            ->get();

        $stages = StatusApplicationEnum::pipelineStages();
        $statuses = StatusApplicationEnum::cases();
        $statusValues = array_map(fn (StatusApplicationEnum $status) => $status->value, $statuses);

        $applications = Application::query()
            ->with(['candidate.user', 'job.branch', 'cvAttachment', 'latestInterview', 'latestOffer', 'latestScorecard'])
            ->whereIn('status', $statusValues)
            ->when($this->selectedJobId, fn ($q) => $q->where('job_id', $this->selectedJobId))
            ->when($user->branchScopeId(), function (Builder $query, int $branchId): void {
                $query->where(function (Builder $query) use ($branchId): void {
                    $query
                        ->where('branch_id', $branchId)
                        ->orWhereHas('job', fn (Builder $jobQuery) => $jobQuery->where('branch_id', $branchId));
                });
            })
            ->when(! in_array($user->role, ['director', 'admin'], true) && ! $user->branchScopeId(), fn ($q) => $q->whereHas('job', fn ($jq) => $jq->where('created_by', $user->id)))
            ->latest()
            ->get();

        $latestSubmissionsByApplicationKey = $this->latestSubmissionsByApplicationKey($applications);
        $nextActionStatusesByApplicationId = $this->nextActionStatusesByApplicationId($applications);
        $selectedInterviewApplication = $this->selectedInterviewApplication();
        $selectedEvaluationApplication = $this->selectedEvaluationApplication();
        $workflowGuard = app(ApplicationWorkflowGuard::class);
        $pipelineActionPermissions = $applications->mapWithKeys(fn (Application $application): array => [
            $application->id => [
                'manage' => $workflowGuard->canRunHrPipelineActions($user)
                    && $workflowGuard->canAccessApplicationBranch($user, $application),
                'schedule' => $workflowGuard->canManageInterview($user, $application),
                'evaluate' => $workflowGuard->canEvaluateInterview($user, $application),
                'reject' => $workflowGuard->canRejectApplication($user, $application),
            ],
        ])->all();

        $applicationsByStage = [];
        foreach ($stages as $stageKey => $stage) {
            $stageStatusValues = array_map(fn (StatusApplicationEnum $status): string => $status->value, $stage['statuses']);

            $applicationsByStage[$stageKey] = $applications
                ->filter(fn (Application $application): bool => in_array($this->applicationStatusValue($application), $stageStatusValues, true))
                ->values();
        }

        return view('livewire.client.employers.application-pipeline', [
            'jobs' => $jobs,
            'stages' => $stages,
            'applicationsByStage' => $applicationsByStage,
            'latestSubmissionsByApplicationKey' => $latestSubmissionsByApplicationKey,
            'nextActionStatusesByApplicationId' => $nextActionStatusesByApplicationId,
            'selectedInterviewApplication' => $selectedInterviewApplication,
            'selectedEvaluationApplication' => $selectedEvaluationApplication,
            'interviewerOptions' => $this->interviewerOptions($selectedInterviewApplication),
            'workplaceOptions' => $this->workplaceOptions($selectedInterviewApplication),
            'pipelineActionPermissions' => $pipelineActionPermissions,
        ]);
    }

    private function findManageableApplication(int $applicationId): Application
    {
        $application = Application::query()
            ->with(['job'])
            ->findOrFail($applicationId);

        abort_unless($this->canManageApplication(Auth::user(), $application), 403);

        return $application;
    }

    private function canManageApplication(?User $user, Application $application): bool
    {
        $workflowGuard = app(ApplicationWorkflowGuard::class);

        return $workflowGuard->canRunHrPipelineActions($user)
            && $workflowGuard->canAccessApplicationBranch($user, $application);
    }

    private function canScheduleInterview(Application $application): bool
    {
        return app(ApplicationWorkflowGuard::class)->canManageInterview(Auth::user(), $application);
    }

    private function canEvaluateInterview(Application $application): bool
    {
        return app(ApplicationWorkflowGuard::class)->canEvaluateInterview(Auth::user(), $application);
    }

    private function selectedInterviewApplication(): ?Application
    {
        if (! $this->interviewApplicationId) {
            return null;
        }

        return Application::query()
            ->with(['job.branch'])
            ->find($this->interviewApplicationId);
    }

    private function selectedEvaluationApplication(): ?Application
    {
        if (! $this->evaluationApplicationId) {
            return null;
        }

        return Application::query()
            ->with(['job.branch', 'latestInterview.scorecards', 'latestScorecard'])
            ->find($this->evaluationApplicationId);
    }

    private function interviewerOptions(?Application $application): array
    {
        if (! $application) {
            return [];
        }

        $branchId = (int) ($application->branch_id ?: $application->job?->branch_id);

        return User::query()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->whereIn('role', ['hr', 'pm', 'director'])
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (User $user): array => [
                $user->id => trim($user->name.' - '.strtoupper((string) $user->role)),
            ])
            ->all();
    }

    private function workplaceOptions(?Application $application): array
    {
        if (! $application) {
            return [];
        }

        $branchId = (int) ($application->branch_id ?: $application->job?->branch_id);

        return Workplace::query()
            ->where('branch_id', $branchId)
            ->where('is_interview_room', true)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Workplace $workplace): array => [
                $workplace->id => trim(implode(' - ', array_filter([
                    $workplace->name,
                    $workplace->room ? 'Phòng '.$workplace->room : null,
                    $workplace->floor ? 'Tầng '.$workplace->floor : null,
                ]))),
            ])
            ->all();
    }

    private function nextActionStatus(Application $application, ApplicationPipelineService $pipelineService): ?StatusApplicationEnum
    {
        $currentStatus = $application->status instanceof StatusApplicationEnum
            ? $application->status
            : StatusApplicationEnum::tryFrom((string) $application->status);

        if ($currentStatus === StatusApplicationEnum::SCREENING) {
            return null;
        }

        if ($currentStatus === StatusApplicationEnum::INTERVIEWING) {
            $latestScorecard = $application->latestScorecard;

            if (! $latestScorecard || $latestScorecard->conclusion !== 'pass') {
                return null;
            }
        }

        if ($currentStatus === StatusApplicationEnum::OFFERED
            && ($application->latestOffer?->status !== 'accepted' || ! $application->latestOffer?->accepted_at)
        ) {
            return null;
        }

        return collect($pipelineService->allowedTransitions($application->status))
            ->first(fn (StatusApplicationEnum $status): bool => $status !== StatusApplicationEnum::REJECTED);
    }

    private function nextActionStatusesByApplicationId(Collection $applications): array
    {
        $pipelineService = app(ApplicationPipelineService::class);

        return $applications
            ->mapWithKeys(function (Application $application) use ($pipelineService): array {
                $status = $this->nextActionStatus($application, $pipelineService);

                return [
                    $application->id => $status ? [
                        'value' => $status->value,
                        'label' => $this->statusLabel($status),
                    ] : null,
                ];
            })
            ->all();
    }

    private function statusLabel(StatusApplicationEnum $status): string
    {
        return match ($status) {
            StatusApplicationEnum::CV_REVIEWING => 'Chờ sàng lọc CV',
            StatusApplicationEnum::SCREENING => 'Sơ tuyển',
            StatusApplicationEnum::INTERVIEW_SCHEDULED => 'Đã lên lịch phỏng vấn',
            StatusApplicationEnum::INTERVIEWING => 'Chờ đánh giá phỏng vấn',
            StatusApplicationEnum::OFFERED => 'Đề nghị tuyển dụng',
            StatusApplicationEnum::HIRED => 'Đã tuyển',
            StatusApplicationEnum::REJECTED => 'Từ chối',
        };
    }

    private function latestSubmissionsByApplicationKey(Collection $applications): array
    {
        $candidateIds = $applications->pluck('candidate_id')->filter()->unique()->values();
        $jobIds = $applications->pluck('job_id')->filter()->unique()->values();

        if ($candidateIds->isEmpty() || $jobIds->isEmpty()) {
            return [];
        }

        return CandidateJobSubmission::query()
            ->whereIn('candidate_id', $candidateIds->all())
            ->whereIn('job_id', $jobIds->all())
            ->latest()
            ->get()
            ->unique(fn (CandidateJobSubmission $submission): string => $this->submissionKey($submission->candidate_id, $submission->job_id))
            ->keyBy(fn (CandidateJobSubmission $submission): string => $this->submissionKey($submission->candidate_id, $submission->job_id))
            ->all();
    }

    private function applicationStatusValue(Application $application): string
    {
        return $application->status instanceof StatusApplicationEnum
            ? $application->status->value
            : (string) $application->status;
    }

    private function submissionKey(int $candidateId, int $jobId): string
    {
        return $candidateId.':'.$jobId;
    }
}
