<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use App\Models\Concerns\HasOwner;
use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $owner_id
 * @property int $study_plan_topic_id
 * @property Carbon $scheduled_for
 * @property int $interval_days
 * @property ReviewStatus $status
 * @property Carbon|null $completed_at
 * @property Carbon|null $notified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['study_plan_topic_id', 'scheduled_for', 'interval_days'])]
class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory, HasOwner;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_for' => 'date',
            'interval_days' => 'integer',
            'status' => ReviewStatus::class,
            'completed_at' => 'datetime',
            'notified_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<StudyPlanTopic, $this>
     */
    public function studyPlanTopic(): BelongsTo
    {
        return $this->belongsTo(StudyPlanTopic::class);
    }
}
