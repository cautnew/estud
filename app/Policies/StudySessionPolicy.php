<?php

namespace App\Policies;

use App\Models\StudySession;
use App\Models\User;
use App\Policies\Concerns\AuthorizesOwnedResource;

class StudySessionPolicy
{
    use AuthorizesOwnedResource;

    /**
     * Any authenticated user may list and log their own study sessions.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Any authenticated user may start a study session for themselves.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Pausing, resuming, completing, and auto-saving the stopwatch all
     * require the same ownership as updating the session.
     */
    public function pause(User $user, StudySession $studySession): bool
    {
        return $this->isOwnedBy($user, $studySession);
    }

    public function resume(User $user, StudySession $studySession): bool
    {
        return $this->isOwnedBy($user, $studySession);
    }

    public function complete(User $user, StudySession $studySession): bool
    {
        return $this->isOwnedBy($user, $studySession);
    }

    public function sync(User $user, StudySession $studySession): bool
    {
        return $this->isOwnedBy($user, $studySession);
    }
}
