<?php

use App\Enums\GoalPeriod;
use App\Enums\GoalType;
use App\Models\Goal;
use App\Models\GoalProgress;
use App\Models\StudySession;
use App\Models\Subject;
use App\Models\User;

test('guests cannot access goals', function () {
    $response = $this->get(route('goals.index'));

    $response->assertRedirect(route('login'));
});

test('a user only sees their own goals in the index', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $own = Goal::factory()->for($user, 'owner')->create();
    Goal::factory()->create();

    $response = $this->actingAs($user)->get(route('goals.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('goals', 1)
        ->where('goals.0.id', $own->id)
    );
});

test('visiting the index refreshes progress for the current period', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $goal = Goal::factory()->for($user, 'owner')->ofType(GoalType::Hours)->create(['target' => 1]);
    StudySession::factory()->for($user, 'owner')->completed()->create(['date' => today()->toDateString(), 'duration_seconds' => 3600]);

    $this->actingAs($user)->get(route('goals.index'));

    $progress = GoalProgress::where('goal_id', $goal->id)->sole();
    expect($progress->current_value)->toBe(1.0);
    expect($progress->achieved)->toBeTrue();
});

test('a user can create a goal for themselves', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $subject = Subject::factory()->create();

    $response = $this->actingAs($user)->post(route('goals.store'), [
        'type' => GoalType::Topics->value,
        'period' => GoalPeriod::Weekly->value,
        'target' => 5,
        'subject_id' => $subject->id,
    ]);

    $response->assertSessionHasNoErrors();
    $goal = Goal::sole();
    expect($goal->owner_id)->toBe($user->id);
    expect($goal->type)->toBe(GoalType::Topics);
    expect($goal->subject_id)->toBe($subject->id);
});

test('a user can update their own goal', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $goal = Goal::factory()->for($user, 'owner')->create(['target' => 5]);

    $response = $this->actingAs($user)->put(route('goals.update', $goal), ['target' => 15]);

    $response->assertSessionHasNoErrors();
    expect($goal->refresh()->target)->toBe(15.0);
});

test('a user cannot update another user\'s goal', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $goal = Goal::factory()->create();

    $response = $this->actingAs($user)->put(route('goals.update', $goal), ['target' => 15]);

    $response->assertForbidden();
});

test('a user can delete their own goal', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $goal = Goal::factory()->for($user, 'owner')->create();

    $response = $this->actingAs($user)->delete(route('goals.destroy', $goal));

    $response->assertRedirect(route('goals.index'));
    expect(Goal::find($goal->id))->toBeNull();
});

test('a user cannot delete another user\'s goal', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $goal = Goal::factory()->create();

    $response = $this->actingAs($user)->delete(route('goals.destroy', $goal));

    $response->assertForbidden();
});
