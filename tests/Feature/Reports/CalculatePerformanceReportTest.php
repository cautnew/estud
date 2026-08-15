<?php

use App\Actions\Reports\CalculatePerformanceReport;
use App\Models\QuestionLog;
use App\Models\Subject;
use App\Models\User;
use App\Reports\ReportFilters;

test('it aggregates accuracy by subject within the filtered period', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create(['name' => 'Direito Civil']);

    QuestionLog::factory()->create(['owner_id' => $user->id, 'subject_id' => $subject->id, 'date' => today(), 'total' => 10, 'correct' => 7, 'incorrect' => 3]);
    QuestionLog::factory()->create(['owner_id' => $user->id, 'subject_id' => $subject->id, 'date' => today(), 'total' => 10, 'correct' => 5, 'incorrect' => 5]);
    // Outside the filtered window.
    QuestionLog::factory()->create(['owner_id' => $user->id, 'subject_id' => $subject->id, 'date' => today()->subDays(30), 'total' => 100, 'correct' => 100, 'incorrect' => 0]);

    $filters = new ReportFilters(from: today()->subDays(6), to: today());

    $report = app(CalculatePerformanceReport::class)->handle($user, $filters);

    expect($report['title'])->toBe('Desempenho');
    expect($report['summary'])->toContain(['label' => 'Total de questões', 'value' => '20']);
    expect($report['summary'])->toContain(['label' => 'Acertos', 'value' => '12']);
    expect($report['summary'])->toContain(['label' => 'Taxa de acerto', 'value' => '60.0%']);
    expect($report['rows'])->toBe([['Direito Civil', '20', '12', '8', '60.0%']]);
});

test('it filters by subject', function () {
    $user = User::factory()->create();
    $target = Subject::factory()->create();
    $other = Subject::factory()->create();

    QuestionLog::factory()->create(['owner_id' => $user->id, 'subject_id' => $target->id, 'date' => today()]);
    QuestionLog::factory()->create(['owner_id' => $user->id, 'subject_id' => $other->id, 'date' => today()]);

    $filters = new ReportFilters(from: today(), to: today(), subjectId: $target->id);

    $report = app(CalculatePerformanceReport::class)->handle($user, $filters);

    expect($report['rows'])->toHaveCount(1);
});
