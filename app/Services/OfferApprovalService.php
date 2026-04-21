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
    ) {}

    /**
     * Approve an offer - sends to candidate and notifies team
     */
    public function approve(Offer $offer, User $approver): bool
    {
        try {
            $offer->loadMissing(['application.candidate', 'application.job.branch']);

            // Ensure PDF is fresh
            if ($offer->offer_letter_template_id) {
                $this->pdfService->refreshForOffer($offer);
                $offer->refresh();
            }

            // Send to candidate
            $candidate = $offer->application?->candidate;
            if (!$candidate?->email) {
                Log::warning('Cannot send offer to candidate - no email', ['offer_id' => $offer->id]);
                return false;
            }

            Mail::to($candidate->email)->send(
                new CandidateOfferMail(
                    $candidate,
                    $offer->application,
                    $offer->application->job,
                    $offer
                )
            );

            // Update offer status
            $offer->forceFill([
                'status' => 'pending',
                'approved_by_user_id' => $approver->id,
                'approved_at' => now(),
                'sent_at' => now(),
                'expires_at' => now()->addDays(3), // Set expiration if not set
            ])->save();

            // Notify team members (HR, PM, Director)
            $this->notifyTeam($offer, $approver);

            Log::info('Offer approved successfully', [
                'offer_id' => $offer->id,
                'approved_by' => $approver->id,
            ]);

            return true;
        } catch (\Throwable $exception) {
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
            $offer->forceFill([
                'status' => 'rejected',
                'approved_by_user_id' => $rejector->id,
                'approved_at' => now(),
                'approval_notes' => $notes,
            ])->save();

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
