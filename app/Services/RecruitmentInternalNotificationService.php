<?php

namespace App\Services;

use App\Filament\Resources\Applications\ApplicationResource;
use App\Filament\Resources\OfferResource;
use App\Models\Application;
use App\Models\Offer;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RecruitmentInternalNotificationService
{
    public function notifyOfferSubmittedForApproval(Offer $offer): void
    {
        $offer->loadMissing(['application.candidate', 'application.job.branch']);

        $this->notifyUsers(
            $this->branchUsers($this->branchId($offer), ['director']),
            'offer_approval_requested',
            'Cần duyệt đề nghị tuyển dụng',
            '',
            OfferResource::getUrl('edit', ['record' => $offer]),
            $offer,
            $this->offerNotificationContext($offer, 'Mở đề nghị'),
        );
    }

    public function notifyOfferRejectedByDirector(Offer $offer, ?User $rejector = null): void
    {
        $offer->loadMissing(['application.candidate', 'application.job.branch', 'application.assignedHr', 'application.job.creator']);

        $notes = trim((string) $offer->approval_notes);
        $message = $notes !== ''
            ? 'Giám đốc ghi chú: '.$notes
            : 'Giám đốc đã gửi đề nghị lại để HR chỉnh sửa.';

        $this->notifyUsers(
            $this->offerOperators($offer)->reject(fn (User $user): bool => $rejector && $user->is($rejector)),
            'offer_rejected_by_director',
            'Đề nghị cần chỉnh sửa',
            $message,
            ApplicationResource::getUrl('view', ['record' => $offer->application]),
            $offer,
            $this->offerNotificationContext($offer, 'Xem ghi chú'),
        );
    }

    public function notifyOfferAcceptedByCandidate(Offer $offer): void
    {
        $offer->loadMissing(['application.candidate', 'application.job.branch', 'application.assignedHr', 'application.job.creator']);

        $this->notifyUsers(
            $this->offerTeam($offer),
            'offer_accepted_by_candidate',
            'Ứng viên đã đồng ý đề nghị',
            '',
            ApplicationResource::getUrl('view', ['record' => $offer->application]),
            $offer,
            $this->offerNotificationContext($offer, 'Xem hồ sơ'),
        );
    }

    public function notifyOfferDeclinedByCandidate(Offer $offer): void
    {
        $offer->loadMissing(['application.candidate', 'application.job.branch', 'application.assignedHr', 'application.job.creator']);

        $reason = trim((string) $offer->declined_reason);
        $message = $reason !== ''
            ? 'Lý do phản hồi: '.$reason
            : 'Cần trao đổi hướng xử lý tiếp theo với ứng viên.';

        $this->notifyUsers(
            $this->offerTeam($offer),
            'offer_declined_by_candidate',
            'Ứng viên đã từ chối đề nghị',
            $message,
            ApplicationResource::getUrl('view', ['record' => $offer->application]),
            $offer,
            $this->offerNotificationContext($offer, 'Xem phản hồi'),
        );
    }

    public function notifyOfferExpired(Offer $offer): void
    {
        $offer->loadMissing(['application.candidate', 'application.job.branch', 'application.assignedHr', 'application.job.creator']);

        $this->notifyUsers(
            $this->offerTeam($offer),
            'offer_expired',
            'Đề nghị đã hết hạn phản hồi',
            'Ứng viên chưa phản hồi trước hạn. HR cần xem lại hướng xử lý hồ sơ.',
            ApplicationResource::getUrl('view', ['record' => $offer->application]),
            $offer,
            $this->offerNotificationContext($offer, 'Xem hồ sơ'),
        );
    }

    /**
     * @param  Collection<int, User>  $users
     */
    private function notifyUsers(Collection $users, string $type, string $title, string $message, string $url, Offer $offer, array $context = []): void
    {
        $application = $offer->application;

        $users
            ->filter(fn (User $user): bool => (bool) $user->id)
            ->unique('id')
            ->each(function (User $user) use ($type, $title, $message, $url, $offer, $application, $context): void {
                UserNotification::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'data' => array_filter([
                        'title' => $title,
                        'message' => $message,
                        'url' => $url,
                        'offer_id' => $offer->id,
                        'application_id' => $application?->id,
                        ...$context,
                    ], fn (mixed $value): bool => $value !== null && $value !== ''),
                ]);
            });
    }

    /**
     * @return Collection<int, User>
     */
    private function offerTeam(Offer $offer): Collection
    {
        return $this->offerOperators($offer)
            ->merge($this->branchUsers($this->branchId($offer), ['director']))
            ->unique('id')
            ->values();
    }

    /**
     * @return Collection<int, User>
     */
    private function offerOperators(Offer $offer): Collection
    {
        $application = $offer->application;

        $operators = collect([
            $application?->assignedHr,
            $application?->job?->creator,
        ])
            ->filter()
            ->merge($this->branchUsers($this->branchId($offer), ['hr']))
            ->filter(fn (User $user): bool => (bool) $user->is_active)
            ->unique('id')
            ->values();

        return $operators;
    }

    /**
     * @param  array<int, string>  $roles
     * @return Collection<int, User>
     */
    private function branchUsers(?int $branchId, array $roles): Collection
    {
        if (! $branchId) {
            return collect();
        }

        return User::query()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->where(function (Builder $query) use ($roles): void {
                $query->whereIn('role', $roles)
                    ->orWhereHas('roles', fn (Builder $roleQuery) => $roleQuery->whereIn('name', $roles));
            })
            ->get();
    }

    private function branchId(Offer $offer): ?int
    {
        return $offer->application?->job?->branch_id
            ?? $offer->application?->branch_id;
    }

    /** @return array<string, string> */
    private function offerNotificationContext(Offer $offer, string $actionLabel): array
    {
        $application = $offer->application;
        $candidateName = $application?->snapshotCandidateName() ?: 'Ứng viên';
        $jobTitle = $application?->job?->title ?: 'vị trí tuyển dụng';
        $branchName = $application?->job?->branch?->name ?: $application?->branch?->name;

        return [
            'subject' => $candidateName,
            'context' => trim($jobTitle.($branchName ? ' · '.$branchName : '')),
            'action_label' => $actionLabel,
        ];
    }
}
