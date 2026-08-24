<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:User');
    }

    public function view(AuthUser $authUser, User $user): bool
    {
        return $authUser->can('View:User');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:User')
            && ($this->isSuperAdmin($authUser) || $authUser->role === 'director');
    }

    public function update(AuthUser $authUser, User $user): bool
    {
        if (! $authUser->can('Update:User')) {
            return false;
        }

        if ($this->isSuperAdmin($authUser) || $authUser->getAuthIdentifier() === $user->getKey()) {
            return true;
        }

        return $authUser->role === 'director'
            && (int) $authUser->branch_id === (int) $user->branch_id
            && in_array($user->role, ['hr', 'pm'], true);
    }

    public function delete(AuthUser $authUser, User $user): bool
    {
        return $authUser->getAuthIdentifier() !== $user->getKey()
            && $authUser->can('Delete:User')
            && ($this->isSuperAdmin($authUser) || (
                $authUser->role === 'director'
                && (int) $authUser->branch_id === (int) $user->branch_id
                && in_array($user->role, ['hr', 'pm'], true)
            ));
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $this->isSuperAdmin($authUser) && $authUser->can('DeleteAny:User');
    }

    public function restore(AuthUser $authUser, User $user): bool
    {
        return $this->isSuperAdmin($authUser) && $authUser->can('Restore:User');
    }

    public function forceDelete(AuthUser $authUser, User $user): bool
    {
        return $authUser->getAuthIdentifier() !== $user->getKey()
            && $this->isSuperAdmin($authUser)
            && $authUser->can('ForceDelete:User');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $this->isSuperAdmin($authUser) && $authUser->can('ForceDeleteAny:User');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $this->isSuperAdmin($authUser) && $authUser->can('RestoreAny:User');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $this->isSuperAdmin($authUser) && $authUser->can('Replicate:User');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $this->isSuperAdmin($authUser) && $authUser->can('Reorder:User');
    }

    private function isSuperAdmin(AuthUser $authUser): bool
    {
        return $authUser->role === 'admin'
            || (method_exists($authUser, 'isSuperAdmin') && $authUser->isSuperAdmin());
    }
}
