<?php

use App\Models\StudySession;
use App\Models\User;

test('any authenticated user can list and create study sessions', function () {
    $user = User::factory()->create();
    $user->assignRole('User');

    expect($user->can('viewAny', StudySession::class))->toBeTrue();
    expect($user->can('create', StudySession::class))->toBeTrue();
});

test('a user can view, update, delete, and operate the stopwatch on their own session', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $session = StudySession::factory()->for($user, 'owner')->create();

    expect($user->can('view', $session))->toBeTrue();
    expect($user->can('update', $session))->toBeTrue();
    expect($user->can('delete', $session))->toBeTrue();
    expect($user->can('pause', $session))->toBeTrue();
    expect($user->can('resume', $session))->toBeTrue();
    expect($user->can('complete', $session))->toBeTrue();
    expect($user->can('sync', $session))->toBeTrue();
});

test('a user cannot view, update, delete, or operate the stopwatch on another user\'s session', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $session = StudySession::factory()->create();

    expect($user->can('view', $session))->toBeFalse();
    expect($user->can('update', $session))->toBeFalse();
    expect($user->can('delete', $session))->toBeFalse();
    expect($user->can('pause', $session))->toBeFalse();
    expect($user->can('resume', $session))->toBeFalse();
    expect($user->can('complete', $session))->toBeFalse();
    expect($user->can('sync', $session))->toBeFalse();
});

test('an admin can manage any study session via the Gate::before bypass', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $session = StudySession::factory()->create();

    expect($admin->can('view', $session))->toBeTrue();
    expect($admin->can('update', $session))->toBeTrue();
    expect($admin->can('delete', $session))->toBeTrue();
});
