<?php

use App\Models\StudyPlan;
use App\Models\StudyPlanSubject;
use App\Models\User;

test('a user can add and remove subjects on a plan they own', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $plan = StudyPlan::factory()->for($user, 'owner')->create();
    $studyPlanSubject = StudyPlanSubject::factory()->for($plan, 'studyPlan')->create();

    expect($user->can('create', [StudyPlanSubject::class, $plan]))->toBeTrue();
    expect($user->can('delete', $studyPlanSubject))->toBeTrue();
});

test('a user cannot add or remove subjects on another user\'s plan', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $owner = User::factory()->create();
    $plan = StudyPlan::factory()->for($owner, 'owner')->create();
    $studyPlanSubject = StudyPlanSubject::factory()->for($plan, 'studyPlan')->create();

    expect($user->can('create', [StudyPlanSubject::class, $plan]))->toBeFalse();
    expect($user->can('delete', $studyPlanSubject))->toBeFalse();
});

test('an admin can add and remove subjects on any plan via the Gate::before bypass', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $owner = User::factory()->create();
    $plan = StudyPlan::factory()->for($owner, 'owner')->create();
    $studyPlanSubject = StudyPlanSubject::factory()->for($plan, 'studyPlan')->create();

    expect($admin->can('create', [StudyPlanSubject::class, $plan]))->toBeTrue();
    expect($admin->can('delete', $studyPlanSubject))->toBeTrue();
});
