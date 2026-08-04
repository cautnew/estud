<?php

namespace App\Actions\StudySessions;

use App\Enums\StudySessionStatus;
use App\Models\StudySession;
use Illuminate\Validation\ValidationException;

class ResumeStudySession
{
    /**
     * Restart the stopwatch from where it was paused. Only valid while
     * the session is paused.
     */
    public function handle(StudySession $session): StudySession
    {
        if ($session->status !== StudySessionStatus::Paused) {
            throw ValidationException::withMessages([
                'status' => 'Apenas sessões pausadas podem ser retomadas.',
            ]);
        }

        $session->forceFill([
            'status' => StudySessionStatus::InProgress,
            'started_at' => now(),
        ])->save();

        return $session;
    }
}
