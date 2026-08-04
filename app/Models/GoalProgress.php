<?php

namespace App\Models;

use Database\Factories\GoalProgressFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $goal_id
 * @property Carbon $period_start
 * @property Carbon $period_end
 * @property float $current_value
 * @property bool $achieved
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['period_start', 'period_end', 'current_value', 'achieved'])]
class GoalProgress extends Model
{
    /** @use HasFactory<GoalProgressFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'current_value' => 'float',
            'achieved' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Goal, $this>
     */
    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }
}
