<?php

use App\Models\User;

test('a user can view and update themselves', function () {
    $user = User::factory()->create();
    $user->assignRole('User');

    expect($user->can('view', $user))->toBeTrue();
    expect($user->can('update', $user))->toBeTrue();
});

test('a user cannot view, update, or delete another user', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $other = User::factory()->create();
    $other->assignRole('User');

    expect($user->can('view', $other))->toBeFalse();
    expect($user->can('update', $other))->toBeFalse();
    expect($user->can('delete', $other))->toBeFalse();
});

test('no one can delete a user through the policy, even themselves', function () {
    $user = User::factory()->create();
    $user->assignRole('User');

    expect($user->can('delete', $user))->toBeFalse();
});

test('an admin can view, update, and delete any user via the Gate::before bypass', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $other = User::factory()->create();
    $other->assignRole('User');

    expect($admin->can('viewAny', User::class))->toBeTrue();
    expect($admin->can('view', $other))->toBeTrue();
    expect($admin->can('update', $other))->toBeTrue();
    expect($admin->can('delete', $other))->toBeTrue();
});
