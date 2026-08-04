<?php

namespace App\Actions\QuestionLogs;

use App\Models\QuestionLog;
use App\Models\User;

class CalculateQuestionStats
{
    /**
     * Summarize a user's question-practice history: overall accuracy, a
     * per-date accuracy trend, and the average accuracy per subject.
     *
     * @return array{
     *     overall: array{total: int, correct: int, incorrect: int, accuracy_rate: float},
     *     bySubject: array<int, array{subject_id: int, subject: string, total: int, correct: int, accuracy_rate: float}>,
     *     history: array<int, array{date: string, total: int, correct: int, accuracy_rate: float}>,
     * }
     */
    public function handle(User $owner): array
    {
        $overall = QuestionLog::query()
            ->ownedBy($owner)
            ->selectRaw('coalesce(sum(total), 0) as total, coalesce(sum(correct), 0) as correct, coalesce(sum(incorrect), 0) as incorrect')
            ->first();

        $bySubject = QuestionLog::query()
            ->join('subjects', 'subjects.id', '=', 'question_logs.subject_id')
            ->where('question_logs.owner_id', $owner->id)
            ->selectRaw('subjects.id as subject_id, subjects.name as subject, sum(question_logs.total) as total, sum(question_logs.correct) as correct')
            ->groupBy('subjects.id', 'subjects.name')
            ->orderBy('subjects.name')
            ->get()
            ->map(fn ($row): array => [
                'subject_id' => (int) $row->subject_id,
                'subject' => $row->subject,
                'total' => (int) $row->total,
                'correct' => (int) $row->correct,
                'accuracy_rate' => $this->accuracyRate((int) $row->correct, (int) $row->total),
            ])
            ->all();

        $history = QuestionLog::query()
            ->ownedBy($owner)
            ->selectRaw('date, sum(total) as total, sum(correct) as correct')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn (QuestionLog $row): array => [
                'date' => $row->date->toDateString(),
                'total' => (int) $row->total,
                'correct' => (int) $row->correct,
                'accuracy_rate' => $this->accuracyRate((int) $row->correct, (int) $row->total),
            ])
            ->all();

        return [
            'overall' => [
                'total' => (int) $overall->total,
                'correct' => (int) $overall->correct,
                'incorrect' => (int) $overall->incorrect,
                'accuracy_rate' => $this->accuracyRate((int) $overall->correct, (int) $overall->total),
            ],
            'bySubject' => $bySubject,
            'history' => $history,
        ];
    }

    private function accuracyRate(int $correct, int $total): float
    {
        return $total > 0 ? round(($correct / $total) * 100, 2) : 0.0;
    }
}
