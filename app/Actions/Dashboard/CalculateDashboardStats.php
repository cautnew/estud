<?php

namespace App\Actions\Dashboard;

use App\Actions\QuestionLogs\CalculateQuestionStats;
use App\Enums\ReviewStatus;
use App\Enums\StudyPlanTopicStatus;
use App\Models\Goal;
use App\Models\GoalProgress;
use App\Models\QuestionLog;
use App\Models\Review;
use App\Models\StudyPlanSubject;
use App\Models\StudyPlanTopic;
use App\Models\StudySession;
use App\Models\User;
use Illuminate\Support\Collection;

class CalculateDashboardStats
{
    public function __construct(
        private readonly CalculateQuestionStats $calculateQuestionStats,
    ) {}

    /**
     * Build every indicator and chart dataset the advanced dashboard needs,
     * in one pass: hours studied, evolution, subjects/topics completed,
     * reviews, goal pace, the activity heatmap, monthly evolution, a
     * period-over-period comparison, accuracy-by-subject (for the radar),
     * and a study-time subject ranking.
     *
     * @return array<string, mixed>
     */
    public function handle(User $owner): array
    {
        $dailySeconds = $this->dailyStudySeconds($owner);

        return [
            'indicators' => [
                'hoursStudied' => $this->hoursStudiedIndicator($dailySeconds),
                'subjectsCompleted' => $this->subjectsCompletedCount($owner),
                'topicsCompleted' => $this->topicsCompletedCount($owner),
                'reviews' => $this->reviewsIndicator($owner),
                'goals' => $this->goalsIndicator($owner),
            ],
            'heatmap' => $dailySeconds
                ->map(fn (int $seconds, string $date): array => [
                    'date' => $date,
                    'hours' => round($seconds / 3600, 2),
                ])
                ->values()
                ->all(),
            'monthlyEvolution' => $this->monthlyEvolution($dailySeconds),
            'periodComparison' => $this->periodComparison($owner),
            'radarBySubject' => array_slice($this->calculateQuestionStats->handle($owner)['bySubject'], 0, 8),
            'subjectRanking' => $this->subjectRanking($owner),
        ];
    }

    /**
     * Total study seconds per day for the last 365 days, including days
     * with no activity, keyed by date string in chronological order.
     *
     * @return Collection<string, int>
     */
    private function dailyStudySeconds(User $owner): Collection
    {
        $start = today()->subDays(364)->toDateString();

        $byDate = StudySession::query()
            ->ownedBy($owner)
            ->selectRaw('date, sum(duration_seconds) as seconds')
            ->where('date', '>=', $start)
            ->groupBy('date')
            ->get()
            ->mapWithKeys(fn (StudySession $row): array => [$row->date->toDateString() => (int) $row->seconds]);

        return collect(range(364, 0))
            ->map(fn (int $daysAgo): string => today()->subDays($daysAgo)->toDateString())
            ->mapWithKeys(fn (string $date): array => [$date => $byDate->get($date, 0)]);
    }

    /**
     * @param  Collection<string, int>  $dailySeconds
     * @return array{hours: float, deltaPercent: float}
     */
    private function hoursStudiedIndicator(Collection $dailySeconds): array
    {
        $thisMonthStart = today()->startOfMonth()->toDateString();
        $lastMonthStart = today()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $lastMonthEnd = today()->subMonthNoOverflow()->endOfMonth()->toDateString();

        $thisMonthHours = round(
            $dailySeconds->filter(fn (int $seconds, string $date): bool => $date >= $thisMonthStart)->sum() / 3600,
            1
        );

        $lastMonthHours = round(
            $dailySeconds->filter(fn (int $seconds, string $date): bool => $date >= $lastMonthStart && $date <= $lastMonthEnd)->sum() / 3600,
            1
        );

        $deltaPercent = $lastMonthHours > 0
            ? round((($thisMonthHours - $lastMonthHours) / $lastMonthHours) * 100, 1)
            : ($thisMonthHours > 0 ? 100.0 : 0.0);

        return [
            'hours' => $thisMonthHours,
            'deltaPercent' => $deltaPercent,
        ];
    }

