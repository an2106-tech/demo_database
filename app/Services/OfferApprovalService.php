<?php

namespace App\Services;

use App\Mail\CandidateOfferMail;
use App\Mail\OfferApprovedNotificationMail;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OfferApprovalService
{
    public function __construct(
        private OfferPdfService $pdfService,
        private RecruitmentInternalNotificationService $internalNotifications,
    ) {}

    /**
     * Approve an offer - sends to candidate and notifies team
     */
    public function approve(Offer $offer, User $approver): bool
    {
        $invitationHandedOff = false;

        try {
            $offer->loadMissing(['application.candidate', 'application.job.branch']);

            if (! $this->canReviewOffer($offer, $approver)) {
                Log::warning('Unauthorized offer approval attempt.', [
                    'offer_id' => $offer->id,
                    'user_id' => $approver->id,
                ]);

                return false;
            }

            $responseDeadline = $offer->expires_at && $offer->expires_at->isFuture()
                ? $offer->expires_at
                : now()->addDays(3);

            if (! $offer->expires_at || ! $offer->expires_at->equalTo($responseDeadline)) {
                $offer->forceFill([
                    'expires_at' => $responseDeadline,
                ])->save();
                $offer->refresh();
            }

            // Send to candidate
            $candidate = $offer->application?->candidate;
            if (!$candidate?->email) {
                Log::warning('Cannot send offer to candidate - no email', ['offer_id' => $offer->id]);
                return false;
            }

            $sentAt = now();

            // Regenerate immediately before delivery so the attachment always
            // carries the approved issuance date and actual approver details.
            $this->pdfService->refreshForOffer(
                $offer,
                issuedAt: $sentAt,
                responseDeadline: $responseDeadline,
                approver: $approver,
            );
            $offer->refresh();

            // Claim the approval atomically. A second director click must not send
            // another invitation while the first request is handing off the email.
            $claimed = Offer::query()
                ->whereKey($offer->id)
                ->where('status', 'awaiting_approval')
                ->update([
                    'status' => 'pending',
                    'sent_at' => $sentAt,
                    'expires_at' => $responseDeadline,
                    'approved_by_user_id' => $approver->id,
                    'approved_at' => $sentAt,
                    'updated_at' => $sentAt,
                ]);

            if ($claimed !== 1) {
                return false;
            }

            $offer->refresh();

            Mail::to($candidate->email)->send(
                new CandidateOfferMail(
                    $candidate,
                    $offer->application,
                    $offer->application->job,
                    $offer
                )
            );
            $invitationHandedOff = true;

            // Internal email must not keep the director waiting after the
            // candidate invitation has been handed off successfully.
            $offerId = $offer->id;
            $approverId = $approver->id;

            defer(function () use ($offerId, $approverId): void {
                $queuedOffer = Offer::query()->find($offerId);
                $queuedApprover = User::query()->find($approverId);

                if ($queuedOffer && $queuedApprover) {
                    $this->notifyTeam($queuedOffer, $queuedApprover);
                }
            });

            Log::info('Offer approved successfully', [
                'offer_id' => $offer->id,
                'approved_by' => $approver->id,
            ]);

            return true;
        } catch (\Throwable $exception) {
            if (! $invitationHandedOff) {
                Offer::query()
                    ->whereKey($offer->id)
                    ->where('status', 'pending')
                    ->whereNull('response_at')
                    ->update([
                        'status' => 'awaiting_approval',
                        'sent_at' => null,
                        'approved_by_user_id' => null,
                        'approved_at' => null,
                        'updated_at' => now(),
                    ]);
            }

            Log::error('Error approving offer', [
                'offer_id' => $offer->id,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Reject an offer - keep application status but mark offer as rejected
     */
    public function reject(Offer $offer, User $rejector, string $notes = ''): bool
    {
        try {
            $offer->loadMissing(['application.job.branch']);

            if (! $this->canReviewOffer($offer, $rejector)) {
                Log::warning('Unauthorized offer rejection attempt.', [
                    'offer_id' => $offer->id,
                    'user_id' => $rejector->id,
                ]);

                return false;
            }

            if (mb_strlen(trim($notes)) < 10) {
                Log::warning('Offer adjustment request rejected because notes are too short.', [
                    'offer_id' => $offer->id,
                    'user_id' => $rejector->id,
                ]);

                return false;
            }

            $offer->forceFill([
                'status' => 'rejected',
                'approved_by_user_id' => $rejector->id,
                'approved_at' => now(),
                'approval_notes' => $notes,
            ])->save();
            $offer->refresh();

            $this->internalNotifications->notifyOfferRejectedByDirector($offer, $rejector);

            Log::info('Offer rejected', [
                'offer_id' => $offer->id,
                'rejected_by' => $rejector->id,
                'notes' => $notes,
            ]);

            return true;
        } catch (\Throwable $exception) {
            Log::error('Error rejecting offer', [
                'offer_id' => $offer->id,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function canReviewOffer(Offer $offer, User $user): bool
    {
        if ($offer->status !== 'awaiting_approval') {
            return false;
        }

        if ($user->isSuperAdmin() || $user->role === 'admin') {
            return true;
        }

        $isDirector = $user->role === 'director' || $user->hasRole('director');
        if (! $isDirector || ! $user->branchScopeId()) {
            return false;
        }

        $branchId = $offer->application?->job?->branch_id;

        return $branchId && (int) $branchId === (int) $user->branchScopeId();
    }

    /**
     * Notify team members about approved offer
     */
    protected function notifyTeam(Offer $offer, User $approver): void
    {
        $offer->loadMissing(['application.job.branch']);
        $branchId = $offer->application?->job?->branch_id;

        if (!$branchId) {
            return;
        }

        // Get all relevant team members
        $teamMembers = User::query()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['director', 'hr', 'pm']))
            ->with('roles')
            ->get();

        /** @var User $user */
        foreach ($teamMembers as $user) {
            if (!$user->email) {
                continue;
            }

            try {
                // Determine user's role
                $role = $user->roles?->first()?->name ?? $user->role ?? 'hr';

                Mail::to($user->email)->send(
                    new OfferApprovedNotificationMail(
                        $offer,
                        $offer->application,
                        $offer->application->job,
                        $user,
                        $role
                    )
                );
            } catch (\Throwable $exception) {
                Log::warning('Failed to send offer approval notification', [
                    'offer_id' => $offer->id,
                    'user_id' => $user->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }
}
