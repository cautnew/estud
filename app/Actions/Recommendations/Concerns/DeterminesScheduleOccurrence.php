<?php

namespace App\Actions\Recommendations\Concerns;

use App\Models\ScheduleItem;
use Carbon\CarbonInterface;

/**
 * Determines whether a cronograma (schedule) item recurs on a given date.
 *
 * `ScheduleItem` stores only a day-of-week OR a day-of-month, with no
 * anchor start date on either `Schedule` or `ScheduleItem`. This means an
 * alternating cadence (Biweekly/Quarterly) cannot be distinguished from
 * its simpler counterpart (Weekly/Monthly) — both fall back to matching
 * the same day-of-week/day-of-month on every occurrence. This is a
 * documented simplification, not a bug: see docs/health-score-algorithm.md.
 *
 * Shared by `CalculateHealthScore` (cronograma adherence component) and
 * `GenerateStudyRecommendations` ("O que estudar hoje"), so the two never
 * disagree about what "today's schedule" means.
 */
trait DeterminesScheduleOccurrence
{
    protected function scheduleItemOccursOn(ScheduleItem $item, CarbonInterface $date): bool
    {
        if ($item->day_of_week !== null) {
            return $item->day_of_week === $date->dayOfWeek;
        }

        if ($item->day_of_month !== null) {
            return $item->day_of_month === $date->day;
        }

        return true;
    }
}
