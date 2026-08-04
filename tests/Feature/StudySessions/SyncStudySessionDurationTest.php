<?php

use App\Actions\StudySessions\SyncStudySessionDuration;
use App\Models\StudySession;

test('syncing a running session checkpoints the elapsed time without changing its status', function () {
    $session = StudySession::factory()->create();

    $this->travel(45)->seconds();

    (new SyncStudySessionDuration)->handle($session);

    expect($session->status->value)->toBe('in_progress');
    expect($session->duration_seconds)->toBeGreaterThanOrEqual(45);
    expect($session->started_at)->not->toBeNull();
});

test('syncing does not accumulate extra time beyond what elapsed', function () {
    $session = StudySession::factory()->create();

    $this->travel(30)->seconds();
    (new SyncStudySessionDuration)->handle($session);
    $afterFirstSync = $session->duration_seconds;

    (new SyncStudySessionDuration)->handle($session);

    expect($session->duration_seconds)->toBe($afterFirstSync);
});

test('syncing a paused session is a no-op', function () {
    $session = StudySession::factory()->paused()->create(['duration_seconds' => 200]);

    (new SyncStudySessionDuration)->handle($session);

    expect($session->duration_seconds)->toBe(200);
});
