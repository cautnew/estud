<?php

use App\Actions\Reports\CalculateReviewsReport;
use App\Models\Review;
use App\Models\StudyPlanSubject;
use App\Models\StudyPlanTopic;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use App\Reports\ReportFilters;

test('it counts completed, pending, skipped, and overdue reviews', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create(['name' => 'Direito Penal']);
    $topic = Topic::factory()->create(['subject_id' => $subject->id]);
    $planSubject = StudyPlanSubject::factory()->create(['subject_id' => $subject->id]);
    $planTopic = StudyPlanTopic::factory()->create(['study_plan_subject_id' => $planSubject->id, 'topic_id' => $topic->id]);

    Review::factory()->completed()->create(['owner_id' => $user->id, 'study_plan_topic_id' => $planTopic->id, 'scheduled_for' => today()->subDays(2)]);
    Review::factory()->skipped()->create(['owner_id' => $user->id, 'study_plan_topic_id' => $planTopic->id, 'scheduled_for' => today()->subDay()]);
    // Pending and overdue.
    Review::factory()->create(['owner_id' => $user->id, 'study_plan_topic_id' => $planTopic->id, 'scheduled_for' => today()->subDay()]);
    // Pending, not yet due -- still inside the filtered date range, but not overdue.
    Review::factory()->create(['owner_id' => $user->id, 'study_plan_topic_id' => $planTopic->id, 'scheduled_for' => today()]);

    $filters = new ReportFilters(from: today()->subDays(6), to: today());

    $report = app(CalculateReviewsReport::class)->handle($user, $filters);

    expect($report['title'])->toBe('Revisões');
    expect($report['summary'])->toContain(['label' => 'Total de revisões', 'value' => '4']);
    expect($report['summary'])->toContain(['label' => 'Concluídas', 'value' => '1']);
    expect($report['summary'])->toContain(['label' => 'Puladas', 'value' => '1']);
    expect($report['summary'])->toContain(['label' => 'Atrasadas', 'value' => '1']);
    expect($report['rows'])->toHaveCount(4);
});

test("it never leaks another user's reviews", function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Review::factory()->create(['owner_id' => $other->id, 'scheduled_for' => today()]);

    $filters = new ReportFilters(from: today(), to: today());

    $report = app(CalculateReviewsReport::class)->handle($user, $filters);

    expect($report['rows'])->toBe([]);
});
