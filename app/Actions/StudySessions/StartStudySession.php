<?php

namespace App\Actions\StudySessions;

use App\Enums\StudySessionStatus;
use App\Models\StudySession;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class StartStudySession
{
    /**
     * Start the stopwatch on a brand new study session for the given
     * subject/topic, owned by the acting user. A user may only run one
     * stopwatch at a time, so an existing running/paused session must be
     * completed first.
     */
    public function handle(User $owner, Subject $subject, ?Topic $topic, ?Carbon $date = null, ?string $note = null): StudySession
    {
        $hasOpenSession = StudySession::query()
            ->ownedBy($owner)
            ->whereIn('status', [StudySessionStatus::InProgress, StudySessionStatus::Paused])
            ->exists();

        if ($hasOpenSession) {
            throw ValidationException::withMessages([
                'subject_id' => 'Conclua a sessão de estudo em andamento antes de iniciar outra.',
            ]);
        }

        $session = new StudySession([
            'subject_id' => $subject->id,
            'topic_id' => $topic?->id,
            'date' => $date ?? now(),
            'note' => $note,
        ]);

        $session->forceFill([
            'owner_id' => $owner->id,
            'status' => StudySessionStatus::InProgress,
            'started_at' => now(),
            'duration_seconds' => 0,
        ])->save();

        return $session;
    }
}
