<?php

use App\Actions\StudySessions\CompleteStudySession;
use App\Enums\StudySessionStatus;
use App\Models\StudySession;
use Illuminate\Validation\ValidationException;

test('completing a running session folds the elapsed time and stamps completed_at', function () {
    $session = StudySession::factory()->create();

    $this->travel(60)->seconds();

    (new CompleteStudySession)->handle($session);

    expect($session->status)->toBe(StudySessionStatus::Completed);
    expect($session->started_at)->toBeNull();
    expect($session->completed_at)->not->toBeNull();
    expect($session->duration_seconds)->toBeGreaterThanOrEqual(60);
});

test('completing a paused session keeps its accumulated duration', function () {
    $session = StudySession::factory()->paused()->create(['duration_seconds' => 300]);

    (new CompleteStudySession)->handle($session);

    expect($session->status)->toBe(StudySessionStatus::Completed);
    expect($session->duration_seconds)->toBe(300);
});

test('completing a session can attach a closing note', function () {
    $session = StudySession::factory()->create();

    (new CompleteStudySession)->handle($session, 'Terminei os exercícios');

    expect($session->note)->toBe('Terminei os exercícios');
});

test('an already completed session cannot be completed again', function () {
    $session = StudySession::factory()->completed()->create();

    expect(fn () => (new CompleteStudySession)->handle($session))
        ->toThrow(ValidationException::class);
});
