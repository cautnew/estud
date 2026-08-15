<?php

namespace App\Actions\Recommendations;

use App\Actions\Recommendations\Concerns\DeterminesScheduleOccurrence;
use App\Enums\ReviewStatus;
use App\Models\Goal;
use App\Models\GoalProgress;
use App\Models\Review;
use App\Models\ScheduleItem;
use App\Models\StudySession;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Computes the user's Health Score (0-100): a single number summarizing
 * how well they're keeping up with their studies across four weighted
 * dimensions — constância, metas, revisões, cronograma.
 *
 * Full write-up of the reasoning behind every weight and constant below
 * lives in docs/health-score-algorithm.md; keep the two in sync.
 */
class CalculateHealthScore
{
    use DeterminesScheduleOccurrence;

    /**
     * Component weights in points (must sum to 100).
     */
    private const CONSISTENCY_WEIGHT = 30;

    private const GOALS_WEIGHT = 25;

    private const REVIEWS_WEIGHT = 25;

    private const SCHEDULE_WEIGHT = 20;

    /**
     * Lookback window for the "share of active days" half of the
     * consistency component.
     */
    private const CONSISTENCY_WINDOW_DAYS = 14;

    /**
     * Streak length (in days) that earns full marks on the "momentum"
     * half of the consistency component.
     */
    private const STREAK_TARGET_DAYS = 7;

    /**
     * Lookback window for the "reviews completed on time" ratio.
     */
    private const REVIEW_WINDOW_DAYS = 60;

    /**
     * Points deducted per currently-overdue review, capped so a single
     * bad week can't zero out the whole component.
     */
    private const OVERDUE_PENALTY_PER_REVIEW = 2.0;

    private const MAX_OVERDUE_PENALTY = 10.0;

    /**
     * Lookback window for cronograma adherence.
     */
    private const SCHEDULE_WINDOW_DAYS = 7;

    /**
     * @return array{score: int, label: string, components: array<string, array{score: float, weight: int, details: array<string, int>}>}
     */
    public function handle(User $user): array
    {
        $consistency = $this->consistencyComponent($user);
        $goals = $this->goalsComponent($user);
        $reviews = $this->reviewsComponent($user);
        $schedule = $this->scheduleComponent($user);

        $total = $consistency['score'] + $goals['score'] + $reviews['score'] + $schedule['score'];

        return [
            'score' => (int) round($total),
            'label' => $this->label($total),
            'components' => [
                'consistency' => $consistency,
                'goals' => $goals,
                'reviews' => $reviews,
                'schedule' => $schedule,
            ],
        ];
    }

    /**
     * Rewards both a sustained study habit (share of active days in the
     * last CONSISTENCY_WINDOW_DAYS days) and momentum (the current streak,
     * capped at STREAK_TARGET_DAYS), split evenly between the two.
     *
     * @return array{score: float, weight: int, details: array<string, int>}
     */
    private function consistencyComponent(User $user): array
    {
        $activeDates = StudySession::query()
            ->ownedBy($user)
            ->where('date', '>=', today()->subDays(self::CONSISTENCY_WINDOW_DAYS - 1))
            ->selectRaw('date, sum(duration_seconds) as seconds')
            ->groupBy('date')
            ->get()
            ->filter(fn ($row): bool => (int) $row->seconds > 0)
            ->map(fn ($row): string => $row->date->toDateString())
            ->flip();

        $activeDays = $activeDates->count();
        $activityRatio = $activeDays / self::CONSISTENCY_WINDOW_DAYS;

        $streak = $this->currentStreak($activeDates);
        $streakRatio = min($streak / self::STREAK_TARGET_DAYS, 1);

        $score = ($activityRatio * 0.5 + $streakRatio * 0.5) * self::CONSISTENCY_WEIGHT;

        return [
            'score' => round($score, 2),
            'weight' => self::CONSISTENCY_WEIGHT,
            'details' => [
                'activeDays' => $activeDays,
                'windowDays' => self::CONSISTENCY_WINDOW_DAYS,
                'currentStreak' => $streak,
            ],
        ];
    }

    /**
     * Walks backward from today (or yesterday, if today has no logged
     * activity yet — a grace day so an ongoing streak isn't zeroed out
     * before the day is over) counting consecutive active days.
     *
     * @param  Collection<string, int>  $activeDates  date string keys, from ->flip()
     */
    private function currentStreak(Collection $activeDates): int
    {
        $cursor = $activeDates->has(today()->toDateString())
            ? today()
            : today()->subDay();

        $streak = 0;

        while ($activeDates->has($cursor->toDateString())) {
            $streak++;
            $cursor = $cursor->subDay();
        }

        return $streak;
    }

