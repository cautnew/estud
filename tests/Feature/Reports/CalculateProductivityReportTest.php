<?php

use App\Actions\Reports\CalculateProductivityReport;
use App\Models\StudySession;
use App\Models\Subject;
use App\Models\User;
use App\Reports\ReportFilters;

test('it summarizes hours, sessions, and active days within the filtered period', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create(['name' => 'Direito Civil']);

    StudySession::factory()->completed()->create([
        'owner_id' => $user->id,
        'subject_id' => $subject->id,
        'date' => today(),
        'duration_seconds' => 3600,
    ]);
    StudySession::factory()->completed()->create([
        'owner_id' => $user->id,
        'subject_id' => $subject->id,
        'date' => today()->subDay(),
        'duration_seconds' => 1800,
    ]);
    // Outside the filtered window -- must not be counted.
    StudySession::factory()->completed()->create([
        'owner_id' => $user->id,
        'subject_id' => $subject->id,
        'date' => today()->subDays(60),
        'duration_seconds' => 9999,
    ]);

    $filters = new ReportFilters(from: today()->subDays(6), to: today());

    $report = app(CalculateProductivityReport::class)->handle($user, $filters);

    expect($report['title'])->toBe('Produtividade');
    expect($report['summary'])->toContain(['label' => 'Sessões', 'value' => '2']);
    expect($report['summary'])->toContain(['label' => 'Dias ativos', 'value' => '2']);
    expect($report['columns'])->toBe(['Data', 'Horas Estudadas', 'Sessões', 'Disciplinas']);
    expect($report['rows'])->toHaveCount(2);
});

test('it filters by subject', function () {
    $user = User::factory()->create();
    $target = Subject::factory()->create();
    $other = Subject::factory()->create();

    StudySession::factory()->completed()->create(['owner_id' => $user->id, 'subject_id' => $target->id, 'date' => today()]);
    StudySession::factory()->completed()->create(['owner_id' => $user->id, 'subject_id' => $other->id, 'date' => today()]);

    $filters = new ReportFilters(from: today(), to: today(), subjectId: $target->id);

    $report = app(CalculateProductivityReport::class)->handle($user, $filters);

    expect($report['rows'])->toHaveCount(1);
});

test("it never leaks another user's sessions", function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    StudySession::factory()->completed()->create(['owner_id' => $other->id, 'date' => today()]);

    $filters = new ReportFilters(from: today(), to: today());

    $report = app(CalculateProductivityReport::class)->handle($user, $filters);

    expect($report['rows'])->toBe([]);
});
