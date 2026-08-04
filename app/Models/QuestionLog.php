<?php

namespace App\Models;

use App\Models\Concerns\HasOwner;
use Database\Factories\QuestionLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
 * @property int $total
 * @property int $correct
 * @property int $incorrect
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['subject_id', 'topic_id', 'date', 'total', 'correct', 'incorrect'])]
class QuestionLog extends Model
{
    /** @use HasFactory<QuestionLogFactory> */
    use HasFactory, HasOwner;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total' => 'integer',
            'correct' => 'integer',
            'incorrect' => 'integer',
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
     * Percentage of resolved questions answered correctly (0 when none
     * were resolved, to avoid a division by zero).
     *
     * @return Attribute<float, never>
     */
    protected function accuracyRate(): Attribute
    {
        return Attribute::make(
            get: fn (): float => $this->total > 0
                ? round(($this->correct / $this->total) * 100, 2)
                : 0.0,
        );
    }
}
