<?php

use App\Models\Review;
use App\Models\User;

test('any authenticated user can view their own review agenda', function () {
    $user = User::factory()->create();
    $user->assignRole('User');

    expect($user->can('viewAny', Review::class))->toBeTrue();
});

test('a user can complete and skip their own review', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $review = Review::factory()->for($user, 'owner')->create();

    expect($user->can('complete', $review))->toBeTrue();
    expect($user->can('skip', $review))->toBeTrue();
});

test('a user cannot complete or skip another user\'s review', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $review = Review::factory()->create();

    expect($user->can('complete', $review))->toBeFalse();
    expect($user->can('skip', $review))->toBeFalse();
});

test('an admin can complete any review via the Gate::before bypass', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $review = Review::factory()->create();

    expect($admin->can('complete', $review))->toBeTrue();
});
