<?php

use App\Models\Schedule;
use App\Models\User;

test('any authenticated user can list and create schedules', function () {
    $user = User::factory()->create();
    $user->assignRole('User');

    expect($user->can('viewAny', Schedule::class))->toBeTrue();
    expect($user->can('create', Schedule::class))->toBeTrue();
});

test('a user can view, update, and delete their own schedule', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->for($user, 'owner')->create();

    expect($user->can('view', $schedule))->toBeTrue();
    expect($user->can('update', $schedule))->toBeTrue();
    expect($user->can('delete', $schedule))->toBeTrue();
});

test('a user cannot view, update, or delete another user\'s schedule', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $owner = User::factory()->create();
    $schedule = Schedule::factory()->for($owner, 'owner')->create();

    expect($user->can('view', $schedule))->toBeFalse();
    expect($user->can('update', $schedule))->toBeFalse();
    expect($user->can('delete', $schedule))->toBeFalse();
});

test('an admin can view, update, and delete any schedule via the Gate::before bypass', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $owner = User::factory()->create();
    $schedule = Schedule::factory()->for($owner, 'owner')->create();

    expect($admin->can('view', $schedule))->toBeTrue();
    expect($admin->can('update', $schedule))->toBeTrue();
    expect($admin->can('delete', $schedule))->toBeTrue();
});
