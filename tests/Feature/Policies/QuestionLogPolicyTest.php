<?php

use App\Models\QuestionLog;
use App\Models\User;

test('any authenticated user can list and create question logs', function () {
    $user = User::factory()->create();
    $user->assignRole('User');

    expect($user->can('viewAny', QuestionLog::class))->toBeTrue();
    expect($user->can('create', QuestionLog::class))->toBeTrue();
});

test('a user can view, update, and delete their own question log', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $log = QuestionLog::factory()->for($user, 'owner')->create();

    expect($user->can('view', $log))->toBeTrue();
    expect($user->can('update', $log))->toBeTrue();
    expect($user->can('delete', $log))->toBeTrue();
});

test('a user cannot view, update, or delete another user\'s question log', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $log = QuestionLog::factory()->create();

    expect($user->can('view', $log))->toBeFalse();
    expect($user->can('update', $log))->toBeFalse();
    expect($user->can('delete', $log))->toBeFalse();
});

test('an admin can manage any question log via the Gate::before bypass', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $log = QuestionLog::factory()->create();

    expect($admin->can('view', $log))->toBeTrue();
    expect($admin->can('update', $log))->toBeTrue();
    expect($admin->can('delete', $log))->toBeTrue();
});
