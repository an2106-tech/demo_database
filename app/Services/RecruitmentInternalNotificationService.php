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
            'Có đề nghị tuyển dụng chờ duyệt',
            $this->offerMessage($offer, 'HR đã gửi đề nghị tuyển dụng cần giám đốc chi nhánh xem xét.'),
            OfferResource::getUrl('edit', ['record' => $offer]),
            $offer,
        );
    }

    public function notifyOfferRejectedByDirector(Offer $offer, ?User $rejector = null): void
    {
        $offer->loadMissing(['application.candidate', 'application.job.branch', 'application.assignedHr', 'application.job.creator']);

        $actor = $rejector?->name ? ' bởi '.$rejector->name : '';
        $notes = trim((string) $offer->approval_notes);
        $message = $this->offerMessage($offer, 'Đề nghị tuyển dụng đã bị từ chối'.$actor.'.')
            .($notes !== '' ? ' Lý do: '.$notes : '');

        $this->notifyUsers(
            $this->offerOperators($offer)->reject(fn (User $user): bool => $rejector && $user->is($rejector)),
            'offer_rejected_by_director',
            'Đề nghị tuyển dụng cần điều chỉnh',
            $message,
            ApplicationResource::getUrl('view', ['record' => $offer->application]),
            $offer,
        );
    }

    public function notifyOfferAcceptedByCandidate(Offer $offer): void
    {
        $offer->loadMissing(['application.candidate', 'application.job.branch', 'application.assignedHr', 'application.job.creator']);

        $this->notifyUsers(
            $this->offerTeam($offer),
            'offer_accepted_by_candidate',
            'Ứng viên đã đồng ý đề nghị',
            $this->offerMessage($offer, 'Ứng viên đã xác nhận đồng ý đề nghị tuyển dụng.'),
            ApplicationResource::getUrl('view', ['record' => $offer->application]),
            $offer,
        );
    }

    public function notifyOfferDeclinedByCandidate(Offer $offer): void
    {
        $offer->loadMissing(['application.candidate', 'application.job.branch', 'application.assignedHr', 'application.job.creator']);

        $reason = trim((string) $offer->declined_reason);
        $message = $this->offerMessage($offer, 'Ứng viên đã từ chối đề nghị tuyển dụng.')
            .($reason !== '' ? ' Lý do: '.$reason : '');

        $this->notifyUsers(
            $this->offerTeam($offer),
            'offer_declined_by_candidate',
            'Ứng viên đã từ chối đề nghị',
            $message,
            ApplicationResource::getUrl('view', ['record' => $offer->application]),
            $offer,
        );
    }

    /**
     * @param  Collection<int, User>  $users
     */
    private function notifyUsers(Collection $users, string $type, string $title, string $message, string $url, Offer $offer): void
    {
        $application = $offer->application;

        $users
            ->filter(fn (User $user): bool => (bool) $user->id)
            ->unique('id')
            ->each(function (User $user) use ($type, $title, $message, $url, $offer, $application): void {
                UserNotification::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'data' => [
                        'title' => $title,
                        'message' => $message,
                        'url' => $url,
                        'offer_id' => $offer->id,
                        'application_id' => $application?->id,
                    ],
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
            ->unique('id')
            ->values();

        return $operators->isNotEmpty()
            ? $operators
            : $this->branchUsers($this->branchId($offer), ['hr']);
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

    private function offerMessage(Offer $offer, string $prefix): string
    {
        $application = $offer->application;
        $candidateName = $application?->snapshotCandidateName() ?: 'Ứng viên';
        $jobTitle = $application?->job?->title ?: 'vị trí tuyển dụng';
        $branchName = $application?->job?->branch?->name ?: $application?->branch?->name;

        return trim($prefix.' '.$candidateName.' - '.$jobTitle.($branchName ? ' tại '.$branchName : '').'.');
    }
}
