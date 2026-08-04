<?php

namespace App\Actions\StudySessions;

use App\Enums\StudySessionStatus;
use App\Models\StudySession;
use Illuminate\Validation\ValidationException;

class PauseStudySession
{
    /**
     * Stop the stopwatch, folding the elapsed running segment into the
     * persisted duration. Only valid while the session is in progress.
     */
    public function handle(StudySession $session): StudySession
    {
        if ($session->status !== StudySessionStatus::InProgress) {
            throw ValidationException::withMessages([
                'status' => 'Apenas sessões em andamento podem ser pausadas.',
            ]);
        }

        $session->forceFill([
            'status' => StudySessionStatus::Paused,
            'duration_seconds' => $session->currentDurationSeconds(),
            'started_at' => null,
        ])->save();

        return $session;
    }
}
