<?php

namespace App\Services;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Models\Interview;
use App\Models\Offer;
use App\Models\User;

class ApplicationWorkflowGuard
{
    public function canOverseeRecruitment(?User $user): bool
    {
        return (bool) ($user?->isSuperAdmin() || $user?->role === 'admin');
    }

    public function isHr(?User $user): bool
    {
        return (bool) ($user?->role === 'hr' || $user?->hasRole('hr'));
    }

    public function canRunHrPipelineActions(?User $user): bool
    {
        return $this->canOverseeRecruitment($user) || $this->isHr($user);
    }

    public function applicationBranchId(Application $application): ?int
    {
        $branchId = $application->branch_id ?: $application->job?->branch_id;

        return $branchId ? (int) $branchId : null;
    }

    public function canAccessApplicationBranch(?User $user, Application $application): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->canOverseeRecruitment($user)) {
            return true;
        }

        $scopeBranchId = $user->branchScopeId();
        $applicationBranchId = $this->applicationBranchId($application);

        return $scopeBranchId !== null
            && $applicationBranchId !== null
            && (int) $scopeBranchId === (int) $applicationBranchId;
    }

    public function isAssignedInterviewer(?User $user, Application $application): bool
    {
        if (! $user?->id) {
            return false;
        }

        $interview = $this->latestInterview($application);

        return (bool) $interview && (int) $interview->interviewer_id === (int) $user->id;
    }

    public function canScreenApplication(?User $user, Application $application): bool
    {
        return $this->status($application) === StatusApplicationEnum::NEW
            && $this->canRunHrPipelineActions($user)
            && $this->canAccessApplicationBranch($user, $application);
    }

    public function canManageInterview(?User $user, Application $application): bool
    {
        if (! $this->canRunHrPipelineActions($user) || ! $this->canAccessApplicationBranch($user, $application)) {
            return false;
        }

        $status = $this->status($application);

        if ($status === StatusApplicationEnum::SCREENING) {
            return true;
        }

        if (! in_array($status, [StatusApplicationEnum::INTERVIEW_SCHEDULED, StatusApplicationEnum::INTERVIEW], true)) {
            return false;
        }

        $interview = $this->latestInterview($application);

        if (! $interview || $interview->result !== 'pending' || $interview->scorecards()->exists()) {
            return false;
        }

        return ! $interview->scheduled_at || $interview->scheduled_at->gt(now());
    }

    public function hasInterviewStatus(Application $application): bool
    {
        return in_array($this->statusValue($application), [
            StatusApplicationEnum::INTERVIEW_SCHEDULED->value,
            StatusApplicationEnum::INTERVIEW->value,
        ], true);
    }

    public function canEvaluateInterview(?User $user, Application $application): bool
    {
        $status = $this->status($application);

        if (! in_array($status, [StatusApplicationEnum::INTERVIEW_SCHEDULED, StatusApplicationEnum::INTERVIEW], true)) {
            return false;
        }

        $interview = $this->latestInterview($application);

        if (! $interview || $interview->result !== 'pending') {
            return false;
        }

        if (! $this->canAccessApplicationBranch($user, $application)) {
            return false;
        }

        if (! $this->canOverseeRecruitment($user) && ! $this->isAssignedInterviewer($user, $application)) {
            return false;
        }

        if ($status === StatusApplicationEnum::INTERVIEW) {
            return true;
        }

        return $interview->scheduled_at?->lte(now()) ?? false;
    }

    public function canSendInterviewSchedule(?User $user, Application $application): bool
    {
        if (! $this->canManageInterview($user, $application)) {
            return false;
        }

        $interview = $this->latestInterview($application);

        if (! $interview) {
            return false;
        }

        return blank($interview->invite_sent_at)
            || (
                $interview->updated_at
                && $interview->invite_sent_at
                && $interview->updated_at->gt($interview->invite_sent_at)
            );
    }

    public function canRejectApplication(?User $user, Application $application): bool
    {
        return $this->canRunHrPipelineActions($user)
            && $this->canAccessApplicationBranch($user, $application)
            && in_array($this->status($application), [
                StatusApplicationEnum::NEW,
                StatusApplicationEnum::SCREENING,
                StatusApplicationEnum::INTERVIEW_SCHEDULED,
                StatusApplicationEnum::INTERVIEW,
                StatusApplicationEnum::OFFER,
            ], true);
    }

    public function canReopenOfferResponse(?User $user, Application $application): bool
    {
        return $user?->hasRole('super_admin') === true
            && in_array($application->latestOffer?->status, ['accepted', 'declined', 'expired'], true);
    }

    public function canManageOffer(?User $user, Application $application): bool
    {
        return $this->statusValue($application) === StatusApplicationEnum::OFFER->value
            && $this->canRunHrPipelineActions($user)
            && $this->canAccessApplicationBranch($user, $application);
    }

    public function canEditOffer(?Offer $offer): bool
    {
        return ! $offer || in_array($offer->status, ['draft', 'rejected'], true);
    }

    public function shouldCreateReplacementOffer(?Offer $offer): bool
    {
        return $offer && in_array($offer->status, ['declined', 'expired'], true);
    }

    private function status(Application $application): ?StatusApplicationEnum
    {
        return $application->status instanceof StatusApplicationEnum
            ? $application->status
            : StatusApplicationEnum::tryFrom((string) $application->status);
    }

    private function statusValue(Application $application): ?string
    {
        return $application->status instanceof StatusApplicationEnum
            ? $application->status->value
            : $application->status;
    }

    private function latestInterview(Application $application): ?Interview
    {
        return $application->latestInterview ?? $application->interviews()->latest('id')->first();
    }
}
