<?php

use App\Actions\StudySessions\PauseStudySession;
use App\Enums\StudySessionStatus;
use App\Models\StudySession;
use Illuminate\Validation\ValidationException;

test('pausing a running session folds the elapsed time into the persisted duration', function () {
    $session = StudySession::factory()->create();

    $this->travel(90)->seconds();

    (new PauseStudySession)->handle($session);

    expect($session->status)->toBe(StudySessionStatus::Paused);
    expect($session->started_at)->toBeNull();
    expect($session->duration_seconds)->toBeGreaterThanOrEqual(90);
});

test('a paused session cannot be paused again', function () {
    $session = StudySession::factory()->paused()->create();

    expect(fn () => (new PauseStudySession)->handle($session))
        ->toThrow(ValidationException::class);
});

test('a completed session cannot be paused', function () {
    $session = StudySession::factory()->completed()->create();

    expect(fn () => (new PauseStudySession)->handle($session))
        ->toThrow(ValidationException::class);
});
