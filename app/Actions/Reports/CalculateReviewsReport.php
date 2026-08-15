<?php

namespace App\Actions\Reports;

use App\Actions\Reports\Concerns\BuildsReportEnvelope;
use App\Enums\ReportType;
use App\Enums\ReviewStatus;
use App\Models\Review;
use App\Models\User;
use App\Reports\ReportFilters;

/**
 * "Revisões": every spaced-repetition review scheduled within the
 * filtered period, with completion/skip/overdue counts.
 *
 * Honors `subjectId` and `studyPlanId`, both applied through the review's
 * `studyPlanTopic.studyPlanSubject` chain.
 */
class CalculateReviewsReport
{
    use BuildsReportEnvelope;

    /**
     * @return array{title: string, filters_description: string, summary: array<int, array{label: string, value: string}>, columns: array<int, string>, rows: array<int, array<int, string>>}
     */
    public function handle(User $user, ReportFilters $filters): array
    {
        $query = Review::query()
            ->ownedBy($user)
            ->whereDate('scheduled_for', '>=', $filters->from)
            ->whereDate('scheduled_for', '<=', $filters->to)
            ->with(['studyPlanTopic.topic', 'studyPlanTopic.studyPlanSubject.subject']);

        if ($filters->subjectId !== null) {
            $query->whereHas('studyPlanTopic.studyPlanSubject', fn ($q) => $q->where('subject_id', $filters->subjectId));
        }

        if ($filters->studyPlanId !== null) {
            $query->whereHas('studyPlanTopic.studyPlanSubject', fn ($q) => $q->where('study_plan_id', $filters->studyPlanId));
        }

        $reviews = $query->orderBy('scheduled_for')->get();

        $completed = $reviews->where('status', ReviewStatus::Completed)->count();
        $pending = $reviews->where('status', ReviewStatus::Pending)->count();
        $skipped = $reviews->where('status', ReviewStatus::Skipped)->count();
        $overdue = $reviews
            ->filter(fn (Review $review): bool => $review->status === ReviewStatus::Pending && $review->scheduled_for->lt(today()))
            ->count();
        $completionRate = $reviews->isNotEmpty() ? ($completed / $reviews->count()) * 100 : 0.0;

        $rows = $reviews->map(fn (Review $review): array => [
            $review->scheduled_for->format('d/m/Y'),
            $review->studyPlanTopic->studyPlanSubject->subject->name,
            $review->studyPlanTopic->topic->name,
            'D+'.$review->interval_days,
            $review->status->label(),
        ])->all();

        return $this->envelope(
            title: ReportType::Reviews->label(),
            filters: $filters,
            summary: [
                ['label' => 'Total de revisões', 'value' => (string) $reviews->count()],
                ['label' => 'Concluídas', 'value' => (string) $completed],
                ['label' => 'Pendentes', 'value' => (string) $pending],
                ['label' => 'Puladas', 'value' => (string) $skipped],
                ['label' => 'Atrasadas', 'value' => (string) $overdue],
                ['label' => 'Taxa de conclusão', 'value' => $this->formatPercentage($completionRate)],
            ],
            columns: ['Data Agendada', 'Disciplina', 'Tópico', 'Intervalo', 'Status'],
            rows: $rows,
        );
    }
}
