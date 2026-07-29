<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Only Admins may list all users (Admin bypasses via Gate::before).
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * A user may view their own record; Admin bypasses via Gate::before.
     */
    public function view(User $user, User $model): bool
    {
        return $user->is($model);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * A user may update their own record; Admin bypasses via Gate::before.
     */
    public function update(User $user, User $model): bool
    {
        return $user->is($model);
    }

    /**
     * No user may delete another user directly through this policy.
     * Admin capability is granted exclusively through Gate::before, keeping
     * this policy the single source of truth for non-privileged behavior.
     */
    public function delete(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
