<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RecruitmentJob;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class RecruitmentJobPolicy
{
    use HandlesAuthorization;
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RecruitmentJob');
    }

    public function view(AuthUser $authUser, RecruitmentJob $recruitmentJob): bool
    {
        return $authUser->can('View:RecruitmentJob');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RecruitmentJob');
    }

    public function update(AuthUser $authUser, RecruitmentJob $recruitmentJob): bool
    {
        return $authUser->can('Update:RecruitmentJob');
    }

    public function delete(AuthUser $authUser, RecruitmentJob $recruitmentJob): bool
    {
        return $authUser->can('Delete:RecruitmentJob');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RecruitmentJob');
    }

    public function restore(AuthUser $authUser, RecruitmentJob $recruitmentJob): bool
    {
        return $authUser->can('Restore:RecruitmentJob');
    }

    public function forceDelete(AuthUser $authUser, RecruitmentJob $recruitmentJob): bool
    {
        return $authUser->can('ForceDelete:RecruitmentJob');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RecruitmentJob');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RecruitmentJob');
    }

    public function replicate(AuthUser $authUser, RecruitmentJob $recruitmentJob): bool
    {
        return $authUser->can('Replicate:RecruitmentJob');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RecruitmentJob');
    }
}
