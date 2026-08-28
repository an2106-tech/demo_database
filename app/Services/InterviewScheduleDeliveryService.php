<?php

namespace App\Services;

use App\Enums\StatusApplicationEnum;
use App\Mail\InterviewScheduledMail;
use App\Models\Application;
use Illuminate\Support\Facades\Log;

class InterviewScheduleDeliveryService
{
    public function __construct(
        private readonly InterviewCalendarService $calendarService,
        private readonly RecruitmentInternalNotificationService $internalNotifications,
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

        $interview = $application->interviews()
            ->with('evaluators.user:id,name,email')
            ->latest('id')
            ->first();
        $interviewer = $interview?->interviewer;

        // Keep the candidate mail type when the interviewer happens to share an address.
        if (filled($interviewer?->email) && ! isset($recipients[$interviewer->email])) {
            $recipients[$interviewer->email] = 'lead';
        }

        foreach ($interview?->evaluators ?? [] as $assignment) {
            $evaluator = $assignment->user;
            if (filled($evaluator?->email) && ! isset($recipients[$evaluator->email])) {
                $recipients[$evaluator->email] = $assignment->role === 'lead' ? 'lead' : 'evaluator';
            }
        }

        return $recipients;
    }

    /**
     * @return array{sent: int, failed: int, candidate_sent: bool, is_update: bool, has_interview: bool}
     */
    public function deliver(Application $application): array
    {
        $interview = $application->interviews()->latest('id')->first();

        if (! $interview) {
            return [
                'sent' => 0,
                'failed' => 0,
                'candidate_sent' => false,
                'is_update' => false,
                'has_interview' => false,
            ];
        }

        $interview->loadMissing(['application.job.branch', 'application.candidate', 'interviewer', 'evaluators.user', 'workplace']);
        $this->calendarService->store($interview);

        $freshApplication = $application->fresh(['job.branch', 'candidate']) ?? $application;
        $recipients = $this->recipients($freshApplication);

        if (! array_key_exists((string) $freshApplication->snapshotCandidateEmail(), $recipients)) {
            return [
                'sent' => 0,
                'failed' => 0,
                'candidate_sent' => false,
                'is_update' => filled($interview->invite_sent_at),
                'has_interview' => true,
            ];
        }

        // Send the candidate first. Internal recipients should not receive a schedule
        // that the candidate did not receive, and retries must not create duplicate mail.
        $candidateEmail = (string) $freshApplication->snapshotCandidateEmail();
        $candidateRecipient = [$candidateEmail => $recipients[$candidateEmail]];
        unset($recipients[$candidateEmail]);
        $recipients = $candidateRecipient + $recipients;
        $sentCount = 0;
        $failedCount = 0;
        $candidateSent = false;
        $isUpdate = filled($interview->invite_sent_at);

        foreach ($recipients as $email => $recipientLabel) {
            try {
                app(OutboundMailQueue::class)->queue(
                    $email,
                    new InterviewScheduledMail($interview, $recipientLabel, $isUpdate),
                );
                $sentCount++;
                $candidateSent = $candidateSent || $recipientLabel === 'candidate';
            } catch (\Throwable $exception) {
                $failedCount++;

                Log::warning('Failed to send interview schedule mail.', [
                    'application_id' => $application->id,
                    'interview_id' => $interview->id,
                    'recipient' => $email,
                    'error' => $exception->getMessage(),
                ]);

                if ($recipientLabel === 'candidate') {
                    break;
                }
            }
        }

        // A schedule is only considered delivered when the candidate received it.
        if ($candidateSent) {
            $interview->forceFill(['invite_sent_at' => now()])->save();
            $this->internalNotifications->notifyInterviewPanelAssigned($interview, $isUpdate);

            $status = $application->status instanceof StatusApplicationEnum
                ? $application->status->value
                : (string) $application->status;

            $application->recordStatusHistory(
                $status,
                $status,
                $isUpdate
                    ? "Đã đưa cập nhật lịch phỏng vấn tới {$sentCount} email vào hàng đợi gửi."
                    : "Đã đưa lịch phỏng vấn tới {$sentCount} email vào hàng đợi gửi.",
            );
        }

        return [
            'sent' => $sentCount,
            'failed' => $failedCount,
            'candidate_sent' => $candidateSent,
            'is_update' => $isUpdate,
            'has_interview' => true,
        ];
    }
}
