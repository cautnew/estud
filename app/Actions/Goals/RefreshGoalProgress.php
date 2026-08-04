<?php

namespace App\Actions\Goals;

use App\Models\Goal;
use App\Models\User;

class RefreshGoalProgress
{
    public function __construct(
        private readonly CalculateGoalProgressValue $calculateGoalProgressValue,
    ) {}

    /**
     * Recompute and upsert the current-period progress for every one of
     * the owner's goals, so the visual tracking is always up to date the
     * moment a study session, topic, review, or question log changes.
     */
    public function handle(User $owner): void
    {
        Goal::query()
            ->ownedBy($owner)
            ->get()
            ->each(fn (Goal $goal) => $this->refreshOne($goal));
    }

    private function refreshOne(Goal $goal): void
    {
        [$periodStart, $periodEnd] = $goal->currentPeriodBounds();

        $value = $this->calculateGoalProgressValue->handle($goal, $periodStart, $periodEnd);
        $achieved = $value >= $goal->target;

        // A plain `where('period_start', ...)` lookup can't be trusted to
        // match: the `date` cast serializes with a midnight time suffix on
        // write but we search with a bare Y-m-d string, so updateOrCreate()
        // would never find the existing row. whereDate() compares just the
        // date part and is immune to that formatting mismatch.
        $progress = $goal->goalProgress()
            ->whereDate('period_start', $periodStart->toDateString())
            ->first();

        if ($progress) {
            $progress->forceFill([
                'period_end' => $periodEnd->toDateString(),
                'current_value' => $value,
                'achieved' => $achieved,
            ])->save();

            return;
        }

        $goal->goalProgress()->create([
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
            'current_value' => $value,
            'achieved' => $achieved,
        ]);
    }
}
