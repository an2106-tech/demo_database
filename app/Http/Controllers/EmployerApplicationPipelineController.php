<?php

namespace App\Http\Controllers;

use App\Enums\StatusApplicationEnum;
use App\Mail\InterviewScheduledMail;
use App\Models\Application;
use App\Models\Interview;
use App\Models\Scorecard;
use App\Models\User;
use App\Services\ApplicationPipelineService;
use App\Services\ApplicationWorkflowGuard;
use App\Services\InterviewCalendarService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EmployerApplicationPipelineController extends Controller
{
    public function advance(
        Request $request,
        Application $application,
        ApplicationPipelineService $pipelineService,
        ApplicationWorkflowGuard $workflowGuard,
    ): RedirectResponse {
        $user = $this->authenticatedUser($request);
        $application->loadMissing(['job', 'latestScorecard', 'latestOffer']);

        $this->authorizePipelineAccess($user, $application, $workflowGuard);

        $currentStatus = $this->status($application);

        if ($currentStatus === StatusApplicationEnum::SCREENING) {
            return back()->with('warning', 'Vui lòng dùng nút Lên lịch PV để chuyển hồ sơ sang vòng phỏng vấn.');
        }

        if ($currentStatus === StatusApplicationEnum::INTERVIEWING
            && $application->latestScorecard?->conclusion !== 'pass'
        ) {
            return back()->with('warning', 'Cần có đánh giá phỏng vấn đạt trước khi chuyển sang đề nghị tuyển dụng.');
        }

        $nextStatus = collect($pipelineService->allowedTransitions($application->status))
            ->first(fn (StatusApplicationEnum $status): bool => $status !== StatusApplicationEnum::REJECTED);

        if (! $nextStatus) {
            return back()->with('warning', 'Hồ sơ này chưa có bước kế tiếp phù hợp.');
        }

        try {
            $pipelineService->transition(
                $application,
                $nextStatus,
                $user,
                'HR chuyển nhanh từ Pipeline.',
            );
        } catch (ValidationException $exception) {
            return back()->with('error', $exception->errors()['status'][0] ?? 'Không thể chuyển trạng thái hồ sơ.');
        }

        return back()->with('message', 'Đã chuyển hồ sơ sang: '.$this->statusLabel($nextStatus).'.');
    }

    public function scheduleInterview(
        Request $request,
        Application $application,
        ApplicationPipelineService $pipelineService,
        ApplicationWorkflowGuard $workflowGuard,
        InterviewCalendarService $calendarService,
    ): RedirectResponse {
        $user = $this->authenticatedUser($request);
        $application->loadMissing(['candidate', 'job.branch', 'latestInterview']);

        abort_unless($workflowGuard->canManageInterview($user, $application), 403);

        $branchId = $workflowGuard->applicationBranchId($application);
        abort_unless($branchId, 403);

        $validated = $request->validate([
            'round_name' => ['required', 'string', 'max:100'],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', Rule::in([30, 45, 60, 90])],
            'type' => ['required', Rule::in(['online', 'offline'])],
            'meeting_link' => [
                Rule::requiredIf($request->input('type') === 'online'),
                'nullable',
                'url',
                'max:500',
            ],
            'workplace_id' => [
                Rule::requiredIf($request->input('type') === 'offline'),
                'nullable',
                Rule::exists('workplaces', 'id')->where(fn ($query) => $query
                    ->where('branch_id', $branchId)
                    ->where('is_interview_room', true)
                    ->where('is_active', true)),
            ],
            'interviewer_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('branch_id', $branchId)
                    ->where('is_active', true)
                    ->whereIn('role', ['hr', 'pm', 'director'])),
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $scheduledAt = Carbon::parse(
            $validated['scheduled_at'],
            config('app.interview_timezone', 'Asia/Ho_Chi_Minh'),
        );

        if ($scheduledAt->lt(now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh')))) {
            return back()->withInput()->with('error', 'Thời gian phỏng vấn không được ở quá khứ.');
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
            'interviewer_id' => (int) $validated['interviewer_id'],
            'round_name' => trim((string) $validated['round_name']) ?: 'Phỏng vấn vòng '.$roundNumber,
            'duration_minutes' => (int) $validated['duration_minutes'],
            'scheduled_at' => $scheduledAt,
            'type' => $validated['type'],
            'meeting_link' => $validated['type'] === 'online' ? trim((string) ($validated['meeting_link'] ?? '')) : null,
            'workplace_id' => $validated['type'] === 'offline' ? (int) $validated['workplace_id'] : null,
            'notes' => trim((string) ($validated['notes'] ?? '')) ?: null,
        ]);
        $interview->save();

        $interview->loadMissing(['application.candidate', 'application.job.branch', 'interviewer', 'workplace']);
        $calendarService->store($interview);

        $status = $this->status($application);
        $comment = $this->interviewScheduleComment($interview, (bool) $existingInterview, $calendarService);

        if ($status === StatusApplicationEnum::SCREENING) {
            $pipelineService->transition(
                $application,
                StatusApplicationEnum::INTERVIEW_SCHEDULED,
                $user,
                $comment,
            );
        } else {
            $application->recordStatusHistory(
                $status?->value,
                $status?->value ?? StatusApplicationEnum::INTERVIEW_SCHEDULED->value,
                $comment,
            );
        }

        $this->sendInterviewNotifications($interview);

        return redirect()
            ->route('employers.application_pipeline')
            ->with('message', $existingInterview ? 'Đã cập nhật lịch phỏng vấn.' : 'Đã tạo lịch phỏng vấn.');
    }

    public function evaluateInterview(
        Request $request,
        Application $application,
        ApplicationPipelineService $pipelineService,
        ApplicationWorkflowGuard $workflowGuard,
    ): RedirectResponse {
        $user = $this->authenticatedUser($request);
        $application->loadMissing(['job.branch', 'latestInterview']);

        abort_unless($workflowGuard->canEvaluateInterview($user, $application), 403);

        $interview = $application->interviews()->latest('id')->firstOrFail();
        $validated = $request->validate([
            'technical_score' => ['required', 'numeric', 'min:0', 'max:10'],
            'problem_solving_score' => ['required', 'numeric', 'min:0', 'max:10'],
            'communication_score' => ['required', 'numeric', 'min:0', 'max:10'],
            'culture_score' => ['required', 'numeric', 'min:0', 'max:10'],
            'conclusion' => ['required', Rule::in(['pass', 'hold', 'fail'])],
            'notes' => ['nullable', 'string', 'max:1500'],
            'rejected_reason' => [
                Rule::requiredIf($request->input('conclusion') === 'fail'),
                'nullable',
                'string',
                'min:5',
                'max:1000',
            ],
        ], [
            'rejected_reason.required' => 'Vui lòng nhập lý do từ chối khi kết luận không đạt.',
        ]);

        $criteria = [
            ['name' => 'Kinh nghiem va chuyen mon', 'score' => (float) $validated['technical_score'], 'note' => null],
            ['name' => 'Tu duy giai quyet van de', 'score' => (float) $validated['problem_solving_score'], 'note' => null],
            ['name' => 'Giao tiep va phoi hop', 'score' => (float) $validated['communication_score'], 'note' => null],
            ['name' => 'Phu hop van hoa FPT Education', 'score' => (float) $validated['culture_score'], 'note' => null],
        ];
        $average = round(collect($criteria)->avg('score'), 2);
        $conclusion = $validated['conclusion'];
        $rejectedReason = trim((string) ($validated['rejected_reason'] ?? ''));

        DB::transaction(function () use (
            $application,
            $interview,
            $user,
            $criteria,
            $average,
            $conclusion,
            $validated,
            $rejectedReason,
            $pipelineService,
        ): void {
            $scorecard = Scorecard::withTrashed()->firstOrNew([
                'interview_id' => $interview->id,
                'evaluator_id' => $user->id,
            ]);

            if ($scorecard->trashed()) {
                $scorecard->restore();
            }

            $scorecardData = [
                'application_id' => $application->id,
                'interview_id' => $interview->id,
                'evaluator_id' => $user->id,
                'criteria' => $criteria,
                'average_score' => $average,
                'conclusion' => $conclusion,
                'notes' => trim((string) ($validated['notes'] ?? '')) ?: null,
            ];

            if (Schema::hasColumn('scorecards', 'recommended_conclusion')) {
                $scorecardData['recommended_conclusion'] = $conclusion;
            }

            $scorecard->fill($scorecardData);
            $scorecard->save();

            $interview->forceFill([
                'result' => $conclusion === 'hold' ? 'pending' : $conclusion,
            ])->save();

            $status = $this->status($application);
            $comment = 'Đánh giá phỏng vấn: '.match ($conclusion) {
                'pass' => 'Đạt',
                'fail' => 'Không đạt',
                default => 'Cần theo dõi/phỏng vấn thêm',
            }.'. Điểm TB: '.number_format($average, 2).'/10.'
                .(filled($validated['notes'] ?? null) ? ' Nhận xét: '.trim((string) $validated['notes']) : '');

            if ($status === StatusApplicationEnum::INTERVIEW_SCHEDULED) {
                $pipelineService->transition(
                    $application,
                    StatusApplicationEnum::INTERVIEWING,
                    $user,
                    $conclusion === 'hold' ? $comment : 'Đã ghi nhận đánh giá phỏng vấn trước khi chuyển bước tiếp theo.',
                );
                $application->refresh();
            }

            if ($conclusion === 'pass') {
                $application->forceFill(['rejected_reason' => null])->save();
                $pipelineService->transition($application, StatusApplicationEnum::OFFERED, $user, $comment);
            } elseif ($conclusion === 'fail') {
                $application->forceFill(['rejected_reason' => $rejectedReason])->save();
                $pipelineService->transition(
                    $application,
                    StatusApplicationEnum::REJECTED,
                    $user,
                    $comment.' Lý do từ chối: '.$rejectedReason,
                );
            } elseif ($status !== StatusApplicationEnum::INTERVIEW_SCHEDULED) {
                $application->recordStatusHistory(
                    StatusApplicationEnum::INTERVIEWING->value,
                    StatusApplicationEnum::INTERVIEWING->value,
                    $comment,
                );
            }
        });

        return redirect()
            ->route('employers.application_pipeline')
            ->with('message', 'Đã lưu đánh giá phỏng vấn.');
    }

    private function authenticatedUser(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        return $user;
    }

    private function authorizePipelineAccess(
        User $user,
        Application $application,
        ApplicationWorkflowGuard $workflowGuard,
    ): void {
        abort_unless(
            $workflowGuard->canRunHrPipelineActions($user)
                && $workflowGuard->canAccessApplicationBranch($user, $application),
            403,
        );
    }

    private function status(Application $application): ?StatusApplicationEnum
    {
        return $application->status instanceof StatusApplicationEnum
            ? $application->status
            : StatusApplicationEnum::tryFrom((string) $application->status);
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

    private function interviewScheduleComment(
        Interview $interview,
        bool $isUpdate,
        InterviewCalendarService $calendarService,
    ): string {
        $scheduledAt = $interview->scheduled_at
            ? $interview->scheduled_at->copy()->setTimezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->format('H:i, d/m/Y')
            : '-';
        $type = $interview->type === 'offline' ? 'Offline' : 'Online';
        $location = $calendarService->resolveLocation($interview);
        $prefix = $isUpdate ? 'Đã cập nhật lịch phỏng vấn' : 'Đã tạo lịch phỏng vấn';

        return sprintf('%s: %s, %s, %d phút, %s.', $prefix, $scheduledAt, $type, (int) ($interview->duration_minutes ?: 60), $location);
    }

    private function sendInterviewNotifications(Interview $interview): void
    {
        $recipients = [];

        if (filled($interview->application?->snapshotCandidateEmail())) {
            $recipients[$interview->application->snapshotCandidateEmail()] = 'candidate';
        }

        if (filled($interview->interviewer?->email)) {
            $recipients[$interview->interviewer->email] = 'interviewer';
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
}
