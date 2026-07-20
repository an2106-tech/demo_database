<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Application;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApplicationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Application');
    }

    public function view(AuthUser $authUser, Application $application): bool
    {
        return $authUser->can('View:Application')
            && $this->canAccessApplicationBranch($authUser, $application);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Application')
            && $this->canManageHrPipeline($authUser);
    }

    public function update(AuthUser $authUser, Application $application): bool
    {
        return $authUser->can('Update:Application')
            && $this->canManageHrPipeline($authUser)
            && $this->canAccessApplicationBranch($authUser, $application);
    }

    public function delete(AuthUser $authUser, Application $application): bool
    {
        return $this->canOverseeRecruitment($authUser)
            && $authUser->can('Delete:Application');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $this->canOverseeRecruitment($authUser)
            && $authUser->can('DeleteAny:Application');
    }

    public function restore(AuthUser $authUser, Application $application): bool
    {
        return $this->canOverseeRecruitment($authUser)
            && $authUser->can('Restore:Application');
    }

    public function forceDelete(AuthUser $authUser, Application $application): bool
    {
        return $this->canOverseeRecruitment($authUser)
            && $authUser->can('ForceDelete:Application');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $this->canOverseeRecruitment($authUser)
            && $authUser->can('ForceDeleteAny:Application');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $this->canOverseeRecruitment($authUser)
            && $authUser->can('RestoreAny:Application');
    }

    public function replicate(AuthUser $authUser, Application $application): bool
    {
        return $authUser->can('Replicate:Application');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Application');
    }

    private function canOverseeRecruitment(AuthUser $authUser): bool
    {
        return (method_exists($authUser, 'isSuperAdmin') && $authUser->isSuperAdmin())
            || ($authUser->role ?? null) === 'admin';
    }

    private function isHr(AuthUser $authUser): bool
    {
        return ($authUser->role ?? null) === 'hr'
            || (method_exists($authUser, 'hasRole') && $authUser->hasRole('hr'));
    }

    private function canManageHrPipeline(AuthUser $authUser): bool
    {
        return $this->canOverseeRecruitment($authUser) || $this->isHr($authUser);
    }

    private function canAccessApplicationBranch(AuthUser $authUser, Application $application): bool
    {
        if ($this->canOverseeRecruitment($authUser)) {
            return true;
        }

        if (! method_exists($authUser, 'branchScopeId')) {
            return false;
        }

        $scopeBranchId = $authUser->branchScopeId();
        $applicationBranchId = $application->branch_id ?: $application->job?->branch_id;

        return $scopeBranchId !== null
            && $applicationBranchId !== null
            && (int) $scopeBranchId === (int) $applicationBranchId;
    }
}
