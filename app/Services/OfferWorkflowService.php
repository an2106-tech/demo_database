<?php

namespace App\Services;

use App\Enums\StatusApplicationEnum;
use App\Mail\OfferApprovalRequestMail;
use App\Models\Application;
use App\Models\Offer;
use App\Models\OfferLetterTemplate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OfferWorkflowService
{
    public function __construct(
        private ApplicationWorkflowGuard $workflowGuard,
        private OfferPdfService $pdfService,
        private RecruitmentInternalNotificationService $internalNotifications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function saveDraft(Application $application, array $data, ?User $actor): Offer
    {
        if (! $this->workflowGuard->canManageOffer($actor, $application)) {
            throw ValidationException::withMessages([
                'offer' => 'Tài khoản hiện tại không thể tạo hoặc chỉnh sửa đề nghị tuyển dụng cho hồ sơ này.',
            ]);
        }

        $data = $this->validateDraftData($application, $data);

        return DB::transaction(function () use ($application, $data): Offer {
            $existingOffer = $application->offers()->latest('id')->lockForUpdate()->first();
            $createReplacement = $this->workflowGuard->shouldCreateReplacementOffer($existingOffer);

            if (! $createReplacement && ! $this->workflowGuard->canEditOffer($existingOffer)) {
                throw ValidationException::withMessages([
                    'offer' => $this->lockedOfferMessage($existingOffer),
                ]);
            }

            $offer = $createReplacement || ! $existingOffer
                ? new Offer(['application_id' => $application->id, 'status' => 'draft'])
                : $existingOffer;
            $template = filled($data['offer_letter_template_id'] ?? null)
                ? OfferLetterTemplate::query()->find($data['offer_letter_template_id'])
                : null;

            $offer->fill([
                'application_id' => $application->id,
                'offer_letter_template_id' => $data['offer_letter_template_id'] ?? null,
                'letter_template_snapshot' => $template
                    ? ['name' => $template->name, 'body_html' => $template->body_html]
                    : null,
                'salary_offered' => $data['salary_offered'],
                'salary_adjustment_reason' => $data['salary_adjustment_reason'],
                'start_date' => $data['start_date'],
                'probation_months' => $data['probation_months'],
                'expires_at' => $data['expires_at'],
                'content' => $data['content'] ?? '',
            ]);
            $offer->forceFill([
                'status' => 'draft',
                'approval_requested_at' => null,
                'approved_by_user_id' => null,
                'approved_at' => null,
                // Keep the director's note visible while HR prepares the
                // revised draft. It is cleared only after a successful resend.
                'approval_notes' => $createReplacement ? null : $offer->approval_notes,
                'sent_at' => null,
                'response_at' => null,
                'accepted_at' => null,
                'declined_reason' => null,
            ]);

            $pdfNeedsRefresh = ! $offer->exists
                || $offer->isDirty([
                    'application_id',
                    'offer_letter_template_id',
                    'letter_template_snapshot',
                    'content',
                    'salary_offered',
                    'start_date',
                    'probation_months',
                    'expires_at',
                ])
                || ! $this->pdfService->hasValidPdf($offer);

            $offer->save();

            try {
                if ($pdfNeedsRefresh) {
                    $this->pdfService->refreshForOffer($offer);
                }
            } catch (\Throwable) {
                throw ValidationException::withMessages([
                    'offer' => 'Chưa thể tạo PDF đề nghị. Vui lòng kiểm tra lại mẫu và thử lại.',
                ]);
            }

            return $offer;
        });
    }

    /**
     * @return array{sent: int, failed: int, resubmitted: bool}
     */
    public function submitForApproval(Application $application, ?User $actor): array
    {
        if (! $this->workflowGuard->canManageOffer($actor, $application)) {
            throw ValidationException::withMessages([
                'offer' => 'Tài khoản hiện tại không thể gửi đề nghị tuyển dụng của hồ sơ này để duyệt.',
            ]);
        }

        $offer = $application->offers()->latest('id')->first();
        if (! $offer || ! in_array($offer->status, ['draft', 'rejected'], true)) {
            throw ValidationException::withMessages([
                'offer' => 'Chỉ có thể gửi duyệt khi đề nghị đang là bản nháp hoặc đã được trả về để điều chỉnh.',
            ]);
        }

        $directors = $this->branchDirectors($application);
        if ($directors->isEmpty()) {
            throw ValidationException::withMessages([
                'offer' => 'Chưa có giám đốc chi nhánh đang hoạt động để duyệt đề nghị này.',
            ]);
        }

        $wasRevision = filled($offer->approval_notes);
        $resubmitted = filled($offer->approval_requested_at) || $wasRevision;
        $previousStatus = $offer->status;
        $previousApprovalRequestedAt = $offer->approval_requested_at;
        $this->pdfService->ensureForOffer($offer);
        $offer->forceFill([
            'status' => 'awaiting_approval',
            'approval_requested_at' => now(),
        ])->save();

        $sent = 0;
        $failed = 0;

        foreach ($directors as $director) {
            try {
                app(OutboundMailQueue::class)->queue(
                    $director->email,
                    new OfferApprovalRequestMail($offer, $application, $application->job, $director),
                );
                $sent++;
            } catch (\Throwable $exception) {
                $failed++;
                Log::warning('Failed to send offer approval request mail.', [
                    'application_id' => $application->id,
                    'offer_id' => $offer->id,
                    'recipient' => $director->email,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        if ($sent === 0) {
            $offer->forceFill([
                'status' => $previousStatus,
                'approval_requested_at' => $previousApprovalRequestedAt,
            ])->save();

            throw ValidationException::withMessages([
                'offer' => 'Chưa gửi được yêu cầu duyệt đến giám đốc. Đề nghị chưa chuyển sang chờ duyệt, vui lòng kiểm tra và gửi lại.',
            ]);
        }

        if ($previousStatus === 'rejected' || $wasRevision) {
            $application->recordStatusHistory(
                $this->statusValue($application),
                $this->statusValue($application),
                'HR đã cập nhật và gửi lại đề nghị tuyển dụng để giám đốc duyệt.',
            );
        }

        $offer->forceFill(['approval_notes' => null])->save();
        $offer->loadMissing([
            'application.candidate',
            'application.job.branch',
            'application.assignedHr',
            'application.job.creator',
        ]);
        $this->internalNotifications->notifyOfferSubmittedForApproval($offer);

        return ['sent' => $sent, 'failed' => $failed, 'resubmitted' => $resubmitted];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function validateDraftData(Application $application, array $data): array
    {
        $timezone = config('app.interview_timezone', 'Asia/Ho_Chi_Minh');
        $salary = $data['salary_offered'] ?? null;
        $probationMonths = $data['probation_months'] ?? null;

        if (filter_var($salary, FILTER_VALIDATE_INT) === false || (int) $salary <= 0) {
            throw ValidationException::withMessages(['salary_offered' => 'Mức lương đề nghị phải lớn hơn 0.']);
        }

        $salaryAdjustmentReason = trim((string) ($data['salary_adjustment_reason'] ?? ''));
        if ($this->isOutsidePublishedSalaryRange($application, (int) $salary) && $salaryAdjustmentReason === '') {
            throw ValidationException::withMessages([
                'salary_adjustment_reason' => 'Vui lòng nêu lý do khi mức lương đề nghị nằm ngoài khung lương đã công khai.',
            ]);
        }

        if (mb_strlen($salaryAdjustmentReason) > 1000) {
            throw ValidationException::withMessages([
                'salary_adjustment_reason' => 'Lý do điều chỉnh lương không được vượt quá 1.000 ký tự.',
            ]);
        }

        if (filter_var($probationMonths, FILTER_VALIDATE_INT) === false || (int) $probationMonths < 0 || (int) $probationMonths > 6) {
            throw ValidationException::withMessages(['probation_months' => 'Thời gian thử việc phải nằm trong khoảng 0-6 tháng.']);
        }

        try {
            $startDate = Carbon::parse($data['start_date'] ?? null, $timezone)->startOfDay();
        } catch (\Throwable) {
            throw ValidationException::withMessages(['start_date' => 'Ngày bắt đầu dự kiến không hợp lệ.']);
        }

        if ($startDate->lt(now($timezone)->startOfDay())) {
            throw ValidationException::withMessages(['start_date' => 'Ngày bắt đầu dự kiến không được ở quá khứ.']);
        }

        try {
            $expiresAt = Carbon::parse($data['expires_at'] ?? null, $timezone);
        } catch (\Throwable) {
            throw ValidationException::withMessages(['expires_at' => 'Hạn phản hồi đề nghị không hợp lệ.']);
        }

        if ($expiresAt->lte(now($timezone))) {
            throw ValidationException::withMessages(['expires_at' => 'Hạn phản hồi đề nghị phải ở tương lai.']);
        }

        if ($expiresAt->gte($startDate)) {
            throw ValidationException::withMessages(['expires_at' => 'Hạn phản hồi cần trước ngày bắt đầu làm việc dự kiến.']);
        }

        $templateId = $data['offer_letter_template_id'] ?? null;
        if (blank($templateId) || ! OfferLetterTemplate::query()
            ->whereKey($templateId)
            ->where('is_active', true)
            ->exists()) {
            throw ValidationException::withMessages([
                'offer_letter_template_id' => 'Vui lòng chọn mẫu thư mời đang áp dụng.',
            ]);
        }

        $data['salary_offered'] = (int) $salary;
        $data['salary_adjustment_reason'] = $salaryAdjustmentReason !== '' ? $salaryAdjustmentReason : null;
        $data['probation_months'] = (int) $probationMonths;
        $data['start_date'] = $startDate->toDateString();
        $data['expires_at'] = $expiresAt;

        return $data;
    }

    private function isOutsidePublishedSalaryRange(Application $application, int $salary): bool
    {
        $range = $application->job?->salary_range;
        if (! is_array($range)) {
            return false;
        }

        // An offer is stored in VND. Do not compare it with a public range in
        // another currency until the system owns a conversion-rate policy.
        if (strtoupper((string) ($range['currency'] ?? 'VND')) !== 'VND') {
            return false;
        }

        $min = isset($range['min']) && is_numeric($range['min']) ? (int) $range['min'] : null;
        $max = isset($range['max']) && is_numeric($range['max']) ? (int) $range['max'] : null;

        return ($min !== null && $salary < $min) || ($max !== null && $salary > $max);
    }

    /** @return Collection<int, User> */
    private function branchDirectors(Application $application): Collection
    {
        $branchId = $application->job?->branch_id ?? $application->branch_id;

        if (! $branchId) {
            return collect();
        }

        return User::query()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->whereNotNull('email')
            ->where(function ($query): void {
                $query->where('role', 'director')
                    ->orWhereHas('roles', fn ($roleQuery) => $roleQuery->where('name', 'director'));
            })
            ->get();
    }

    private function lockedOfferMessage(?Offer $offer): string
    {
        return match ($offer?->status) {
            'awaiting_approval' => 'Đề nghị đang chờ giám đốc duyệt nên chưa thể chỉnh sửa.',
            'pending' => 'Đề nghị đã gửi ứng viên phản hồi nên chưa thể chỉnh sửa.',
            default => 'Trạng thái đề nghị hiện tại không cho phép chỉnh sửa.',
        };
    }

    private function statusValue(Application $application): string
    {
        return $application->status instanceof StatusApplicationEnum
            ? $application->status->value
            : (string) $application->status;
    }
}
