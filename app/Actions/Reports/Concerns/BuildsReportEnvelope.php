<?php

namespace App\Actions\Reports\Concerns;

use App\Models\StudyPlan;
use App\Models\Subject;
use App\Reports\ReportFilters;
use Illuminate\Support\Carbon;

/**
 * Shared plumbing for every report action: building the uniform output
 * envelope (title, filter summary, headline metrics, and a data table)
 * that both the PDF view (resources/views/reports/report.blade.php) and
 * the Excel export (App\Exports\ReportExport) render identically, plus
 * common value formatting.
 */
trait BuildsReportEnvelope
{
    /**
     * @param  array<int, array{label: string, value: string}>  $summary
     * @param  array<int, string>  $columns
     * @param  array<int, array<int, string>>  $rows
     * @return array{title: string, filters_description: string, summary: array<int, array{label: string, value: string}>, columns: array<int, string>, rows: array<int, array<int, string>>}
     */
    protected function envelope(string $title, ReportFilters $filters, array $summary, array $columns, array $rows): array
    {
        return [
            'title' => $title,
            'filters_description' => $this->describeFilters($filters),
            'summary' => $summary,
            'columns' => $columns,
            'rows' => $rows,
        ];
    }

    protected function describeFilters(ReportFilters $filters): string
    {
        $parts = [
            sprintf('Período: %s a %s', $filters->from->format('d/m/Y'), $filters->to->format('d/m/Y')),
        ];

        if ($filters->subjectId !== null) {
            $subject = Subject::find($filters->subjectId);

            if ($subject !== null) {
                $parts[] = 'Disciplina: '.$subject->name;
            }
        }

        if ($filters->studyPlanId !== null) {
            $plan = StudyPlan::find($filters->studyPlanId);

            if ($plan !== null) {
                $parts[] = 'Plano: '.$plan->name;
            }
        }

        return implode(' · ', $parts);
    }

    protected function formatHours(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        return $hours > 0 ? sprintf('%dh%02dmin', $hours, $minutes) : sprintf('%dmin', $minutes);
    }

    protected function formatPercentage(float $value): string
    {
        return number_format($value, 1).'%';
    }

    protected function formatDate(string $date): string
    {
        return Carbon::parse($date)->format('d/m/Y');
    }
}
