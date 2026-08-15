<?php

namespace App\Actions\Reports;

use App\Actions\Reports\Concerns\BuildsReportEnvelope;
use App\Enums\ReportType;
use App\Models\StudySession;
use App\Models\User;
use App\Reports\ReportFilters;

/**
 * "Produtividade": a daily study-activity log within the filtered period
 * — hours studied, session count, and subjects touched per day.
 *
 * Honors `subjectId`; `studyPlanId` doesn't apply, since study sessions
 * aren't tied to a specific study plan.
 */
class CalculateProductivityReport
{
    use BuildsReportEnvelope;

    /**
     * @return array{title: string, filters_description: string, summary: array<int, array{label: string, value: string}>, columns: array<int, string>, rows: array<int, array<int, string>>}
     */
    public function handle(User $user, ReportFilters $filters): array
    {
        $sessions = StudySession::query()
            ->ownedBy($user)
            ->whereDate('date', '>=', $filters->from)
            ->whereDate('date', '<=', $filters->to)
            ->when($filters->subjectId, fn ($query, int $subjectId) => $query->where('subject_id', $subjectId))
            ->with('subject')
            ->get();

        $totalSeconds = (int) $sessions->sum('duration_seconds');
        $activeDays = $sessions->pluck('date')->map(fn ($date): string => $date->toDateString())->unique()->count();

        $rows = $sessions
            ->groupBy(fn (StudySession $session): string => $session->date->toDateString())
            ->map(function ($daySessions, string $date): array {
                $seconds = (int) $daySessions->sum('duration_seconds');
                $subjects = $daySessions->pluck('subject.name')->unique()->filter()->implode(', ');

                return [
                    $this->formatDate($date),
                    $this->formatHours($seconds),
                    (string) $daySessions->count(),
                    $subjects,
                ];
            })
            ->sortKeys()
            ->values()
            ->all();

        return $this->envelope(
            title: ReportType::Productivity->label(),
            filters: $filters,
            summary: [
                ['label' => 'Horas estudadas', 'value' => $this->formatHours($totalSeconds)],
                ['label' => 'Sessões', 'value' => (string) $sessions->count()],
                ['label' => 'Dias ativos', 'value' => (string) $activeDays],
                [
                    'label' => 'Duração média por sessão',
                    'value' => $sessions->isNotEmpty()
                        ? $this->formatHours(intdiv($totalSeconds, $sessions->count()))
                        : '0min',
                ],
            ],
            columns: ['Data', 'Horas Estudadas', 'Sessões', 'Disciplinas'],
            rows: $rows,
        );
    }
}
