<?php

use App\Actions\Reports\CalculateGoalsReport;
use App\Enums\GoalPeriod;
use App\Models\Goal;
use App\Models\GoalProgress;
use App\Models\Subject;
use App\Models\User;
use App\Reports\ReportFilters;

test('it summarizes goal-progress periods starting within the filtered range', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create(['name' => 'Direito Civil']);
    $goal = Goal::factory()->withPeriod(GoalPeriod::Weekly)->create(['owner_id' => $user->id, 'subject_id' => $subject->id, 'target' => 10]);

    GoalProgress::factory()->achieved()->create([
        'goal_id' => $goal->id,
        'period_start' => today()->subDays(3),
        'period_end' => today()->addDays(3),
        'current_value' => 12,
    ]);
    GoalProgress::factory()->create([
        'goal_id' => $goal->id,
        'period_start' => today()->subDays(10),
        'period_end' => today()->subDays(4),
        'current_value' => 4,
        'achieved' => false,
    ]);
    // Outside the filtered window.
    GoalProgress::factory()->achieved()->create([
        'goal_id' => $goal->id,
        'period_start' => today()->subDays(60),
        'period_end' => today()->subDays(54),
    ]);

    $filters = new ReportFilters(from: today()->subDays(9), to: today());

    $report = app(CalculateGoalsReport::class)->handle($user, $filters);

    expect($report['title'])->toBe('Metas');
    expect($report['summary'])->toContain(['label' => 'Períodos avaliados', 'value' => '1']);
    expect($report['summary'])->toContain(['label' => 'Períodos com meta atingida', 'value' => '1']);
    expect($report['rows'])->toHaveCount(1);
});

test("it never leaks another user's goals", function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $goal = Goal::factory()->create(['owner_id' => $other->id]);

    GoalProgress::factory()->create(['goal_id' => $goal->id, 'period_start' => today(), 'period_end' => today()]);

    $filters = new ReportFilters(from: today(), to: today());

    $report = app(CalculateGoalsReport::class)->handle($user, $filters);

    expect($report['rows'])->toBe([]);
});
