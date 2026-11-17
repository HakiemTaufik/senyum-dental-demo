<?php

namespace App\Policies;

use App\Models\User;

/**
 * Who may manage staff accounts: only the owner.
 * The owner also can't delete their own account, which prevents
 * locking everyone out of the admin panel by accident.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->canManageStaff();
    }

    public function view(User $user, User $model): bool
    {
        return $user->role->canManageStaff();
    }

    public function create(User $user): bool
    {
        return $user->role->canManageStaff();
    }

    public function update(User $user, User $model): bool
    {
        return $user->role->canManageStaff();
    }

    public function delete(User $user, User $model): bool
    {
        return $user->role->canManageStaff() && $user->isNot($model);
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function restore(User $user, User $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
