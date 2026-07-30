<?php

namespace App\Actions\StudyCycles;

class CalculateStudyCycleItemScore
{
    /**
     * Relative weight of each factor in the final score. Difficulty and
     * importance are combined into a single "relevance" factor before
     * weighting, mirroring the classic peso = dificuldade + importância
     * rule of thumb used in manual study-cycle planning.
     */
    private const RELEVANCE_WEIGHT = 0.40;

    private const PROGRESS_WEIGHT = 0.35;

    private const DELAY_WEIGHT = 0.25;

    /**
     * Number of days of delay ("atraso") beyond which an item is treated as
     * maximally overdue. Also the delay assumed for a subject that has
     * never been studied, so it dominates freshly-studied ones.
     */
    private const MAX_DELAY_DAYS = 30;

    /**
     * Score an item's priority (0-100, higher = more urgent) from its
     * difficulty (1-5), importance (1-5), progress (0-100), and days since
     * it was last studied (null = never studied, treated as maximally
     * overdue).
     */
    public function handle(int $difficulty, int $importance, int $progress, ?int $daysSinceLastStudied): float
    {
        $relevance = (($difficulty / 5) + ($importance / 5)) / 2;
        $missingProgress = (100 - $progress) / 100;
        $delayDays = min($daysSinceLastStudied ?? self::MAX_DELAY_DAYS, self::MAX_DELAY_DAYS);

        $score = $relevance * self::RELEVANCE_WEIGHT
            + $missingProgress * self::PROGRESS_WEIGHT
            + ($delayDays / self::MAX_DELAY_DAYS) * self::DELAY_WEIGHT;

        return round($score * 100, 2);
    }
}
