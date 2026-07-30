<?php

namespace App\Policies;

use App\Models\Subject;
use App\Models\User;
use App\Policies\Concerns\AuthorizesOwnedResource;

class SubjectPolicy
{
    use AuthorizesOwnedResource;

    /**
     * Any authenticated user may list subjects (global + their own).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Global subjects and subjects owned by the user are viewable;
     * Admin bypasses via Gate::before.
     */
    public function view(User $user, Subject $subject): bool
    {
        return $subject->owner_id === null || $this->isOwnedBy($user, $subject);
    }

    /**
     * Any authenticated user may create their own subject.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * A user may only update a subject they own; Admin bypasses via Gate::before.
     */
    public function update(User $user, Subject $subject): bool
    {
        return $this->isOwnedBy($user, $subject);
    }

    /**
     * Users may never delete subjects. Admin deletion is granted via
     * Gate::before, but is further constrained in the controller: global
     * subjects are permanent, and user-owned subjects may only be deleted
     * once they have no topics left. Gate::before bypasses this method
     * entirely for Admins, so those rules cannot be enforced here and must
     * live in AdminSubjectController::destroy() instead.
     */
    public function delete(User $user, Subject $subject): bool
    {
        return false;
    }
}
