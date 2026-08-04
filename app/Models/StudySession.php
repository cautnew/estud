<?php

namespace App\Models;

use App\Enums\StudySessionStatus;
use App\Models\Concerns\HasOwner;
use Database\Factories\StudySessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $owner_id
 * @property int $subject_id
 * @property int|null $topic_id
 * @property Carbon $date
 * @property StudySessionStatus $status
 * @property Carbon|null $started_at
 * @property int $duration_seconds
 * @property string|null $note
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['subject_id', 'topic_id', 'date', 'note'])]
class StudySession extends Model
{
    /** @use HasFactory<StudySessionFactory> */
    use HasFactory, HasOwner;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'status' => StudySessionStatus::class,
            'started_at' => 'datetime',
            'duration_seconds' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Subject, $this>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * @return BelongsTo<Topic, $this>
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    /**
     * Elapsed seconds so far, including the running segment when the
     * session is currently in progress, without persisting anything.
     */
    public function currentDurationSeconds(): int
    {
        if ($this->status !== StudySessionStatus::InProgress || $this->started_at === null) {
            return $this->duration_seconds;
        }

        return $this->duration_seconds + $this->started_at->diffInSeconds(now());
    }
}
