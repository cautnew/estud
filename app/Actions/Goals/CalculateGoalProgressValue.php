<?php

namespace App\Actions\Goals;

use App\Enums\GoalType;
use App\Enums\ReviewStatus;
use App\Enums\StudyPlanTopicStatus;
use App\Models\Goal;
use App\Models\QuestionLog;
use App\Models\Review;
use App\Models\StudyPlanSubject;
use App\Models\StudyPlanTopic;
use App\Models\StudySession;
use Carbon\CarbonInterface;

class CalculateGoalProgressValue
{
    /**
     * Compute how much progress a goal has made within a given period,
     * from the relevant source table for its type. Optionally scoped to
     * a single subject when the goal targets one.
     */
    public function handle(Goal $goal, CarbonInterface $periodStart, CarbonInterface $periodEnd): float
    {
        return match ($goal->type) {
            GoalType::Hours => $this->hoursStudied($goal, $periodStart, $periodEnd),
            GoalType::Subjects => $this->subjectsCompleted($goal, $periodStart, $periodEnd),
            GoalType::Topics => $this->topicsCompleted($goal, $periodStart, $periodEnd),
            GoalType::Reviews => $this->reviewsCompleted($goal, $periodStart, $periodEnd),
            GoalType::Questions => $this->questionsResolved($goal, $periodStart, $periodEnd),
        };
    }

    private function hoursStudied(Goal $goal, CarbonInterface $start, CarbonInterface $end): float
    {
        $query = StudySession::query()
            ->where('owner_id', $goal->owner_id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()]);

        if ($goal->subject_id) {
            $query->where('subject_id', $goal->subject_id);
        }

        return round($query->sum('duration_seconds') / 3600, 2);
    }

    private function subjectsCompleted(Goal $goal, CarbonInterface $start, CarbonInterface $end): float
    {
        $query = StudyPlanSubject::query()
            ->whereHas('studyPlan', fn ($q) => $q->where('owner_id', $goal->owner_id))
            ->with('studyPlanTopics')
            ->withCount([
                'studyPlanTopics',
                'studyPlanTopics as done_topics_count' => fn ($q) => $q->where('status', StudyPlanTopicStatus::Done),
            ]);

        if ($goal->subject_id) {
            $query->where('subject_id', $goal->subject_id);
        }

        return (float) $query->get()
            ->filter(fn (StudyPlanSubject $subject): bool => $subject->study_plan_topics_count > 0
                && $subject->study_plan_topics_count === $subject->done_topics_count)
            ->filter(function (StudyPlanSubject $subject) use ($start, $end): bool {
                $completedAt = $subject->studyPlanTopics->max('studied_at');

                return $completedAt !== null && $completedAt->between($start, $end);
            })
            ->count();
    }

    private function topicsCompleted(Goal $goal, CarbonInterface $start, CarbonInterface $end): float
    {
        $query = StudyPlanTopic::query()
            ->whereHas('studyPlanSubject.studyPlan', fn ($q) => $q->where('owner_id', $goal->owner_id))
            ->where('status', StudyPlanTopicStatus::Done)
            ->whereBetween('studied_at', [$start, $end->copy()->endOfDay()]);

        if ($goal->subject_id) {
            $query->whereHas('studyPlanSubject', fn ($q) => $q->where('subject_id', $goal->subject_id));
        }

        return (float) $query->count();
    }

    private function reviewsCompleted(Goal $goal, CarbonInterface $start, CarbonInterface $end): float
    {
        $query = Review::query()
            ->where('owner_id', $goal->owner_id)
            ->where('status', ReviewStatus::Completed)
            ->whereBetween('completed_at', [$start, $end->copy()->endOfDay()]);

        if ($goal->subject_id) {
            $query->whereHas(
                'studyPlanTopic.studyPlanSubject',
                fn ($q) => $q->where('subject_id', $goal->subject_id)
            );
        }

        return (float) $query->count();
    }

    private function questionsResolved(Goal $goal, CarbonInterface $start, CarbonInterface $end): float
    {
        $query = QuestionLog::query()
            ->where('owner_id', $goal->owner_id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()]);

        if ($goal->subject_id) {
            $query->where('subject_id', $goal->subject_id);
        }

        return (float) $query->sum('total');
    }
}
