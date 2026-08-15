<?php

namespace App\Actions\Recommendations;

use App\Actions\Recommendations\Concerns\DeterminesScheduleOccurrence;
use App\Enums\StudyPlanTopicStatus;
use App\Models\ScheduleItem;
use App\Models\StudyCycleItem;
use App\Models\StudyPlanTopic;
use App\Models\User;

/**
 * Builds the "O que estudar hoje" panel by combining three independent
 * signals, concatenated in priority order rather than merged into a single
 * ranked list — the three sources aren't on a comparable numeric scale
 * (schedule priority is an arbitrary ordinal, study-cycle priority_score
 * is 0-100, plan topics have no score at all), so forcing them onto one
 * axis would fabricate precision the data doesn't support. Documented in
 * docs/health-score-algorithm.md.
 *
 *   1. Cronograma: whatever the user's schedule(s) call for today — the
 *      most concrete signal, since the user explicitly planned it.
 *   2. Ciclo de estudos: the highest-priority items from the user's study
 *      cycle(s) (by their maintained `priority_score`), for subjects not
 *      already at 100% progress.
 *   3. Plano de estudos: pending/in-progress topics from the user's study
 *      plans, as a general fallback backlog when the above are empty or
 *      exhausted.
 */
class GenerateStudyRecommendations
{
    use DeterminesScheduleOccurrence;

    private const CYCLE_ITEM_LIMIT = 3;

    private const PLAN_TOPIC_LIMIT = 5;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function handle(User $user): array
    {
        return [
            ...$this->fromSchedule($user),
            ...$this->fromStudyCycle($user),
            ...$this->fromStudyPlan($user),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fromSchedule(User $user): array
    {
        $today = today();

        return ScheduleItem::query()
            ->whereHas('schedule', fn ($query) => $query->where('owner_id', $user->id))
            ->with('subject')
            ->get()
            ->filter(fn (ScheduleItem $item): bool => $this->scheduleItemOccursOn($item, $today))
            ->sortBy('priority')
            ->map(fn (ScheduleItem $item): array => [
                'source' => 'schedule',
                'subject_id' => $item->subject_id,
                'subject' => $item->subject->name,
                'topic_id' => null,
                'topic' => null,
                'label' => $item->start_time->format('H:i').' · '.$item->duration_minutes.' min',
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fromStudyCycle(User $user): array
    {
        return StudyCycleItem::query()
            ->whereHas('studyCycle', fn ($query) => $query->where('owner_id', $user->id))
            ->where('progress', '<', 100)
            ->with('subject')
            ->orderByDesc('priority_score')
            ->limit(self::CYCLE_ITEM_LIMIT)
            ->get()
            ->map(fn (StudyCycleItem $item): array => [
                'source' => 'cycle',
                'subject_id' => $item->subject_id,
                'subject' => $item->subject->name,
                'topic_id' => null,
                'topic' => null,
                'label' => 'Prioridade '.round($item->priority_score),
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fromStudyPlan(User $user): array
    {
        return StudyPlanTopic::query()
            ->whereHas('studyPlanSubject.studyPlan', fn ($query) => $query->where('owner_id', $user->id))
            ->whereIn('status', [StudyPlanTopicStatus::Pending, StudyPlanTopicStatus::InProgress])
            ->with(['topic', 'studyPlanSubject.subject'])
            ->orderBy('order')
            ->get()
            ->sortByDesc(fn (StudyPlanTopic $topic): bool => $topic->status === StudyPlanTopicStatus::InProgress)
            ->take(self::PLAN_TOPIC_LIMIT)
            ->map(fn (StudyPlanTopic $topic): array => [
                'source' => 'plan',
                'subject_id' => $topic->studyPlanSubject->subject_id,
                'subject' => $topic->studyPlanSubject->subject->name,
                'topic_id' => $topic->topic_id,
                'topic' => $topic->topic->name,
                'label' => $topic->status === StudyPlanTopicStatus::InProgress ? 'Em andamento' : 'Pendente',
            ])
            ->values()
            ->all();
    }
}
