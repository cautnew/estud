<?php

use App\Enums\StudySessionStatus;
use App\Models\StudySession;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;

test('guests cannot access study sessions', function () {
    $response = $this->get(route('study-sessions.index'));

    $response->assertRedirect(route('login'));
});

test('a user only sees their own study sessions in the index', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $own = StudySession::factory()->for($user, 'owner')->create();
    StudySession::factory()->create();

    $response = $this->actingAs($user)->get(route('study-sessions.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('studySessions', 1)
        ->where('studySessions.0.id', $own->id)
    );
});

test('a user can start a study session', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $subject = Subject::factory()->create();
    $topic = Topic::factory()->for($subject)->create();

    $response = $this->actingAs($user)->post(route('study-sessions.store'), [
        'subject_id' => $subject->id,
        'topic_id' => $topic->id,
        'note' => 'Foco em jurisprudência recente',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('study-sessions.index'));

    $session = StudySession::sole();
    expect($session->owner_id)->toBe($user->id);
    expect($session->status)->toBe(StudySessionStatus::InProgress);
});

test('a user cannot start a second study session while one is already running', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $subject = Subject::factory()->create();
    StudySession::factory()->for($user, 'owner')->for($subject)->create();

    $response = $this->actingAs($user)->post(route('study-sessions.store'), [
        'subject_id' => $subject->id,
    ]);

    $response->assertSessionHasErrors('subject_id');
    expect(StudySession::where('owner_id', $user->id)->count())->toBe(1);
});

test('a user can pause, resume, and complete their running session', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $session = StudySession::factory()->for($user, 'owner')->create();

    $this->actingAs($user)->patch(route('study-sessions.pause', $session))->assertSessionHasNoErrors();
    expect($session->refresh()->status)->toBe(StudySessionStatus::Paused);

    $this->actingAs($user)->patch(route('study-sessions.resume', $session))->assertSessionHasNoErrors();
    expect($session->refresh()->status)->toBe(StudySessionStatus::InProgress);

    $this->actingAs($user)->patch(route('study-sessions.complete', $session), ['note' => 'Concluído'])
        ->assertSessionHasNoErrors();
    expect($session->refresh()->status)->toBe(StudySessionStatus::Completed);
    expect($session->note)->toBe('Concluído');
});

test('a user cannot operate the stopwatch on another user\'s session', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $session = StudySession::factory()->create();

    $this->actingAs($user)->patch(route('study-sessions.pause', $session))->assertForbidden();
    $this->actingAs($user)->patch(route('study-sessions.resume', $session))->assertForbidden();
    $this->actingAs($user)->patch(route('study-sessions.complete', $session))->assertForbidden();
    $this->actingAs($user)->patch(route('study-sessions.sync', $session))->assertForbidden();
});

test('the sync endpoint checkpoints the elapsed duration without changing the status', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $session = StudySession::factory()->for($user, 'owner')->create();

    $this->travel(20)->seconds();

    $this->actingAs($user)->patch(route('study-sessions.sync', $session))->assertSessionHasNoErrors();

    $session->refresh();
    expect($session->status)->toBe(StudySessionStatus::InProgress);
    expect($session->duration_seconds)->toBeGreaterThanOrEqual(20);
});

test('a user can update a session\'s logged details', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $subject = Subject::factory()->create();
    $session = StudySession::factory()->for($user, 'owner')->for($subject)->completed()->create();

    $response = $this->actingAs($user)->put(route('study-sessions.update', $session), [
        'note' => 'Nota revisada',
    ]);

    $response->assertSessionHasNoErrors();
    expect($session->refresh()->note)->toBe('Nota revisada');
});

test('a user can delete their own study session', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $session = StudySession::factory()->for($user, 'owner')->create();

    $response = $this->actingAs($user)->delete(route('study-sessions.destroy', $session));

    $response->assertRedirect(route('study-sessions.index'));
    expect(StudySession::find($session->id))->toBeNull();
});

test('a user cannot delete another user\'s study session', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $session = StudySession::factory()->create();

    $response = $this->actingAs($user)->delete(route('study-sessions.destroy', $session));

    $response->assertForbidden();
});
