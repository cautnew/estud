<?php

use App\Models\StudyPlan;
use App\Models\StudyPlanSubject;
use App\Models\StudyPlanTopic;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;

test('a user can add a topic belonging to the linked subject', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $plan = StudyPlan::factory()->for($user, 'owner')->create();
    $subject = Subject::factory()->create();
    $studyPlanSubject = StudyPlanSubject::factory()->for($plan, 'studyPlan')->for($subject)->create();
    $topic = Topic::factory()->for($subject)->create();

    $response = $this->actingAs($user)->post(
        route('plan-subjects.plan-topics.store', $studyPlanSubject),
        ['topic_id' => $topic->id],
    );

    $response->assertSessionHasNoErrors();
    expect($studyPlanSubject->studyPlanTopics()->where('topic_id', $topic->id)->exists())->toBeTrue();
});

test('a user cannot add a topic from a different subject', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $plan = StudyPlan::factory()->for($user, 'owner')->create();
    $subject = Subject::factory()->create();
    $studyPlanSubject = StudyPlanSubject::factory()->for($plan, 'studyPlan')->for($subject)->create();
    $otherSubjectTopic = Topic::factory()->create();

    $response = $this->actingAs($user)->post(
        route('plan-subjects.plan-topics.store', $studyPlanSubject),
        ['topic_id' => $otherSubjectTopic->id],
    );

    $response->assertSessionHasErrors('topic_id');
});

test('a user cannot add a topic to another user\'s plan subject', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $plan = StudyPlan::factory()->create();
    $subject = Subject::factory()->create();
    $studyPlanSubject = StudyPlanSubject::factory()->for($plan, 'studyPlan')->for($subject)->create();
    $topic = Topic::factory()->for($subject)->create();

    $response = $this->actingAs($user)->post(
        route('plan-subjects.plan-topics.store', $studyPlanSubject),
        ['topic_id' => $topic->id],
    );

    $response->assertForbidden();
});

test('a user can update the status, studied date, and notes of a topic on their own plan', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $plan = StudyPlan::factory()->for($user, 'owner')->create();
    $studyPlanSubject = StudyPlanSubject::factory()->for($plan, 'studyPlan')->create();
    $studyPlanTopic = StudyPlanTopic::factory()->for($studyPlanSubject, 'studyPlanSubject')->create();

    $response = $this->actingAs($user)->put(route('plan-topics.update', $studyPlanTopic), [
        'status' => 'done',
        'studied_at' => '2026-08-01',
        'notes' => 'Revisado duas vezes',
    ]);

    $response->assertSessionHasNoErrors();
    $studyPlanTopic->refresh();
    expect($studyPlanTopic->status->value)->toBe('done');
    expect($studyPlanTopic->notes)->toBe('Revisado duas vezes');
});

test('a user cannot update a topic on another user\'s plan', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $plan = StudyPlan::factory()->create();
    $studyPlanSubject = StudyPlanSubject::factory()->for($plan, 'studyPlan')->create();
    $studyPlanTopic = StudyPlanTopic::factory()->for($studyPlanSubject, 'studyPlanSubject')->create();

    $response = $this->actingAs($user)->put(route('plan-topics.update', $studyPlanTopic), [
        'status' => 'done',
    ]);

    $response->assertForbidden();
});

test('a user can remove a topic from their own plan', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $plan = StudyPlan::factory()->for($user, 'owner')->create();
    $studyPlanSubject = StudyPlanSubject::factory()->for($plan, 'studyPlan')->create();
    $studyPlanTopic = StudyPlanTopic::factory()->for($studyPlanSubject, 'studyPlanSubject')->create();

    $response = $this->actingAs($user)->delete(route('plan-topics.destroy', $studyPlanTopic));

    $response->assertSessionHasNoErrors();
    expect(StudyPlanTopic::find($studyPlanTopic->id))->toBeNull();
});

test('a user cannot remove a topic from another user\'s plan', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $plan = StudyPlan::factory()->create();
    $studyPlanSubject = StudyPlanSubject::factory()->for($plan, 'studyPlan')->create();
    $studyPlanTopic = StudyPlanTopic::factory()->for($studyPlanSubject, 'studyPlanSubject')->create();

    $response = $this->actingAs($user)->delete(route('plan-topics.destroy', $studyPlanTopic));

    $response->assertForbidden();
});
