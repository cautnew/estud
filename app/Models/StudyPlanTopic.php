<?php

namespace App\Models;

use App\Enums\StudyPlanTopicStatus;
use Database\Factories\StudyPlanTopicFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $study_plan_subject_id
 * @property int $topic_id
 * @property int $order
 * @property StudyPlanTopicStatus $status
 * @property Carbon|null $studied_at
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['topic_id', 'order', 'status', 'studied_at', 'notes'])]
class StudyPlanTopic extends Model
{
    /** @use HasFactory<StudyPlanTopicFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => StudyPlanTopicStatus::class,
            'studied_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<StudyPlanSubject, $this>
     */
    public function studyPlanSubject(): BelongsTo
    {
        return $this->belongsTo(StudyPlanSubject::class);
    }

    /**
     * @return BelongsTo<Topic, $this>
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }
}