    /**
     * Share of the user's goals currently on track this period. Mirrors
     * `CalculateDashboardStats::goalsIndicator()`. No goals configured
     * scores 0 — deliberately, to nudge goal-setting rather than award a
     * free pass (see docs/health-score-algorithm.md).
     *
     * @return array{score: float, weight: int, details: array<string, int>}
     */
    private function goalsComponent(User $user): array
    {
        $goals = Goal::query()->ownedBy($user)->with('goalProgress')->get();

        if ($goals->isEmpty()) {
            return [
                'score' => 0.0,
                'weight' => self::GOALS_WEIGHT,
                'details' => ['onTrack' => 0, 'total' => 0],
            ];
        }

        $onTrack = $goals->filter(function (Goal $goal): bool {
            [$periodStart] = $goal->currentPeriodBounds();

            $progress = $goal->goalProgress
                ->first(fn (GoalProgress $progress): bool => $progress->period_start->isSameDay($periodStart));

            return $progress !== null && $progress->achieved;
        })->count();

        $score = ($onTrack / $goals->count()) * self::GOALS_WEIGHT;

        return [
            'score' => round($score, 2),
            'weight' => self::GOALS_WEIGHT,
            'details' => ['onTrack' => $onTrack, 'total' => $goals->count()],
        ];
    }

    /**
     * Rewards completing reviews as they come due (within
     * REVIEW_WINDOW_DAYS), then subtracts a capped penalty for the number
     * of reviews currently overdue — so a large backlog can't hide behind
     * a good historical completion ratio.
     *
     * @return array{score: float, weight: int, details: array<string, int>}
     */
    private function reviewsComponent(User $user): array
    {
        $due = Review::query()
            ->ownedBy($user)
            ->where('scheduled_for', '<=', today())
            ->where('scheduled_for', '>=', today()->subDays(self::REVIEW_WINDOW_DAYS))
            ->get();

        $overdueCount = Review::query()
            ->ownedBy($user)
            ->where('status', ReviewStatus::Pending)
            ->where('scheduled_for', '<', today())
            ->count();

        if ($due->isEmpty()) {
            return [
                'score' => 0.0,
                'weight' => self::REVIEWS_WEIGHT,
                'details' => ['completed' => 0, 'due' => 0, 'overdue' => $overdueCount],
            ];
        }

        $completed = $due->where('status', ReviewStatus::Completed)->count();
        $base = ($completed / $due->count()) * self::REVIEWS_WEIGHT;
        $penalty = min($overdueCount * self::OVERDUE_PENALTY_PER_REVIEW, self::MAX_OVERDUE_PENALTY);
        $score = max($base - $penalty, 0.0);

        return [
            'score' => round($score, 2),
            'weight' => self::REVIEWS_WEIGHT,
            'details' => ['completed' => $completed, 'due' => $due->count(), 'overdue' => $overdueCount],
        ];
    }

    /**
     * How closely study sessions matched the cronograma over the last
     * SCHEDULE_WINDOW_DAYS days: for every (day, subject) pair the
     * schedule called for, was a study session logged for that subject on
     * that day?
     *
     * @return array{score: float, weight: int, details: array<string, int>}
     */
    private function scheduleComponent(User $user): array
    {
        $items = ScheduleItem::query()
            ->whereHas('schedule', fn ($query) => $query->where('owner_id', $user->id))
            ->get();

        if ($items->isEmpty()) {
            return [
                'score' => 0.0,
                'weight' => self::SCHEDULE_WEIGHT,
                'details' => ['matched' => 0, 'occurrences' => 0],
            ];
        }

        $studiedPairs = StudySession::query()
            ->ownedBy($user)
            ->where('date', '>=', today()->subDays(self::SCHEDULE_WINDOW_DAYS - 1))
            ->where('duration_seconds', '>', 0)
            ->get(['date', 'subject_id'])
            ->map(fn (StudySession $session): string => $session->date->toDateString().':'.$session->subject_id)
            ->flip();

        $occurrences = 0;
        $matched = 0;

        foreach (range(0, self::SCHEDULE_WINDOW_DAYS - 1) as $daysAgo) {
            $date = today()->subDays($daysAgo);

            foreach ($items as $item) {
                if (! $this->scheduleItemOccursOn($item, $date)) {
                    continue;
                }

                $occurrences++;

                if ($studiedPairs->has($date->toDateString().':'.$item->subject_id)) {
                    $matched++;
                }
            }
        }

        if ($occurrences === 0) {
            return [
                'score' => 0.0,
                'weight' => self::SCHEDULE_WEIGHT,
                'details' => ['matched' => 0, 'occurrences' => 0],
            ];
        }

        $score = ($matched / $occurrences) * self::SCHEDULE_WEIGHT;

        return [
            'score' => round($score, 2),
            'weight' => self::SCHEDULE_WEIGHT,
            'details' => ['matched' => $matched, 'occurrences' => $occurrences],
        ];
    }

    private function label(float $score): string
    {
        return match (true) {
            $score >= 80 => 'Excelente',
            $score >= 60 => 'Bom',
            $score >= 40 => 'Regular',
            $score >= 20 => 'Atenção',
            default => 'Crítico',
        };
    }
}
