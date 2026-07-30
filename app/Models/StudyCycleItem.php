<?php

namespace App\Models;

use Database\Factories\StudyCycleItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $study_cycle_id
 * @property int $subject_id
 * @property int $difficulty
 * @property int $importance
 * @property int $progress
 * @property Carbon|null $last_studied_at
 * @property float $priority_score
 * @property int $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['subject_id', 'difficulty', 'importance', 'progress', 'last_studied_at', 'order'])]
class StudyCycleItem extends Model
{
    /** @use HasFactory<StudyCycleItemFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'difficulty' => 'integer',
            'importance' => 'integer',
            'progress' => 'integer',
            'last_studied_at' => 'datetime',
            'priority_score' => 'float',
            'order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<StudyCycle, $this>
     */
    public function studyCycle(): BelongsTo
    {
        return $this->belongsTo(StudyCycle::class);
    }

    /**
     * @return BelongsTo<Subject, $this>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
