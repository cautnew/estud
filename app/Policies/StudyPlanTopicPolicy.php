<?php

namespace App\Policies;

use App\Models\StudyPlanSubject;
use App\Models\StudyPlanTopic;
use App\Models\User;

class StudyPlanTopicPolicy
{
    /**
     * A user may only add topics to a subject belonging to a plan they
     * own; Admin bypasses via Gate::before.
     */
    public function create(User $user, StudyPlanSubject $studyPlanSubject): bool
    {
        return $studyPlanSubject->studyPlan->owner_id === $user->id;
    }

    /**
     * A user may only update topics belonging to a plan they own; Admin
     * bypasses via Gate::before.
     */
    public function update(User $user, StudyPlanTopic $studyPlanTopic): bool
    {
        return $studyPlanTopic->studyPlanSubject->studyPlan->owner_id === $user->id;
    }

    /**
     * A user may only remove topics belonging to a plan they own; Admin
     * bypasses via Gate::before.
     */
    public function delete(User $user, StudyPlanTopic $studyPlanTopic): bool
    {
        return $studyPlanTopic->studyPlanSubject->studyPlan->owner_id === $user->id;
    }
}
