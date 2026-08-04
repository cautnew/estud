<?php

use App\Actions\StudySessions\StartStudySession;
use App\Enums\StudySessionStatus;
use App\Models\StudySession;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Validation\ValidationException;

test('starting a session creates it running with zero accumulated duration', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    $topic = Topic::factory()->for($subject)->create();

    $session = (new StartStudySession)->handle($user, $subject, $topic, note: 'Revisão de vícios');

    expect($session->owner_id)->toBe($user->id);
    expect($session->subject_id)->toBe($subject->id);
    expect($session->topic_id)->toBe($topic->id);
    expect($session->status)->toBe(StudySessionStatus::InProgress);
    expect($session->started_at)->not->toBeNull();
    expect($session->duration_seconds)->toBe(0);
    expect($session->note)->toBe('Revisão de vícios');
});

test('a topic is optional when starting a session', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();

    $session = (new StartStudySession)->handle($user, $subject, null);

    expect($session->topic_id)->toBeNull();
});

test('a user cannot start a session while one is already running or paused', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    StudySession::factory()->for($user, 'owner')->for($subject)->create();

    expect(fn () => (new StartStudySession)->handle($user, $subject, null))
        ->toThrow(ValidationException::class);
});

test('a user can start a new session once the previous one is completed', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    StudySession::factory()->for($user, 'owner')->for($subject)->completed()->create();

    $session = (new StartStudySession)->handle($user, $subject, null);

    expect($session->status)->toBe(StudySessionStatus::InProgress);
});
