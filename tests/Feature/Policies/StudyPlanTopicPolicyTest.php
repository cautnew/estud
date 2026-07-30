<?php

use App\Models\StudyPlan;
use App\Models\StudyPlanSubject;
use App\Models\StudyPlanTopic;
use App\Models\User;

test('a user can add, update, and remove topics on a plan subject they own', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $plan = StudyPlan::factory()->for($user, 'owner')->create();
    $studyPlanSubject = StudyPlanSubject::factory()->for($plan, 'studyPlan')->create();
    $studyPlanTopic = StudyPlanTopic::factory()->for($studyPlanSubject, 'studyPlanSubject')->create();

    expect($user->can('create', [StudyPlanTopic::class, $studyPlanSubject]))->toBeTrue();
    expect($user->can('update', $studyPlanTopic))->toBeTrue();
    expect($user->can('delete', $studyPlanTopic))->toBeTrue();
});

test('a user cannot add, update, or remove topics on another user\'s plan subject', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $owner = User::factory()->create();
    $plan = StudyPlan::factory()->for($owner, 'owner')->create();
    $studyPlanSubject = StudyPlanSubject::factory()->for($plan, 'studyPlan')->create();
    $studyPlanTopic = StudyPlanTopic::factory()->for($studyPlanSubject, 'studyPlanSubject')->create();

    expect($user->can('create', [StudyPlanTopic::class, $studyPlanSubject]))->toBeFalse();
    expect($user->can('update', $studyPlanTopic))->toBeFalse();
    expect($user->can('delete', $studyPlanTopic))->toBeFalse();
});

test('an admin can add, update, and remove topics on any plan subject via the Gate::before bypass', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $owner = User::factory()->create();
    $plan = StudyPlan::factory()->for($owner, 'owner')->create();
    $studyPlanSubject = StudyPlanSubject::factory()->for($plan, 'studyPlan')->create();
    $studyPlanTopic = StudyPlanTopic::factory()->for($studyPlanSubject, 'studyPlanSubject')->create();

    expect($admin->can('create', [StudyPlanTopic::class, $studyPlanSubject]))->toBeTrue();
    expect($admin->can('update', $studyPlanTopic))->toBeTrue();
    expect($admin->can('delete', $studyPlanTopic))->toBeTrue();
});
