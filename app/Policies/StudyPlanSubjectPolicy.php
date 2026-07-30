<?php

namespace App\Policies;

use App\Models\StudyPlan;
use App\Models\StudyPlanSubject;
use App\Models\User;

class StudyPlanSubjectPolicy
{
    /**
     * A user may only add subjects to a plan they own; Admin bypasses via
     * Gate::before.
     */
    public function create(User $user, StudyPlan $studyPlan): bool
    {
        return $studyPlan->owner_id === $user->id;
    }

    /**
     * A user may only remove subjects from a plan they own; Admin bypasses
     * via Gate::before.
     */
    public function delete(User $user, StudyPlanSubject $studyPlanSubject): bool
    {
        return $studyPlanSubject->studyPlan->owner_id === $user->id;
    }
}
