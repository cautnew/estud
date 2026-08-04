<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\AuthorizesOwnedResource;

class QuestionLogPolicy
{
    use AuthorizesOwnedResource;

    /**
     * Any authenticated user may view and log their own question practice.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Any authenticated user may log question practice for themselves.
     */
    public function create(User $user): bool
    {
        return true;
    }
}
