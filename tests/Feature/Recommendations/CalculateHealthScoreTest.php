<?php

use App\Actions\Recommendations\CalculateHealthScore;
use App\Enums\GoalPeriod;
use App\Models\Goal;
use App\Models\GoalProgress;
use App\Models\Review;
use App\Models\Schedule;
use App\Models\ScheduleItem;
use App\Models\StudySession;
use App\Models\Subject;
use App\Models\User;

test('a user with no activity at all scores zero on every component', function () {
    $user = User::factory()->create();

    $result = app(CalculateHealthScore::class)->handle($user);

    expect($result['score'])->toBe(0);
    expect($result['label'])->toBe('Crítico');
    expect($result['components']['consistency']['score'])->toBe(0.0);
    expect($result['components']['goals']['score'])->toBe(0.0);
    expect($result['components']['reviews']['score'])->toBe(0.0);
    expect($result['components']['schedule']['score'])->toBe(0.0);
});

test('the consistency component rewards both a 14-day activity ratio and a streak', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();

    foreach (range(0, 6) as $daysAgo) {
        StudySession::factory()->completed()->create([
            'owner_id' => $user->id,
            'subject_id' => $subject->id,
            'date' => today()->subDays($daysAgo),
        ]);
    }

    $result = app(CalculateHealthScore::class)->handle($user);

    expect($result['components']['consistency']['details'])->toBe([
        'activeDays' => 7,
        'windowDays' => 14,
        'currentStreak' => 7,
    ]);
    // activityRatio = 7/14 = 0.5, streakRatio = min(7/7, 1) = 1 -> (0.5*0.5 + 1*0.5) * 30 = 22.5
    expect($result['components']['consistency']['score'])->toBe(22.5);
});

test('the goals component is the ratio of goals on track this period, and zero with no goals', function () {
    $user = User::factory()->create();

    $onTrackGoal = Goal::factory()->withPeriod(GoalPeriod::Weekly)->create(['owner_id' => $user->id]);
    [$periodStart, $periodEnd] = $onTrackGoal->currentPeriodBounds();
    GoalProgress::factory()->achieved()->create([
        'goal_id' => $onTrackGoal->id,
        'period_start' => $periodStart,
        'period_end' => $periodEnd,
    ]);

    $behindGoal = Goal::factory()->withPeriod(GoalPeriod::Weekly)->create(['owner_id' => $user->id]);
    [$behindStart, $behindEnd] = $behindGoal->currentPeriodBounds();
    GoalProgress::factory()->create([
        'goal_id' => $behindGoal->id,
        'period_start' => $behindStart,
        'period_end' => $behindEnd,
        'achieved' => false,
    ]);

    $result = app(CalculateHealthScore::class)->handle($user);

    expect($result['components']['goals']['details'])->toBe(['onTrack' => 1, 'total' => 2]);
    expect($result['components']['goals']['score'])->toBe(12.5);
});

test('the reviews component rewards on-time completion and penalizes an overdue backlog', function () {
    $user = User::factory()->create();

    Review::factory()->completed()->create(['owner_id' => $user->id, 'scheduled_for' => today()->subDays(5)]);
    Review::factory()->completed()->create(['owner_id' => $user->id, 'scheduled_for' => today()->subDays(3)]);
    Review::factory()->create(['owner_id' => $user->id, 'scheduled_for' => today()->subDays(2)]);
    Review::factory()->create(['owner_id' => $user->id, 'scheduled_for' => today()->subDays(1)]);

    $result = app(CalculateHealthScore::class)->handle($user);

    // due = 4, completed = 2 -> base = (2/4)*25 = 12.5; overdue pending = 2 -> penalty = min(2*2, 10) = 4
    expect($result['components']['reviews']['details'])->toBe(['completed' => 2, 'due' => 4, 'overdue' => 2]);
    expect($result['components']['reviews']['score'])->toBe(8.5);
});

test('the schedule component measures how many scheduled occurrences had a matching study session', function () {
    $user = User::factory()->create();
    $schedule = Schedule::factory()->daily()->create(['owner_id' => $user->id]);
    $subject = Subject::factory()->create();
    ScheduleItem::factory()->daily()->create(['schedule_id' => $schedule->id, 'subject_id' => $subject->id]);

    foreach ([0, 1, 2] as $daysAgo) {
        StudySession::factory()->completed()->create([
            'owner_id' => $user->id,
            'subject_id' => $subject->id,
            'date' => today()->subDays($daysAgo),
        ]);
    }

    $result = app(CalculateHealthScore::class)->handle($user);

    expect($result['components']['schedule']['details'])->toBe(['matched' => 3, 'occurrences' => 7]);
    expect($result['components']['schedule']['score'])->toBe(round((3 / 7) * 20, 2));
});

test('a user maxing out every component gets a 100 score labeled Excelente', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();

    // Consistency: 14/14 active days including today (streak >= target of 7).
    foreach (range(0, 13) as $daysAgo) {
        StudySession::factory()->completed()->create([
            'owner_id' => $user->id,
            'subject_id' => $subject->id,
            'date' => today()->subDays($daysAgo),
        ]);
    }

    // Goals: a single goal, on track.
    $goal = Goal::factory()->withPeriod(GoalPeriod::Weekly)->create(['owner_id' => $user->id]);
    [$periodStart, $periodEnd] = $goal->currentPeriodBounds();
    GoalProgress::factory()->achieved()->create([
        'goal_id' => $goal->id,
        'period_start' => $periodStart,
        'period_end' => $periodEnd,
    ]);

    // Reviews: a single due review, completed, no overdue backlog.
    Review::factory()->completed()->create(['owner_id' => $user->id, 'scheduled_for' => today()]);

    // Schedule: a daily item, matched every day in the 7-day window by the
    // study sessions created above.
    $schedule = Schedule::factory()->daily()->create(['owner_id' => $user->id]);
    ScheduleItem::factory()->daily()->create(['schedule_id' => $schedule->id, 'subject_id' => $subject->id]);

    $result = app(CalculateHealthScore::class)->handle($user);

    expect($result['score'])->toBe(100);
    expect($result['label'])->toBe('Excelente');
});
