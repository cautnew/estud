<?php

use App\Models\ReviewPreference;
use App\Models\User;

test('guests cannot update review preferences', function () {
    $response = $this->patch(route('review-preferences.update'), ['intervals' => [1, 7]]);

    $response->assertRedirect(route('login'));
});

test('a user can set custom review intervals', function () {
    $user = User::factory()->create();
    $user->assignRole('User');

    $response = $this->actingAs($user)->patch(route('review-preferences.update'), [
        'intervals' => [3, 1, 14],
    ]);

    $response->assertSessionHasNoErrors();
    $preference = ReviewPreference::where('owner_id', $user->id)->sole();
    expect($preference->intervals)->toBe([1, 3, 14]);
});

test('intervals must be a non-empty list of distinct positive integers', function () {
    $user = User::factory()->create();
    $user->assignRole('User');

    $response = $this->actingAs($user)->patch(route('review-preferences.update'), [
        'intervals' => [1, 1],
    ]);

    $response->assertSessionHasErrors();
});
