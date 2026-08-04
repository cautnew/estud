<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\AuthorizesOwnedResource;

class GoalPolicy
{
    use AuthorizesOwnedResource;

    /**
     * Any authenticated user may view and create their own goals.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }
}
