<?php

use App\Enums\StudyPlanTopicStatus;
use App\Models\StudyPlan;
use App\Models\StudyPlanSubject;
use App\Models\StudyPlanTopic;
use App\Models\User;

test('a user can duplicate their own plan, with progress reset', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $plan = StudyPlan::factory()->for($user, 'owner')->create(['name' => 'Plano original']);
    $studyPlanSubject = StudyPlanSubject::factory()->for($plan, 'studyPlan')->create();
    StudyPlanTopic::factory()->for($studyPlanSubject, 'studyPlanSubject')->create([
        'status' => StudyPlanTopicStatus::Done,
        'studied_at' => now(),
    ]);

    $response = $this->actingAs($user)->post(route('study-plans.duplicate', $plan));

    $response->assertSessionHasNoErrors();

    $copy = StudyPlan::where('id', '!=', $plan->id)->sole();
    expect($copy->name)->toBe('Plano original (cópia)');
    expect($copy->owner_id)->toBe($user->id);
    expect($copy->is_public)->toBeFalse();

    $copiedTopic = StudyPlanTopic::whereHas(
        'studyPlanSubject',
        fn ($query) => $query->where('study_plan_id', $copy->id),
    )->sole();
    expect($copiedTopic->status)->toBe(StudyPlanTopicStatus::Pending);
    expect($copiedTopic->studied_at)->toBeNull();
});

test('a user cannot duplicate another user\'s private plan', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $plan = StudyPlan::factory()->create();

    $response = $this->actingAs($user)->post(route('study-plans.duplicate', $plan));

    $response->assertForbidden();
});

test('a user can clone another user\'s public plan into an independent copy', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $owner = User::factory()->create();
    $plan = StudyPlan::factory()->for($owner, 'owner')->public()->create();
    $studyPlanSubject = StudyPlanSubject::factory()->for($plan, 'studyPlan')->create();
    StudyPlanTopic::factory()->for($studyPlanSubject, 'studyPlanSubject')->create();

    $response = $this->actingAs($user)->post(route('study-plans.duplicate', $plan));

    $response->assertSessionHasNoErrors();

    $copy = StudyPlan::where('owner_id', $user->id)->sole();
    expect($copy->id)->not->toBe($plan->id);

    $copy->update(['name' => 'Alterado pelo clone']);
    expect($plan->refresh()->name)->not->toBe('Alterado pelo clone');
});
