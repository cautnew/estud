<?php

namespace App\Actions\Reports;

use App\Actions\Reports\Concerns\BuildsReportEnvelope;
use App\Enums\ReportType;
use App\Models\QuestionLog;
use App\Models\User;
use App\Reports\ReportFilters;
use Illuminate\Support\Collection;

/**
 * "Desempenho": question accuracy within the filtered period, broken down
 * by subject.
 *
 * Honors `subjectId`; `studyPlanId` doesn't apply (question logs aren't
 * tied to a specific study plan).
 */
class CalculatePerformanceReport
{
    use BuildsReportEnvelope;

    /**
     * @return array{title: string, filters_description: string, summary: array<int, array{label: string, value: string}>, columns: array<int, string>, rows: array<int, array<int, string>>}
     */
    public function handle(User $user, ReportFilters $filters): array
    {
        $logs = QuestionLog::query()
            ->ownedBy($user)
            ->whereDate('date', '>=', $filters->from)
            ->whereDate('date', '<=', $filters->to)
            ->when($filters->subjectId, fn ($query, int $subjectId) => $query->where('subject_id', $subjectId))
            ->with('subject')
            ->get();

        $totalQuestions = (int) $logs->sum('total');
        $totalCorrect = (int) $logs->sum('correct');
        $totalIncorrect = (int) $logs->sum('incorrect');
        $accuracy = $totalQuestions > 0 ? ($totalCorrect / $totalQuestions) * 100 : 0.0;

        $rows = $logs
            ->groupBy('subject_id')
            ->map(function (Collection $subjectLogs): array {
                $total = (int) $subjectLogs->sum('total');
                $correct = (int) $subjectLogs->sum('correct');
                $incorrect = (int) $subjectLogs->sum('incorrect');
                $rate = $total > 0 ? ($correct / $total) * 100 : 0.0;

                return [
                    $subjectLogs->first()->subject->name,
                    (string) $total,
                    (string) $correct,
                    (string) $incorrect,
                    $this->formatPercentage($rate),
                    $total,
                ];
            })
            ->sortByDesc(fn (array $row): int => $row[5])
            ->map(fn (array $row): array => array_slice($row, 0, 5))
            ->values()
            ->all();

        return $this->envelope(
            title: ReportType::Performance->label(),
            filters: $filters,
            summary: [
                ['label' => 'Total de questões', 'value' => (string) $totalQuestions],
                ['label' => 'Acertos', 'value' => (string) $totalCorrect],
                ['label' => 'Erros', 'value' => (string) $totalIncorrect],
                ['label' => 'Taxa de acerto', 'value' => $this->formatPercentage($accuracy)],
            ],
            columns: ['Disciplina', 'Total', 'Acertos', 'Erros', 'Taxa de Acerto'],
            rows: $rows,
        );
    }
}
