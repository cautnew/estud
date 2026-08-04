<?php

use App\Actions\Reviews\UpdateReviewPreference;
use App\Models\ReviewPreference;
use App\Models\User;

test('it creates a preference when the user has none', function () {
    $user = User::factory()->create();

    $preference = (new UpdateReviewPreference)->handle($user, [30, 10, 5]);

    expect($preference->owner_id)->toBe($user->id);
    expect($preference->intervals)->toBe([5, 10, 30]);
});

test('it updates an existing preference in place', function () {
    $user = User::factory()->create();
    $existing = ReviewPreference::factory()->for($user, 'owner')->create(['intervals' => [1, 7]]);

    $preference = (new UpdateReviewPreference)->handle($user, [3]);

    expect($preference->id)->toBe($existing->id);
    expect(ReviewPreference::where('owner_id', $user->id)->count())->toBe(1);
    expect($preference->fresh()->intervals)->toBe([3]);
});
