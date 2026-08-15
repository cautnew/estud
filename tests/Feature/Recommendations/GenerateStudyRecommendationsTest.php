<?php

use App\Actions\Recommendations\GenerateStudyRecommendations;
use App\Enums\StudyPlanTopicStatus;
use App\Models\Schedule;
use App\Models\ScheduleItem;
use App\Models\StudyCycle;
use App\Models\StudyCycleItem;
use App\Models\StudyPlan;
use App\Models\StudyPlanSubject;
use App\Models\StudyPlanTopic;
use App\Models\Subject;
use App\Models\User;

test('it lists schedule items occurring today, ordered by priority', function () {
    $user = User::factory()->create();
    $schedule = Schedule::factory()->weekly()->create(['owner_id' => $user->id]);
    $todaySubject = Subject::factory()->create(['name' => 'Direito Constitucional']);
    $otherDaySubject = Subject::factory()->create();

    ScheduleItem::factory()->create([
        'schedule_id' => $schedule->id,
        'subject_id' => $todaySubject->id,
        'day_of_week' => today()->dayOfWeek,
        'priority' => 1,
    ]);
    ScheduleItem::factory()->create([
        'schedule_id' => $schedule->id,
        'subject_id' => $otherDaySubject->id,
        'day_of_week' => (today()->dayOfWeek + 1) % 7,
    ]);

    $result = app(GenerateStudyRecommendations::class)->handle($user);

    expect($result)->toHaveCount(1);
    expect($result[0]['source'])->toBe('schedule');
    expect($result[0]['subject'])->toBe('Direito Constitucional');
});

test('it lists the top study-cycle items not yet at 100% progress, ordered by priority score', function () {
    $user = User::factory()->create();
    $cycle = StudyCycle::factory()->create(['owner_id' => $user->id]);

    $high = Subject::factory()->create(['name' => 'Alta prioridade']);
    $low = Subject::factory()->create(['name' => 'Baixa prioridade']);
    $done = Subject::factory()->create();

    StudyCycleItem::factory()->create(['study_cycle_id' => $cycle->id, 'subject_id' => $high->id, 'priority_score' => 90, 'progress' => 40]);
    StudyCycleItem::factory()->create(['study_cycle_id' => $cycle->id, 'subject_id' => $low->id, 'priority_score' => 10, 'progress' => 40]);
    StudyCycleItem::factory()->create(['study_cycle_id' => $cycle->id, 'subject_id' => $done->id, 'priority_score' => 99, 'progress' => 100]);

    $result = app(GenerateStudyRecommendations::class)->handle($user);

    expect($result)->toHaveCount(2);
    expect($result[0]['subject'])->toBe('Alta prioridade');
    expect($result[1]['subject'])->toBe('Baixa prioridade');
});

test('it lists pending/in-progress study plan topics as a fallback, in-progress first', function () {
    $user = User::factory()->create();
    $plan = StudyPlan::factory()->create(['owner_id' => $user->id]);
    $planSubject = StudyPlanSubject::factory()->create([
        'study_plan_id' => $plan->id,
        'subject_id' => Subject::factory()->create()->id,
    ]);

    StudyPlanTopic::factory()->create([
        'study_plan_subject_id' => $planSubject->id,
        'status' => StudyPlanTopicStatus::Pending,
        'order' => 1,
    ]);
    $inProgress = StudyPlanTopic::factory()->create([
        'study_plan_subject_id' => $planSubject->id,
        'status' => StudyPlanTopicStatus::InProgress,
        'order' => 2,
    ]);
    StudyPlanTopic::factory()->create([
        'study_plan_subject_id' => $planSubject->id,
        'status' => StudyPlanTopicStatus::Done,
        'order' => 0,
    ]);

    $result = app(GenerateStudyRecommendations::class)->handle($user);

    expect($result)->toHaveCount(2);
    expect($result[0]['source'])->toBe('plan');
    expect($result[0]['topic_id'])->toBe($inProgress->topic_id);
});

test("it never leaks another user's schedule, cycle, or plan items", function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $schedule = Schedule::factory()->daily()->create(['owner_id' => $other->id]);
    ScheduleItem::factory()->daily()->create(['schedule_id' => $schedule->id]);

    $cycle = StudyCycle::factory()->create(['owner_id' => $other->id]);
    StudyCycleItem::factory()->create(['study_cycle_id' => $cycle->id, 'progress' => 0]);

    $plan = StudyPlan::factory()->create(['owner_id' => $other->id]);
    $planSubject = StudyPlanSubject::factory()->create(['study_plan_id' => $plan->id]);
    StudyPlanTopic::factory()->create(['study_plan_subject_id' => $planSubject->id]);

    $result = app(GenerateStudyRecommendations::class)->handle($user);

    expect($result)->toBe([]);
});
