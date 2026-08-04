<?php

use App\Actions\Goals\RefreshGoalProgress;
use App\Enums\GoalPeriod;
use App\Enums\GoalType;
use App\Models\Goal;
use App\Models\GoalProgress;
use App\Models\StudySession;
use App\Models\User;
use Illuminate\Support\Carbon;

test('it creates a progress row for the current period, marking it achieved when the target is met', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->for($user, 'owner')->ofType(GoalType::Hours)->withPeriod(GoalPeriod::Weekly)->create(['target' => 2]);
    StudySession::factory()->for($user, 'owner')->completed()->create(['date' => today()->toDateString(), 'duration_seconds' => 7200]);

    app(RefreshGoalProgress::class)->handle($user);

    $progress = GoalProgress::where('goal_id', $goal->id)->sole();
    expect($progress->current_value)->toBe(2.0);
    expect($progress->achieved)->toBeTrue();
    expect($progress->period_start->toDateString())->toBe(today()->startOfWeek()->toDateString());
});

test('it marks a goal as not achieved when progress falls short of the target', function () {
    $user = User::factory()->create();
    Goal::factory()->for($user, 'owner')->ofType(GoalType::Hours)->create(['target' => 5]);
    StudySession::factory()->for($user, 'owner')->completed()->create(['date' => today()->toDateString(), 'duration_seconds' => 3600]);

    app(RefreshGoalProgress::class)->handle($user);

    expect(GoalProgress::sole()->achieved)->toBeFalse();
});

test('refreshing again updates the same period row instead of duplicating it', function () {
    $user = User::factory()->create();
    Goal::factory()->for($user, 'owner')->ofType(GoalType::Hours)->create(['target' => 5]);
    StudySession::factory()->for($user, 'owner')->completed()->create(['date' => today()->toDateString(), 'duration_seconds' => 3600]);

    app(RefreshGoalProgress::class)->handle($user);
    StudySession::factory()->for($user, 'owner')->completed()->create(['date' => today()->toDateString(), 'duration_seconds' => 3600]);
    app(RefreshGoalProgress::class)->handle($user);

    $progress = GoalProgress::sole();
    expect($progress->current_value)->toBe(2.0);
});

test('a new period gets its own progress row once the previous one has ended', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->for($user, 'owner')->ofType(GoalType::Hours)->withPeriod(GoalPeriod::Weekly)->create();

    $this->travelTo(Carbon::parse('2026-08-01'));
    StudySession::factory()->for($user, 'owner')->completed()->create(['date' => today()->toDateString(), 'duration_seconds' => 3600]);
    app(RefreshGoalProgress::class)->handle($user);

    $this->travelTo(Carbon::parse('2026-08-20'));
    StudySession::factory()->for($user, 'owner')->completed()->create(['date' => today()->toDateString(), 'duration_seconds' => 3600]);
    app(RefreshGoalProgress::class)->handle($user);

    expect(GoalProgress::where('goal_id', $goal->id)->count())->toBe(2);
});

test('data belonging to other users never leaks into a refreshed progress value', function () {
    $user = User::factory()->create();
    Goal::factory()->for($user, 'owner')->ofType(GoalType::Hours)->create(['target' => 1]);
    StudySession::factory()->completed()->create(['date' => today()->toDateString(), 'duration_seconds' => 999_999]);

    app(RefreshGoalProgress::class)->handle($user);

    expect(GoalProgress::sole()->current_value)->toBe(0.0);
});
