<?php

use App\Enums\GoalType;
use App\Models\Goal;
use App\Models\GoalProgress;
use App\Models\QuestionLog;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;

test('guests cannot access question logs', function () {
    $response = $this->get(route('question-logs.index'));

    $response->assertRedirect(route('login'));
});

test('a user only sees their own question logs in the index', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $own = QuestionLog::factory()->for($user, 'owner')->create();
    QuestionLog::factory()->create();

    $response = $this->actingAs($user)->get(route('question-logs.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('questionLogs', 1)
        ->where('questionLogs.0.id', $own->id)
    );
});

test('a user can log resolved/correct/incorrect questions for a subject and topic', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $subject = Subject::factory()->create();
    $topic = Topic::factory()->for($subject)->create();

    $response = $this->actingAs($user)->post(route('question-logs.store'), [
        'subject_id' => $subject->id,
        'topic_id' => $topic->id,
        'date' => '2026-08-01',
        'total' => 10,
        'correct' => 7,
        'incorrect' => 3,
    ]);

    $response->assertSessionHasNoErrors();
    $log = QuestionLog::sole();
    expect($log->owner_id)->toBe($user->id);
    expect($log->total)->toBe(10);
    expect($log->correct)->toBe(7);
    expect($log->incorrect)->toBe(3);
});

test('correct plus incorrect cannot exceed the total resolved', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $subject = Subject::factory()->create();

    $response = $this->actingAs($user)->post(route('question-logs.store'), [
        'subject_id' => $subject->id,
        'date' => '2026-08-01',
        'total' => 10,
        'correct' => 8,
        'incorrect' => 8,
    ]);

    $response->assertSessionHasErrors('total');
    expect(QuestionLog::count())->toBe(0);
});

test('a user can update their own question log', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $log = QuestionLog::factory()->for($user, 'owner')->create(['total' => 10, 'correct' => 5, 'incorrect' => 5]);

    $response = $this->actingAs($user)->put(route('question-logs.update', $log), [
        'correct' => 8,
        'incorrect' => 2,
    ]);

    $response->assertSessionHasNoErrors();
    $log->refresh();
    expect($log->correct)->toBe(8);
    expect($log->incorrect)->toBe(2);
});

test('a user cannot update another user\'s question log', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $log = QuestionLog::factory()->create();

    $response = $this->actingAs($user)->put(route('question-logs.update', $log), ['total' => 20]);

    $response->assertForbidden();
});

test('a user can delete their own question log', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $log = QuestionLog::factory()->for($user, 'owner')->create();

    $response = $this->actingAs($user)->delete(route('question-logs.destroy', $log));

    $response->assertRedirect(route('question-logs.index'));
    expect(QuestionLog::find($log->id))->toBeNull();
});

test('a user cannot delete another user\'s question log', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $log = QuestionLog::factory()->create();

    $response = $this->actingAs($user)->delete(route('question-logs.destroy', $log));

    $response->assertForbidden();
});

test('logging questions automatically refreshes a questions goal\'s progress', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $goal = Goal::factory()->for($user, 'owner')->ofType(GoalType::Questions)->create(['target' => 10]);
    $subject = Subject::factory()->create();

    $this->actingAs($user)->post(route('question-logs.store'), [
        'subject_id' => $subject->id,
        'date' => today()->toDateString(),
        'total' => 10,
        'correct' => 8,
        'incorrect' => 2,
    ]);

    $progress = GoalProgress::where('goal_id', $goal->id)->sole();
    expect($progress->achieved)->toBeTrue();
});
