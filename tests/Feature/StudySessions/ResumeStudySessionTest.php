<?php

use App\Actions\StudySessions\ResumeStudySession;
use App\Enums\StudySessionStatus;
use App\Models\StudySession;
use Illuminate\Validation\ValidationException;

test('resuming a paused session restarts the stopwatch without losing prior duration', function () {
    $session = StudySession::factory()->paused()->create(['duration_seconds' => 120]);

    (new ResumeStudySession)->handle($session);

    expect($session->status)->toBe(StudySessionStatus::InProgress);
    expect($session->started_at)->not->toBeNull();
    expect($session->duration_seconds)->toBe(120);
});

test('a running session cannot be resumed', function () {
    $session = StudySession::factory()->create();

    expect(fn () => (new ResumeStudySession)->handle($session))
        ->toThrow(ValidationException::class);
});

test('a completed session cannot be resumed', function () {
    $session = StudySession::factory()->completed()->create();

    expect(fn () => (new ResumeStudySession)->handle($session))
        ->toThrow(ValidationException::class);
});
