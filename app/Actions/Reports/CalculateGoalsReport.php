<?php

namespace App\Actions\Reports;

use App\Actions\Reports\Concerns\BuildsReportEnvelope;
use App\Enums\ReportType;
use App\Models\GoalProgress;
use App\Models\User;
use App\Reports\ReportFilters;

/**
 * "Metas": every goal-progress period starting within the filtered range,
 * with target vs. achieved value and a success rate.
 *
 * Honors `subjectId` (via `Goal.subject_id`); `studyPlanId` doesn't apply
 * (goals aren't tied to a study plan).
 */
class CalculateGoalsReport
{
    use BuildsReportEnvelope;

    /**
     * @return array{title: string, filters_description: string, summary: array<int, array{label: string, value: string}>, columns: array<int, string>, rows: array<int, array<int, string>>}
     */
    public function handle(User $user, ReportFilters $filters): array
    {
        $query = GoalProgress::query()
            ->whereHas('goal', fn ($q) => $q->where('owner_id', $user->id))
            ->whereDate('period_start', '>=', $filters->from)
            ->whereDate('period_start', '<=', $filters->to)
            ->with('goal.subject');

        if ($filters->subjectId !== null) {
            $query->whereHas('goal', fn ($q) => $q->where('subject_id', $filters->subjectId));
        }

        $progress = $query->orderBy('period_start')->get();

        $total = $progress->count();
        $achieved = $progress->where('achieved', true)->count();
        $successRate = $total > 0 ? ($achieved / $total) * 100 : 0.0;

        $rows = $progress->map(function (GoalProgress $item): array {
            $goal = $item->goal;
            $label = $goal->type->label().($goal->subject !== null ? ' · '.$goal->subject->name : '');

            return [
                $label,
                $item->period_start->format('d/m/Y').' a '.$item->period_end->format('d/m/Y'),
                (string) $goal->target,
                (string) $item->current_value,
                $item->achieved ? 'Sim' : 'Não',
            ];
        })->all();

        return $this->envelope(
            title: ReportType::Goals->label(),
            filters: $filters,
            summary: [
                ['label' => 'Períodos avaliados', 'value' => (string) $total],
                ['label' => 'Períodos com meta atingida', 'value' => (string) $achieved],
                ['label' => 'Taxa de sucesso', 'value' => $this->formatPercentage($successRate)],
            ],
            columns: ['Meta', 'Período', 'Alvo', 'Valor Atual', 'Atingida'],
            rows: $rows,
        );
    }
}
