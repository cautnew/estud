<?php

namespace App\Policies;

use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;

class TopicPolicy
{
    /**
     * A user may add topics only to a subject they own; Admin bypasses via
     * Gate::before, allowing topics on any subject including global ones.
     */
    public function create(User $user, Subject $subject): bool
    {
        return $subject->owner_id === $user->id;
    }

    /**
     * A user may only update topics belonging to a subject they own;
     * Admin bypasses via Gate::before.
     */
    public function update(User $user, Topic $topic): bool
    {
        return $topic->subject->owner_id === $user->id;
    }

    /**
     * Users may never delete topics; only Admin, via Gate::before.
     */
    public function delete(User $user, Topic $topic): bool
    {
        return false;
    }
}
