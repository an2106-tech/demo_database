<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Workplace;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkplacePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Workplace');
    }

    public function view(AuthUser $authUser, Workplace $workplace): bool
    {
        return $authUser->can('View:Workplace');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Workplace');
    }

    public function update(AuthUser $authUser, Workplace $workplace): bool
    {
        return $authUser->can('Update:Workplace');
    }

    public function delete(AuthUser $authUser, Workplace $workplace): bool
    {
        return $authUser->can('Delete:Workplace');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Workplace');
    }

    public function restore(AuthUser $authUser, Workplace $workplace): bool
    {
        return $authUser->can('Restore:Workplace');
    }

    public function forceDelete(AuthUser $authUser, Workplace $workplace): bool
    {
        return $authUser->can('ForceDelete:Workplace');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Workplace');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Workplace');
    }

    public function replicate(AuthUser $authUser, Workplace $workplace): bool
    {
        return $authUser->can('Replicate:Workplace');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Workplace');
    }

}