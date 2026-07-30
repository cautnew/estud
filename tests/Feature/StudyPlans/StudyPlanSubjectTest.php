<?php

use App\Models\StudyPlan;
use App\Models\StudyPlanSubject;
use App\Models\Subject;
use App\Models\User;

test('a user can add a visible subject to their own plan', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $plan = StudyPlan::factory()->for($user, 'owner')->create();
    $subject = Subject::factory()->create();

    $response = $this->actingAs($user)->post(route('study-plans.plan-subjects.store', $plan), [
        'subject_id' => $subject->id,
    ]);

    $response->assertSessionHasNoErrors();
    expect($plan->studyPlanSubjects()->where('subject_id', $subject->id)->exists())->toBeTrue();
});

test('a user cannot add a subject invisible to them', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $plan = StudyPlan::factory()->for($user, 'owner')->create();
    $subject = Subject::factory()->ownedBy()->create();

    $response = $this->actingAs($user)->post(route('study-plans.plan-subjects.store', $plan), [
        'subject_id' => $subject->id,
    ]);

    $response->assertSessionHasErrors('subject_id');
});

test('a user cannot add the same subject twice', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $plan = StudyPlan::factory()->for($user, 'owner')->create();
    $subject = Subject::factory()->create();
    StudyPlanSubject::factory()->for($plan, 'studyPlan')->for($subject)->create();

    $response = $this->actingAs($user)->post(route('study-plans.plan-subjects.store', $plan), [
        'subject_id' => $subject->id,
    ]);

    $response->assertSessionHasErrors('subject_id');
});

test('a user cannot add a subject to another user\'s plan', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $plan = StudyPlan::factory()->create();
    $subject = Subject::factory()->create();

    $response = $this->actingAs($user)->post(route('study-plans.plan-subjects.store', $plan), [
        'subject_id' => $subject->id,
    ]);

    $response->assertForbidden();
});

test('a user can remove a subject from their own plan', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $plan = StudyPlan::factory()->for($user, 'owner')->create();
    $studyPlanSubject = StudyPlanSubject::factory()->for($plan, 'studyPlan')->create();

    $response = $this->actingAs($user)->delete(route('plan-subjects.destroy', $studyPlanSubject));

    $response->assertSessionHasNoErrors();
    expect(StudyPlanSubject::find($studyPlanSubject->id))->toBeNull();
});

test('a user cannot remove a subject from another user\'s plan', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $plan = StudyPlan::factory()->create();
    $studyPlanSubject = StudyPlanSubject::factory()->for($plan, 'studyPlan')->create();

    $response = $this->actingAs($user)->delete(route('plan-subjects.destroy', $studyPlanSubject));

    $response->assertForbidden();
});
