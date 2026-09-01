<?php

namespace App\Services;

use App\Enums\StatusApplicationEnum;
use App\Mail\CandidateOfferMail;
use App\Mail\OfferApprovedNotificationMail;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class OfferApprovalService
{
    private ?string $lastError = null;

    public function __construct(
        private OfferPdfService $pdfService,
        private RecruitmentInternalNotificationService $internalNotifications,
    ) {}

    /**
     * Approve an offer - sends to candidate and notifies team
     */
    public function approve(Offer $offer, User $approver): bool
    {
        $this->lastError = null;
        $invitationHandedOff = false;

        try {
            $offer->loadMissing(['application.candidate', 'application.job.branch']);

            if (! $this->canReviewOffer($offer, $approver)) {
                $this->lastError = 'Đề nghị không còn ở trạng thái chờ duyệt hoặc bạn không có quyền xử lý.';
                Log::warning('Unauthorized offer approval attempt.', [
                    'offer_id' => $offer->id,
                    'user_id' => $approver->id,
                ]);

                return false;
            }

            $responseDeadline = $offer->expires_at;
            $startDate = $offer->start_date?->copy()->startOfDay();

            if (! $responseDeadline || ! $responseDeadline->isFuture()) {
                $this->lastError = 'Hạn phản hồi đã qua. HR cần cập nhật đề nghị trước khi gửi lại duyệt.';

                return false;
            }

            if (! $startDate || $startDate->isBefore(now()->startOfDay())) {
                $this->lastError = 'Ngày bắt đầu dự kiến không còn hợp lệ. HR cần cập nhật đề nghị.';

                return false;
            }

            if ($responseDeadline->greaterThanOrEqualTo($startDate)) {
                $this->lastError = 'Hạn phản hồi phải trước ngày bắt đầu dự kiến.';

                return false;
            }

            // Send to candidate
            $candidate = $offer->application?->candidate;
            if (! $candidate?->email) {
                $this->lastError = 'Ứng viên chưa có địa chỉ email để nhận thư mời.';
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
                $this->lastError = 'Đề nghị vừa được người khác xử lý. Vui lòng tải lại trang.';

                return false;
            }

            $offer->forceFill([
                'status' => 'pending',
                'sent_at' => $sentAt,
                'expires_at' => $responseDeadline,
                'approved_by_user_id' => $approver->id,
                'approved_at' => $sentAt,
                'updated_at' => $sentAt,
            ])->syncOriginal();

            app(OutboundMailQueue::class)->queue(
                $candidate->email,
                new CandidateOfferMail(
                    $candidate,
                    $offer->application,
                    $offer->application->job,
                    $offer
                ),
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
                    $this->internalNotifications->notifyOfferSentToCandidate($queuedOffer);
                    $this->notifyTeam($queuedOffer, $queuedApprover);
                }
            });

            Log::info('Offer approved successfully', [
                'offer_id' => $offer->id,
                'approved_by' => $approver->id,
            ]);

            return true;
        } catch (\Throwable $exception) {
            $this->lastError ??= 'Không thể duyệt đề nghị lúc này. Vui lòng thử lại hoặc kiểm tra queue.';
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

    public function lastError(): ?string
    {
        return $this->lastError;
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

            $application = $offer->application;
            if ($application) {
                $status = $application->status instanceof StatusApplicationEnum
                    ? $application->status->value
                    : (string) $application->status;
                $application->recordStatusHistory(
                    $status,
                    $status,
                    'Giám đốc yêu cầu chỉnh sửa đề nghị tuyển dụng. Ghi chú: '.trim($notes),
                );
            }

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

        if (! $branchId) {
            return;
        }

        // Get all relevant team members
        $teamMembers = User::query()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->where(function (Builder $query): void {
                $query
                    ->whereIn('role', ['director', 'hr', 'pm'])
                    ->orWhereHas('roles', fn (Builder $roleQuery) => $roleQuery->whereIn('name', ['director', 'hr', 'pm']));
            })
            ->with('roles')
            ->get();

        /** @var User $user */
        foreach ($teamMembers as $user) {
            if (! $user->email) {
                continue;
            }

            try {
                // Determine user's role
                $role = $user->roles?->first()?->name ?? $user->role ?? 'hr';

                app(OutboundMailQueue::class)->queue(
                    $user->email,
                    new OfferApprovedNotificationMail(
                        $offer,
                        $offer->application,
                        $offer->application->job,
                        $user,
                        $role
                    ),
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
