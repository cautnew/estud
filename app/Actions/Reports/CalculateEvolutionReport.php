<?php

namespace App\Actions\Reports;

use App\Actions\Reports\Concerns\BuildsReportEnvelope;
use App\Enums\ReportType;
use App\Enums\StudyPlanTopicStatus;
use App\Models\QuestionLog;
use App\Models\StudyPlanTopic;
use App\Models\StudySession;
use App\Models\User;
use App\Reports\ReportFilters;
use Carbon\CarbonInterface;

/**
 * "Evolução": trend of hours studied, topics completed, and questions
 * answered across the filtered period, bucketed into consecutive 7-day
 * windows starting from `filters->from` (not calendar/ISO weeks — this
 * keeps the bucketing predictable regardless of which weekday the range
 * starts on).
 *
 * Honors `subjectId` and `studyPlanId` (the latter only affects the
 * "topics completed" metric, via `StudyPlanTopic`).
 */
class CalculateEvolutionReport
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
            ->get(['date', 'duration_seconds']);

        $questionLogs = QuestionLog::query()
            ->ownedBy($user)
            ->whereDate('date', '>=', $filters->from)
            ->whereDate('date', '<=', $filters->to)
            ->when($filters->subjectId, fn ($query, int $subjectId) => $query->where('subject_id', $subjectId))
            ->get(['date', 'total']);

        $topicsQuery = StudyPlanTopic::query()
            ->whereHas('studyPlanSubject.studyPlan', fn ($query) => $query->where('owner_id', $user->id))
            ->where('status', StudyPlanTopicStatus::Done)
            ->whereNotNull('studied_at')
            ->whereBetween('studied_at', [$filters->from, $filters->to->endOfDay()]);

        if ($filters->subjectId !== null) {
            $topicsQuery->whereHas('studyPlanSubject', fn ($query) => $query->where('subject_id', $filters->subjectId));
        }

        if ($filters->studyPlanId !== null) {
            $topicsQuery->whereHas('studyPlanSubject', fn ($query) => $query->where('study_plan_id', $filters->studyPlanId));
        }

        $topics = $topicsQuery->get(['studied_at']);

        $weeks = $this->weeksBetween($filters->from, $filters->to);

        $totalSeconds = 0;
        $totalTopics = 0;
        $totalQuestions = 0;

        $rows = array_map(function (array $week) use ($sessions, $topics, $questionLogs, &$totalSeconds, &$totalTopics, &$totalQuestions): array {
            [$weekStart, $weekEnd] = $week;

            $weekSeconds = (int) $sessions
                ->filter(fn (StudySession $session): bool => $session->date->between($weekStart, $weekEnd))
                ->sum('duration_seconds');

            $weekTopics = $topics
                ->filter(fn (StudyPlanTopic $topic): bool => $topic->studied_at->between($weekStart, $weekEnd))
                ->count();

            $weekQuestions = (int) $questionLogs
                ->filter(fn (QuestionLog $log): bool => $log->date->between($weekStart, $weekEnd))
                ->sum('total');

            $totalSeconds += $weekSeconds;
            $totalTopics += $weekTopics;
            $totalQuestions += $weekQuestions;

            return [
                $weekStart->format('d/m').' a '.$weekEnd->format('d/m/Y'),
                $this->formatHours($weekSeconds),
                (string) $weekTopics,
                (string) $weekQuestions,
            ];
        }, $weeks);

        $weekCount = max(count($weeks), 1);

        return $this->envelope(
            title: ReportType::Evolution->label(),
            filters: $filters,
            summary: [
                ['label' => 'Horas estudadas no período', 'value' => $this->formatHours($totalSeconds)],
                ['label' => 'Média semanal de horas', 'value' => $this->formatHours(intdiv($totalSeconds, $weekCount))],
                ['label' => 'Tópicos concluídos', 'value' => (string) $totalTopics],
                ['label' => 'Questões respondidas', 'value' => (string) $totalQuestions],
            ],
            columns: ['Semana', 'Horas Estudadas', 'Tópicos Concluídos', 'Questões Respondidas'],
            rows: $rows,
        );
    }

    /**
     * @return array<int, array{0: CarbonInterface, 1: CarbonInterface}>
     */
    private function weeksBetween(CarbonInterface $from, CarbonInterface $to): array
    {
        $weeks = [];
        $cursor = $from;

        while ($cursor->lte($to)) {
            $weekEnd = $cursor->addDays(6);

            if ($weekEnd->gt($to)) {
                $weekEnd = $to;
            }

            $weeks[] = [$cursor, $weekEnd];
            $cursor = $cursor->addDays(7);
        }

        return $weeks;
    }
}
