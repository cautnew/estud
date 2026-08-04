<?php

use App\Models\Goal;
use App\Models\User;

test('any authenticated user can list and create goals', function () {
    $user = User::factory()->create();
    $user->assignRole('User');

    expect($user->can('viewAny', Goal::class))->toBeTrue();
    expect($user->can('create', Goal::class))->toBeTrue();
});

test('a user can view, update, and delete their own goal', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $goal = Goal::factory()->for($user, 'owner')->create();

    expect($user->can('view', $goal))->toBeTrue();
    expect($user->can('update', $goal))->toBeTrue();
    expect($user->can('delete', $goal))->toBeTrue();
});

test('a user cannot view, update, or delete another user\'s goal', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $goal = Goal::factory()->create();

    expect($user->can('view', $goal))->toBeFalse();
    expect($user->can('update', $goal))->toBeFalse();
    expect($user->can('delete', $goal))->toBeFalse();
});

test('an admin can manage any goal via the Gate::before bypass', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $goal = Goal::factory()->create();

    expect($admin->can('view', $goal))->toBeTrue();
    expect($admin->can('update', $goal))->toBeTrue();
    expect($admin->can('delete', $goal))->toBeTrue();
});
