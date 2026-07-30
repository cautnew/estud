<?php

namespace App\Policies;

use App\Models\StudyPlan;
use App\Models\User;
use App\Policies\Concerns\AuthorizesOwnedResource;

class StudyPlanPolicy
{
    use AuthorizesOwnedResource;

    /**
     * Any authenticated user may list their own plans and browse the
     * public library.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * A plan is viewable by its owner or, once published, by any user;
     * Admin bypasses via Gate::before.
     */
    public function view(User $user, StudyPlan $studyPlan): bool
    {
        return $studyPlan->is_public || $this->isOwnedBy($user, $studyPlan);
    }

    /**
     * Any authenticated user may create their own plan.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * A user may only update a plan they own; Admin bypasses via Gate::before.
     */
    public function update(User $user, StudyPlan $studyPlan): bool
    {
        return $this->isOwnedBy($user, $studyPlan);
    }

    /**
     * A user may only delete a plan they own; Admin bypasses via Gate::before.
     */
    public function delete(User $user, StudyPlan $studyPlan): bool
    {
        return $this->isOwnedBy($user, $studyPlan);
    }

    /**
     * A plan may be duplicated by its owner, or cloned from the public
     * library by anyone else — the same rule as viewing it.
     */
    public function duplicate(User $user, StudyPlan $studyPlan): bool
    {
        return $this->view($user, $studyPlan);
    }
}
