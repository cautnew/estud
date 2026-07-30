<?php

use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;

test('a user can create and update topics on a subject they own', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $subject = Subject::factory()->ownedBy($user)->create();
    $topic = Topic::factory()->for($subject)->create();

    expect($user->can('create', [Topic::class, $subject]))->toBeTrue();
    expect($user->can('update', $topic))->toBeTrue();
});

test('a user cannot create or update topics on a global subject', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $subject = Subject::factory()->create();
    $topic = Topic::factory()->for($subject)->create();

    expect($user->can('create', [Topic::class, $subject]))->toBeFalse();
    expect($user->can('update', $topic))->toBeFalse();
});

test('a user cannot create or update topics on another user\'s subject', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $owner = User::factory()->create();
    $subject = Subject::factory()->ownedBy($owner)->create();
    $topic = Topic::factory()->for($subject)->create();

    expect($user->can('create', [Topic::class, $subject]))->toBeFalse();
    expect($user->can('update', $topic))->toBeFalse();
});

test('no user may delete a topic through the policy, even on their own subject', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $subject = Subject::factory()->ownedBy($user)->create();
    $topic = Topic::factory()->for($subject)->create();

    expect($user->can('delete', $topic))->toBeFalse();
});

test('an admin can create, update, and delete topics on any subject via the Gate::before bypass', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $globalSubject = Subject::factory()->create();
    $topic = Topic::factory()->for($globalSubject)->create();

    expect($admin->can('create', [Topic::class, $globalSubject]))->toBeTrue();
    expect($admin->can('update', $topic))->toBeTrue();
    expect($admin->can('delete', $topic))->toBeTrue();
});
