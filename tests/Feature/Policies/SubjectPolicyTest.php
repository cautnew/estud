<?php

use App\Models\Subject;
use App\Models\User;

test('any authenticated user can list and create subjects', function () {
    $user = User::factory()->create();
    $user->assignRole('User');

    expect($user->can('viewAny', Subject::class))->toBeTrue();
    expect($user->can('create', Subject::class))->toBeTrue();
});

test('a global subject is viewable by any user', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $subject = Subject::factory()->create();

    expect($user->can('view', $subject))->toBeTrue();
});

test('a user can view and update their own subject', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $subject = Subject::factory()->ownedBy($user)->create();

    expect($user->can('view', $subject))->toBeTrue();
    expect($user->can('update', $subject))->toBeTrue();
});

test('a user cannot view or update another user\'s subject', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $owner = User::factory()->create();
    $subject = Subject::factory()->ownedBy($owner)->create();

    expect($user->can('view', $subject))->toBeFalse();
    expect($user->can('update', $subject))->toBeFalse();
});

test('a user cannot update a global subject', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $subject = Subject::factory()->create();

    expect($user->can('update', $subject))->toBeFalse();
});

test('no user may delete a subject through the policy, even their own', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $subject = Subject::factory()->ownedBy($user)->create();

    expect($user->can('delete', $subject))->toBeFalse();
});

test('an admin can view, update, and delete any subject via the Gate::before bypass', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $owner = User::factory()->create();
    $ownedSubject = Subject::factory()->ownedBy($owner)->create();
    $globalSubject = Subject::factory()->create();

    expect($admin->can('view', $ownedSubject))->toBeTrue();
    expect($admin->can('update', $ownedSubject))->toBeTrue();
    expect($admin->can('delete', $ownedSubject))->toBeTrue();
    expect($admin->can('update', $globalSubject))->toBeTrue();
    expect($admin->can('delete', $globalSubject))->toBeTrue();
});
