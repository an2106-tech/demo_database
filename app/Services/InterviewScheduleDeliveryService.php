<?php

namespace App\Services;

use App\Enums\StatusApplicationEnum;
use App\Mail\InterviewScheduledMail;
use App\Models\Application;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InterviewScheduleDeliveryService
{
    public function __construct(
        private readonly InterviewCalendarService $calendarService,
    ) {}

    /**
     * @return array<string, string>
     */
    public function recipients(Application $application): array
    {
        $recipients = [];

        if (filled($application->snapshotCandidateEmail())) {
            $recipients[$application->snapshotCandidateEmail()] = 'candidate';
        }

        $branchId = $application->job?->branch_id;

        if (! $branchId) {
            return $recipients;
        }

        User::query()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', ['director', 'pm']))
            ->get()
            ->each(function (User $user) use (&$recipients): void {
                if (filled($user->email)) {
                    $recipients[$user->email] = $user->role ?: 'internal';
                }
            });

        return $recipients;
    }

    /**
     * @return array{sent: int, failed: int, is_update: bool, has_interview: bool}
     */
    public function deliver(Application $application): array
    {
        $interview = $application->interviews()->latest('id')->first();

        if (! $interview) {
            return [
                'sent' => 0,
                'failed' => 0,
                'is_update' => false,
                'has_interview' => false,
            ];
        }

        $interview->loadMissing(['application.job.branch', 'application.candidate', 'interviewer', 'workplace']);
        $this->calendarService->store($interview);

        $freshApplication = $application->fresh(['job.branch', 'candidate']) ?? $application;
        $recipients = $this->recipients($freshApplication);
        $sentCount = 0;
        $failedCount = 0;
        $isUpdate = filled($interview->invite_sent_at);

        foreach ($recipients as $email => $recipientLabel) {
            try {
                Mail::to($email)->send(new InterviewScheduledMail($interview, $recipientLabel, $isUpdate));
                $sentCount++;
            } catch (\Throwable $exception) {
                $failedCount++;

                Log::warning('Failed to send interview schedule mail.', [
                    'application_id' => $application->id,
                    'interview_id' => $interview->id,
                    'recipient' => $email,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        if ($sentCount > 0) {
            $interview->forceFill(['invite_sent_at' => now()])->save();

            $status = $application->status instanceof StatusApplicationEnum
                ? $application->status->value
                : (string) $application->status;

            $application->recordStatusHistory(
                $status,
                $status,
                $isUpdate
                    ? "Đã gửi cập nhật lịch phỏng vấn tới {$sentCount} email."
                    : "Đã gửi lịch phỏng vấn tới {$sentCount} email.",
            );
        }

        return [
            'sent' => $sentCount,
            'failed' => $failedCount,
            'is_update' => $isUpdate,
            'has_interview' => true,
        ];
    }
}
