<?php

namespace App\Livewire\Client\Employers;

use App\Enums\StatusApplicationEnum;
use App\Mail\InterviewScheduledMail;
use App\Models\Application;
use App\Models\CandidateJobSubmission;
use App\Models\Interview;
use App\Models\RecruitmentJob;
use App\Models\User;
use App\Models\Workplace;
use App\Services\ApplicationPipelineService;
use App\Services\InterviewCalendarService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
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
        $application = $this->findManageableApplication($applicationId);

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

    public function saveInterviewSchedule(): void
    {
        $pipelineService = app(ApplicationPipelineService::class);
        $calendarService = app(InterviewCalendarService::class);
        $application = $this->findManageableApplication((int) $this->interviewApplicationId);

        abort_unless($this->canScheduleInterview($application), 403);

        $this->validate($this->interviewRules($application), [], [
            'interviewForm.round_name' => 'tên vòng phỏng vấn',
            'interviewForm.scheduled_at' => 'thời gian phỏng vấn',
            'interviewForm.duration_minutes' => 'thời lượng',
            'interviewForm.type' => 'hình thức',
            'interviewForm.meeting_link' => 'link phỏng vấn',
            'interviewForm.workplace_id' => 'địa điểm phỏng vấn',
            'interviewForm.interviewer_id' => 'người phỏng vấn',
            'interviewForm.notes' => 'ghi chú',
        ]);

        $scheduledAt = Carbon::parse($this->interviewForm['scheduled_at'], config('app.interview_timezone', 'Asia/Ho_Chi_Minh'));

        if ($scheduledAt->lt(now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh')))) {
            throw ValidationException::withMessages([
                'interviewForm.scheduled_at' => 'Thời gian phỏng vấn không được ở quá khứ.',
            ]);
        }

        $existingInterview = $application->interviews()->latest('id')->first();
        $roundNumber = (int) ($existingInterview?->round_number ?: 1);
        $interview = $existingInterview ?? new Interview([
            'application_id' => $application->id,
            'round_number' => $roundNumber,
            'result' => 'pending',
        ]);

        $interview->fill([
            'application_id' => $application->id,
            'interviewer_id' => (int) $this->interviewForm['interviewer_id'],
            'round_name' => trim((string) $this->interviewForm['round_name']) ?: 'Phỏng vấn vòng '.$roundNumber,
            'duration_minutes' => (int) $this->interviewForm['duration_minutes'],
            'scheduled_at' => $scheduledAt,
            'type' => $this->interviewForm['type'],
            'meeting_link' => $this->interviewForm['type'] === 'online' ? trim((string) $this->interviewForm['meeting_link']) : null,
            'workplace_id' => $this->interviewForm['type'] === 'offline' ? (int) $this->interviewForm['workplace_id'] : null,
            'notes' => trim((string) $this->interviewForm['notes']) ?: null,
        ]);
        $interview->save();

        $interview->loadMissing(['application.candidate', 'application.job.branch', 'interviewer', 'workplace']);
        $calendarService->store($interview);

        $status = $application->status instanceof StatusApplicationEnum
            ? $application->status
            : StatusApplicationEnum::tryFrom((string) $application->status);

        if ($status === StatusApplicationEnum::SCREENING) {
            $pipelineService->transition(
                $application,
                StatusApplicationEnum::INTERVIEW_SCHEDULED,
                Auth::user(),
                $this->interviewScheduleComment($interview, false),
            );
        } else {
            $application->recordStatusHistory(
                $status?->value,
                $status?->value ?? StatusApplicationEnum::INTERVIEW_SCHEDULED->value,
                $this->interviewScheduleComment($interview, (bool) $existingInterview),
            );
        }

        $this->sendInterviewNotifications($interview);
        $this->closeInterviewScheduler();

        $this->dispatch('app-notify', message: $existingInterview ? 'Đã cập nhật lịch phỏng vấn.' : 'Đã tạo lịch phỏng vấn.');
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

    public function rejectApplication(int $applicationId): void
    {
        $pipelineService = app(ApplicationPipelineService::class);
        $application = $this->findManageableApplication($applicationId);

        try {
            $pipelineService->transition(
                $application,
                StatusApplicationEnum::REJECTED,
                Auth::user(),
                'HR từ chối nhanh từ Pipeline.'
            );
        } catch (ValidationException $exception) {
            $this->dispatch('app-notify', message: $exception->getMessage(), type: 'error');

            return;
        }

        $this->dispatch('app-notify', message: 'Đã chuyển hồ sơ sang trạng thái Từ chối.', type: 'warning');
    }
    #[Layout('layouts.employer')]
    public function render()
    {
        /** @var \App\Models\User $user */
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
        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin() || in_array($user->role, ['admin', 'director'], true)) {
            $branchId = $user->branchScopeId();

            return ! $branchId || (int) ($application->branch_id ?: $application->job?->branch_id) === (int) $branchId;
        }

        $branchId = $user->branchScopeId();

        if ($branchId) {
            return (int) ($application->branch_id ?: $application->job?->branch_id) === (int) $branchId;
        }

        return (int) $application->job?->created_by === (int) $user->id;
    }

    private function canScheduleInterview(Application $application): bool
    {
        $status = $application->status instanceof StatusApplicationEnum
            ? $application->status
            : StatusApplicationEnum::tryFrom((string) $application->status);

        return in_array($status, [
            StatusApplicationEnum::SCREENING,
            StatusApplicationEnum::INTERVIEW_SCHEDULED,
            StatusApplicationEnum::INTERVIEWING,
        ], true);
    }

    private function canEvaluateInterview(Application $application): bool
    {
        $status = $application->status instanceof StatusApplicationEnum
            ? $application->status
            : StatusApplicationEnum::tryFrom((string) $application->status);

        return in_array($status, [
            StatusApplicationEnum::INTERVIEW_SCHEDULED,
            StatusApplicationEnum::INTERVIEWING,
        ], true) && $application->interviews()->exists();
    }

    private function interviewRules(Application $application): array
    {
        $branchId = (int) ($application->branch_id ?: $application->job?->branch_id);

        return [
            'interviewForm.round_name' => ['required', 'string', 'max:100'],
            'interviewForm.scheduled_at' => ['required', 'date'],
            'interviewForm.duration_minutes' => ['required', 'integer', Rule::in([30, 45, 60, 90])],
            'interviewForm.type' => ['required', Rule::in(['online', 'offline'])],
            'interviewForm.meeting_link' => [
                Rule::requiredIf(($this->interviewForm['type'] ?? 'online') === 'online'),
                'nullable',
                'url',
                'max:500',
            ],
            'interviewForm.workplace_id' => [
                Rule::requiredIf(($this->interviewForm['type'] ?? 'online') === 'offline'),
                'nullable',
                Rule::exists('workplaces', 'id')->where(fn ($query) => $query
                    ->where('branch_id', $branchId)
                    ->where('is_interview_room', true)
                    ->where('is_active', true)),
            ],
            'interviewForm.interviewer_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('branch_id', $branchId)
                    ->where('is_active', true)
                    ->whereIn('role', ['hr', 'pm', 'director'])),
            ],
            'interviewForm.notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function interviewScheduleComment(Interview $interview, bool $isUpdate): string
    {
        $scheduledAt = $interview->scheduled_at
            ? $interview->scheduled_at->copy()->setTimezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->format('H:i, d/m/Y')
            : '-';
        $type = $interview->type === 'offline' ? 'Offline' : 'Online';
        $location = app(InterviewCalendarService::class)->resolveLocation($interview);
        $prefix = $isUpdate ? 'Đã cập nhật lịch phỏng vấn' : 'Đã tạo lịch phỏng vấn';

        return sprintf('%s: %s, %s, %d phút, %s.', $prefix, $scheduledAt, $type, (int) ($interview->duration_minutes ?: 60), $location);
    }

    private function sendInterviewNotifications(Interview $interview): void
    {
        $recipients = [];
        $candidateEmail = $interview->application?->snapshotCandidateEmail();
        $interviewerEmail = $interview->interviewer?->email;

        if (filled($candidateEmail)) {
            $recipients[$candidateEmail] = 'candidate';
        }

        if (filled($interviewerEmail)) {
            $recipients[$interviewerEmail] = 'interviewer';
        }

        $sentCount = 0;

        foreach ($recipients as $email => $label) {
            try {
                Mail::to($email)->send(new InterviewScheduledMail($interview, $label));
                $sentCount++;
            } catch (\Throwable $exception) {
                Log::warning('Failed to send HR portal interview schedule mail.', [
                    'interview_id' => $interview->id,
                    'recipient' => $email,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        if ($sentCount > 0) {
            $interview->forceFill(['invite_sent_at' => now()])->save();
        }
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


