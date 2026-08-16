<?php

namespace App\Models;

use App\Enums\GoalPeriod;
use App\Enums\GoalType;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasOwner;
use Carbon\CarbonInterface;
use Database\Factories\GoalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $owner_id
 * @property int|null $subject_id
 * @property GoalType $type
 * @property GoalPeriod $period
 * @property float $target
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['subject_id', 'type', 'period', 'target'])]
class Goal extends Model
{
    /** @use HasFactory<GoalFactory> */
    use Auditable, HasFactory, HasOwner;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => GoalType::class,
            'period' => GoalPeriod::class,
            'target' => 'float',
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
     * @return HasMany<GoalProgress, $this>
     */
    public function goalProgress(): HasMany
    {
        return $this->hasMany(GoalProgress::class)->orderBy('period_start');
    }

    /**
     * The [start, end] dates of the period this goal is currently in,
     * based on its recurrence (daily/weekly/monthly) and the current date.
     *
     * @return array{0: CarbonInterface, 1: CarbonInterface}
     */
    public function currentPeriodBounds(): array
    {
        return match ($this->period) {
            GoalPeriod::Daily => [today(), today()],
            GoalPeriod::Weekly => [today()->startOfWeek(), today()->endOfWeek()],
            GoalPeriod::Monthly => [today()->startOfMonth(), today()->endOfMonth()],
        };
    }
}