    /**
     * @param  Collection<string, int>  $dailySeconds
     * @return array<int, array{month: string, hours: float}>
     */
    private function monthlyEvolution(Collection $dailySeconds): array
    {
        return collect(range(5, 0))
            ->map(fn (int $monthsAgo) => today()->subMonthsNoOverflow($monthsAgo)->startOfMonth())
            ->map(function ($monthStart) use ($dailySeconds): array {
                $monthStartStr = $monthStart->toDateString();
                $monthEndStr = $monthStart->copy()->endOfMonth()->toDateString();

                $seconds = $dailySeconds->filter(
                    fn (int $seconds, string $date): bool => $date >= $monthStartStr && $date <= $monthEndStr
                )->sum();

                return [
                    'month' => $monthStart->format('Y-m'),
                    'hours' => round($seconds / 3600, 1),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Compare this rolling 7-day period against the previous one across
     * hours studied, questions resolved, and topics completed.
     *
     * @return array{current: array{hours: float, questions: int, topics: int}, previous: array{hours: float, questions: int, topics: int}}
     */
    private function periodComparison(User $owner): array
    {
        $currentStart = today()->subDays(6)->toDateString();
        $previousStart = today()->subDays(13)->toDateString();
        $previousEnd = today()->subDays(7)->toDateString();

        $sessions = StudySession::query()
            ->ownedBy($owner)
            ->where('date', '>=', $previousStart)
            ->get();

        $questionLogs = QuestionLog::query()
            ->ownedBy($owner)
            ->where('date', '>=', $previousStart)
            ->get();

        $topics = StudyPlanTopic::query()
            ->whereHas('studyPlanSubject.studyPlan', fn ($query) => $query->where('owner_id', $owner->id))
            ->where('status', StudyPlanTopicStatus::Done)
            ->whereNotNull('studied_at')
            ->where('studied_at', '>=', $previousStart)
            ->get();

        return [
            'current' => [
                'hours' => round($sessions->filter(fn (StudySession $s): bool => $s->date->toDateString() >= $currentStart)->sum('duration_seconds') / 3600, 1),
                'questions' => (int) $questionLogs->filter(fn (QuestionLog $q): bool => $q->date->toDateString() >= $currentStart)->sum('total'),
                'topics' => $topics->filter(fn (StudyPlanTopic $t): bool => $t->studied_at->toDateString() >= $currentStart)->count(),
            ],
            'previous' => [
                'hours' => round($sessions->filter(fn (StudySession $s): bool => $s->date->toDateString() >= $previousStart && $s->date->toDateString() <= $previousEnd)->sum('duration_seconds') / 3600, 1),
                'questions' => (int) $questionLogs->filter(fn (QuestionLog $q): bool => $q->date->toDateString() >= $previousStart && $q->date->toDateString() <= $previousEnd)->sum('total'),
                'topics' => $topics->filter(fn (StudyPlanTopic $t): bool => $t->studied_at->toDateString() >= $previousStart && $t->studied_at->toDateString() <= $previousEnd)->count(),
            ],
        ];
    }

    private function subjectsCompletedCount(User $owner): int
    {
        return StudyPlanSubject::query()
            ->whereHas('studyPlan', fn ($query) => $query->where('owner_id', $owner->id))
            ->withCount([
                'studyPlanTopics',
                'studyPlanTopics as done_topics_count' => fn ($query) => $query->where('status', StudyPlanTopicStatus::Done),
            ])
            ->get()
            ->filter(fn (StudyPlanSubject $subject): bool => $subject->study_plan_topics_count > 0
                && $subject->study_plan_topics_count === $subject->done_topics_count)
            ->count();
    }

    private function topicsCompletedCount(User $owner): int
    {
        return StudyPlanTopic::query()
            ->whereHas('studyPlanSubject.studyPlan', fn ($query) => $query->where('owner_id', $owner->id))
            ->where('status', StudyPlanTopicStatus::Done)
            ->count();
    }

    /**
     * @return array{completed: int, pending: int, overdue: int}
     */
    private function reviewsIndicator(User $owner): array
    {
        return [
            'completed' => Review::query()->ownedBy($owner)->where('status', ReviewStatus::Completed)->count(),
            'pending' => Review::query()->ownedBy($owner)->where('status', ReviewStatus::Pending)->where('scheduled_for', '>=', today())->count(),
            'overdue' => Review::query()->ownedBy($owner)->where('status', ReviewStatus::Pending)->where('scheduled_for', '<', today())->count(),
        ];
    }

    /**
     * How many of the user's goals have already hit their target in the
     * goal's own current period (daily/weekly/monthly). Relies on
     * RefreshGoalProgress having kept goal_progress up to date.
     *
     * @return array{onTrack: int, total: int}
     */
    private function goalsIndicator(User $owner): array
    {
        $goals = Goal::query()->ownedBy($owner)->with('goalProgress')->get();

        $onTrack = $goals->filter(function (Goal $goal) {
            [$periodStart] = $goal->currentPeriodBounds();

            return $goal->goalProgress
                ->first(fn (GoalProgress $progress) => $progress->period_start->isSameDay($periodStart))
                ?->achieved ?? false;
        })->count();

        return [
            'onTrack' => $onTrack,
            'total' => $goals->count(),
        ];
    }

    /**
     * @return array<int, array{subject_id: int, subject: string, hours: float}>
     */
    private function subjectRanking(User $owner): array
    {
        return StudySession::query()
            ->join('subjects', 'subjects.id', '=', 'study_sessions.subject_id')
            ->where('study_sessions.owner_id', $owner->id)
            ->selectRaw('subjects.id as subject_id, subjects.name as subject, sum(study_sessions.duration_seconds) as seconds')
            ->groupBy('subjects.id', 'subjects.name')
            ->orderByDesc('seconds')
            ->limit(8)
            ->get()
            ->map(fn ($row): array => [
                'subject_id' => (int) $row->subject_id,
                'subject' => $row->subject,
                'hours' => round(((int) $row->seconds) / 3600, 1),
            ])
            ->all();
    }
}
