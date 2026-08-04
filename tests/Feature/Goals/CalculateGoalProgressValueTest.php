<?php

use App\Actions\Goals\CalculateGoalProgressValue;
use App\Enums\GoalType;
use App\Enums\StudyPlanTopicStatus;
use App\Models\Goal;
use App\Models\QuestionLog;
use App\Models\Review;
use App\Models\StudyPlan;
use App\Models\StudyPlanSubject;
use App\Models\StudyPlanTopic;
use App\Models\StudySession;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-08-15 12:00:00'));
    $this->start = today()->startOfWeek();
    $this->end = today()->endOfWeek();
});

test('hours: sums study session duration within the period', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->for($user, 'owner')->ofType(GoalType::Hours)->create();

    StudySession::factory()->for($user, 'owner')->completed()->create(['date' => $this->start->toDateString(), 'duration_seconds' => 3600]);
    StudySession::factory()->for($user, 'owner')->completed()->create(['date' => $this->start->copy()->subDay()->toDateString(), 'duration_seconds' => 9999]);

    $value = (new CalculateGoalProgressValue)->handle($goal, $this->start, $this->end);

    expect($value)->toBe(1.0);
});

test('hours: scoped to a single subject when the goal targets one', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    $otherSubject = Subject::factory()->create();
    $goal = Goal::factory()->for($user, 'owner')->for($subject)->ofType(GoalType::Hours)->create();

    StudySession::factory()->for($user, 'owner')->for($subject)->completed()->create(['date' => $this->start->toDateString(), 'duration_seconds' => 3600]);
    StudySession::factory()->for($user, 'owner')->for($otherSubject)->completed()->create(['date' => $this->start->toDateString(), 'duration_seconds' => 3600]);

    $value = (new CalculateGoalProgressValue)->handle($goal, $this->start, $this->end);

    expect($value)->toBe(1.0);
});

test('topics: counts topics marked done within the period', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->for($user, 'owner')->ofType(GoalType::Topics)->create();
    $plan = StudyPlan::factory()->for($user, 'owner')->create();
    $planSubject = StudyPlanSubject::factory()->for($plan)->create();

    StudyPlanTopic::factory()->for($planSubject)->create(['status' => StudyPlanTopicStatus::Done, 'studied_at' => $this->start]);
    StudyPlanTopic::factory()->for($planSubject)->create(['status' => StudyPlanTopicStatus::Done, 'studied_at' => $this->start->copy()->subWeek()]);
    StudyPlanTopic::factory()->for($planSubject)->create(['status' => StudyPlanTopicStatus::Pending]);

    $value = (new CalculateGoalProgressValue)->handle($goal, $this->start, $this->end);

    expect($value)->toBe(1.0);
});

test('subjects: counts a subject only once it becomes fully done within the period', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->for($user, 'owner')->ofType(GoalType::Subjects)->create();
    $plan = StudyPlan::factory()->for($user, 'owner')->create();
    $planSubject = StudyPlanSubject::factory()->for($plan)->create();

    StudyPlanTopic::factory()->for($planSubject)->create(['status' => StudyPlanTopicStatus::Done, 'studied_at' => $this->start->copy()->subWeek()]);
    StudyPlanTopic::factory()->for($planSubject)->create(['status' => StudyPlanTopicStatus::Done, 'studied_at' => $this->start]);

    $value = (new CalculateGoalProgressValue)->handle($goal, $this->start, $this->end);

    expect($value)->toBe(1.0);
});

test('reviews: counts reviews completed within the period', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->for($user, 'owner')->ofType(GoalType::Reviews)->create();

    Review::factory()->for($user, 'owner')->completed()->create(['completed_at' => $this->start]);
    Review::factory()->for($user, 'owner')->completed()->create(['completed_at' => $this->start->copy()->subWeek()]);

    $value = (new CalculateGoalProgressValue)->handle($goal, $this->start, $this->end);

    expect($value)->toBe(1.0);
});

test('questions: sums resolved questions within the period', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->for($user, 'owner')->ofType(GoalType::Questions)->create();

    QuestionLog::factory()->for($user, 'owner')->create(['date' => $this->start->toDateString(), 'total' => 10, 'correct' => 5, 'incorrect' => 5]);
    QuestionLog::factory()->for($user, 'owner')->create(['date' => $this->start->copy()->subWeek()->toDateString(), 'total' => 20, 'correct' => 20, 'incorrect' => 0]);

    $value = (new CalculateGoalProgressValue)->handle($goal, $this->start, $this->end);

    expect($value)->toBe(10.0);
});

test('data from other users never contributes to a goal\'s progress', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->for($user, 'owner')->ofType(GoalType::Hours)->create();

    StudySession::factory()->completed()->create(['date' => $this->start->toDateString(), 'duration_seconds' => 3600]);

    $value = (new CalculateGoalProgressValue)->handle($goal, $this->start, $this->end);

    expect($value)->toBe(0.0);
});
