<?php

use App\Models\Schedule;
use App\Models\ScheduleItem;
use App\Models\User;

test('a user can create, update, delete, and move items on a schedule they own', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $schedule = Schedule::factory()->for($user, 'owner')->create();
    $item = ScheduleItem::factory()->for($schedule)->create();

    expect($user->can('create', [ScheduleItem::class, $schedule]))->toBeTrue();
    expect($user->can('update', $item))->toBeTrue();
    expect($user->can('delete', $item))->toBeTrue();
    expect($user->can('move', $item))->toBeTrue();
});

test('a user cannot create, update, delete, or move items on another user\'s schedule', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $owner = User::factory()->create();
    $schedule = Schedule::factory()->for($owner, 'owner')->create();
    $item = ScheduleItem::factory()->for($schedule)->create();

    expect($user->can('create', [ScheduleItem::class, $schedule]))->toBeFalse();
    expect($user->can('update', $item))->toBeFalse();
    expect($user->can('delete', $item))->toBeFalse();
    expect($user->can('move', $item))->toBeFalse();
});

test('an admin can create, update, delete, and move items on any schedule via the Gate::before bypass', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $owner = User::factory()->create();
    $schedule = Schedule::factory()->for($owner, 'owner')->create();
    $item = ScheduleItem::factory()->for($schedule)->create();

    expect($admin->can('create', [ScheduleItem::class, $schedule]))->toBeTrue();
    expect($admin->can('update', $item))->toBeTrue();
    expect($admin->can('delete', $item))->toBeTrue();
    expect($admin->can('move', $item))->toBeTrue();
});
