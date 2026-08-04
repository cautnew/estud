<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;
use App\Policies\Concerns\AuthorizesOwnedResource;

class ReviewPolicy
{
    use AuthorizesOwnedResource;

    /**
     * Any authenticated user may view their own review agenda.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Completing or skipping a review requires the same ownership as
     * updating it.
     */
    public function complete(User $user, Review $review): bool
    {
        return $this->isOwnedBy($user, $review);
    }

    public function skip(User $user, Review $review): bool
    {
        return $this->isOwnedBy($user, $review);
    }
}
