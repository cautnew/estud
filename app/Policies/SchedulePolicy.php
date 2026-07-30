<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\AuthorizesOwnedResource;

class SchedulePolicy
{
    use AuthorizesOwnedResource;

    /**
     * Any authenticated user may list and create their own schedules.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Any authenticated user may create their own schedule.
     */
    public function create(User $user): bool
    {
        return true;
    }
}
