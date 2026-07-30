<?php

use App\Models\StudyPlan;
use App\Models\StudyPlanSubject;
use App\Models\StudyPlanTopic;
use App\Models\User;

test('guests cannot access study plans', function () {
    $response = $this->get(route('study-plans.index'));

    $response->assertRedirect(route('login'));
});

test('a user only sees their own plans in the index', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $own = StudyPlan::factory()->for($user, 'owner')->create();
    StudyPlan::factory()->create();

    $response = $this->actingAs($user)->get(route('study-plans.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('studyPlans', 1)
        ->where('studyPlans.0.id', $own->id)
    );
});

test('a user can create a study plan', function () {
    $user = User::factory()->create();
    $user->assignRole('User');

    $response = $this->actingAs($user)->post(route('study-plans.store'), [
        'name' => 'Plano TRF',
        'exam_board' => 'TRF 2026',
        'goal' => 'Passar em 1º lugar',
        'deadline' => '2026-12-01',
        'exam_date' => '2026-12-15',
    ]);

    $response->assertSessionHasNoErrors();

    $plan = StudyPlan::sole();
    expect($plan->owner_id)->toBe($user->id);
    expect($plan->is_public)->toBeFalse();
    $response->assertRedirect(route('study-plans.show', $plan));
});

test('a user can view their own plan', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $plan = StudyPlan::factory()->for($user, 'owner')->create();

    $response = $this->actingAs($user)->get(route('study-plans.show', $plan));

    $response->assertOk();
});

test('a user cannot view another user\'s private plan', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $plan = StudyPlan::factory()->create();

    $response = $this->actingAs($user)->get(route('study-plans.show', $plan));

    $response->assertForbidden();
});

test('a user can view another user\'s public plan', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $plan = StudyPlan::factory()->public()->create();

    $response = $this->actingAs($user)->get(route('study-plans.show', $plan));

    $response->assertOk();
});

test('a user can update their own plan, including publishing it', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $plan = StudyPlan::factory()->for($user, 'owner')->create();

    $response = $this->actingAs($user)->put(route('study-plans.update', $plan), [
        'name' => 'Atualizado',
        'exam_board' => $plan->exam_board,
        'is_public' => true,
    ]);

    $response->assertSessionHasNoErrors();
    expect($plan->refresh()->name)->toBe('Atualizado');
    expect($plan->is_public)->toBeTrue();
});

test('a user cannot update another user\'s plan', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $plan = StudyPlan::factory()->create();

    $response = $this->actingAs($user)->put(route('study-plans.update', $plan), [
        'name' => 'Atualizado',
        'exam_board' => $plan->exam_board,
    ]);

    $response->assertForbidden();
});

test('a user can delete their own plan, cascading its subjects and topics', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $plan = StudyPlan::factory()->for($user, 'owner')->create();
    $studyPlanSubject = StudyPlanSubject::factory()->for($plan, 'studyPlan')->create();
    $studyPlanTopic = StudyPlanTopic::factory()->for($studyPlanSubject, 'studyPlanSubject')->create();

    $response = $this->actingAs($user)->delete(route('study-plans.destroy', $plan));

    $response->assertRedirect(route('study-plans.index'));
    expect(StudyPlan::find($plan->id))->toBeNull();
    expect(StudyPlanSubject::find($studyPlanSubject->id))->toBeNull();
    expect(StudyPlanTopic::find($studyPlanTopic->id))->toBeNull();
});

test('a user cannot delete another user\'s plan', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $plan = StudyPlan::factory()->create();

    $response = $this->actingAs($user)->delete(route('study-plans.destroy', $plan));

    $response->assertForbidden();
});
