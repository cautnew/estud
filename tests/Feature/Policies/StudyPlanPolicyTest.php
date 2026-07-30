<?php

use App\Models\StudyPlan;
use App\Models\User;

test('any authenticated user can list and create study plans', function () {
    $user = User::factory()->create();
    $user->assignRole('User');

    expect($user->can('viewAny', StudyPlan::class))->toBeTrue();
    expect($user->can('create', StudyPlan::class))->toBeTrue();
});

test('a user can view, update, delete, and duplicate their own plan', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $plan = StudyPlan::factory()->for($user, 'owner')->create();

    expect($user->can('view', $plan))->toBeTrue();
    expect($user->can('update', $plan))->toBeTrue();
    expect($user->can('delete', $plan))->toBeTrue();
    expect($user->can('duplicate', $plan))->toBeTrue();
});

test('a user cannot view, update, delete, or duplicate another user\'s private plan', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $owner = User::factory()->create();
    $plan = StudyPlan::factory()->for($owner, 'owner')->create();

    expect($user->can('view', $plan))->toBeFalse();
    expect($user->can('update', $plan))->toBeFalse();
    expect($user->can('delete', $plan))->toBeFalse();
    expect($user->can('duplicate', $plan))->toBeFalse();
});

test('a user can view and duplicate, but not update or delete, another user\'s public plan', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $owner = User::factory()->create();
    $plan = StudyPlan::factory()->for($owner, 'owner')->public()->create();

    expect($user->can('view', $plan))->toBeTrue();
    expect($user->can('duplicate', $plan))->toBeTrue();
    expect($user->can('update', $plan))->toBeFalse();
    expect($user->can('delete', $plan))->toBeFalse();
});

test('an admin can view, update, delete, and duplicate any plan via the Gate::before bypass', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $owner = User::factory()->create();
    $plan = StudyPlan::factory()->for($owner, 'owner')->create();

    expect($admin->can('view', $plan))->toBeTrue();
    expect($admin->can('update', $plan))->toBeTrue();
    expect($admin->can('delete', $plan))->toBeTrue();
    expect($admin->can('duplicate', $plan))->toBeTrue();
});
