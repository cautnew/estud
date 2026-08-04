<?php

namespace App\Actions\StudySessions;

use App\Enums\StudySessionStatus;
use App\Models\StudySession;

class SyncStudySessionDuration
{
    /**
     * Auto-save checkpoint: persist the elapsed running time so far
     * without pausing or completing the session, so no progress is lost
     * if the browser closes before the user explicitly stops the timer.
     * A no-op when the session isn't currently running.
     */
    public function handle(StudySession $session): StudySession
    {
        if ($session->status !== StudySessionStatus::InProgress) {
            return $session;
        }

        $session->forceFill([
            'duration_seconds' => $session->currentDurationSeconds(),
            'started_at' => now(),
        ])->save();

        return $session;
    }
}
