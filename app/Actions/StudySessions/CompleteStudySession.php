<?php

namespace App\Actions\StudySessions;

use App\Enums\StudySessionStatus;
use App\Models\StudySession;
use Illuminate\Validation\ValidationException;

class CompleteStudySession
{
    /**
     * Stop the stopwatch for good, folding in any running segment and
     * recording the final duration. Valid from either running or paused.
     */
    public function handle(StudySession $session, ?string $note = null): StudySession
    {
        if ($session->status === StudySessionStatus::Completed) {
            throw ValidationException::withMessages([
                'status' => 'Esta sessão já foi concluída.',
            ]);
        }

        $session->forceFill([
            'status' => StudySessionStatus::Completed,
            'duration_seconds' => $session->currentDurationSeconds(),
            'started_at' => null,
            'completed_at' => now(),
            'note' => $note ?? $session->note,
        ])->save();

        return $session;
    }
}
