<?php

namespace App\Actions\Reviews;

use App\Models\ReviewPreference;
use App\Models\User;

class UpdateReviewPreference
{
    /**
     * Store the user's custom spaced-repetition intervals, replacing
     * their previous preference if one already exists.
     *
     * @param  array<int, int>  $intervals
     */
    public function handle(User $owner, array $intervals): ReviewPreference
    {
        sort($intervals);

        $preference = ReviewPreference::query()->ownedBy($owner)->first() ?? new ReviewPreference;

        $preference->forceFill(['owner_id' => $owner->id]);
        $preference->fill(['intervals' => array_values($intervals)]);
        $preference->save();

        return $preference;
    }
}
