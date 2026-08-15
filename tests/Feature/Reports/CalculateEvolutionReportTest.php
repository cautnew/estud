<?php

use App\Actions\Reports\CalculateEvolutionReport;
use App\Enums\StudyPlanTopicStatus;
use App\Models\QuestionLog;
use App\Models\StudyPlan;
use App\Models\StudyPlanSubject;
use App\Models\StudyPlanTopic;
use App\Models\StudySession;
use App\Models\Subject;
use App\Models\User;
use App\Reports\ReportFilters;

test('it buckets hours, topics completed, and questions into 7-day windows', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();
    $plan = StudyPlan::factory()->create(['owner_id' => $user->id]);
    $planSubject = StudyPlanSubject::factory()->create(['study_plan_id' => $plan->id, 'subject_id' => $subject->id]);

    $from = today()->subDays(13);
    $to = today();

    StudySession::factory()->completed()->create([
        'owner_id' => $user->id,
        'subject_id' => $subject->id,
        'date' => $from,
        'duration_seconds' => 3600,
    ]);
    StudySession::factory()->completed()->create([
        'owner_id' => $user->id,
        'subject_id' => $subject->id,
        'date' => $to,
        'duration_seconds' => 1800,
    ]);

    QuestionLog::factory()->create(['owner_id' => $user->id, 'subject_id' => $subject->id, 'date' => $to, 'total' => 10]);

    StudyPlanTopic::factory()->create([
        'study_plan_subject_id' => $planSubject->id,
        'status' => StudyPlanTopicStatus::Done,
        'studied_at' => $to,
    ]);

    $report = app(CalculateEvolutionReport::class)->handle($user, new ReportFilters(from: $from, to: $to));

    expect($report['title'])->toBe('Evolução');
    // 14-day window -> 2 buckets of 7 days each.
    expect($report['rows'])->toHaveCount(2);
    expect($report['summary'])->toContain(['label' => 'Tópicos concluídos', 'value' => '1']);
    expect($report['summary'])->toContain(['label' => 'Questões respondidas', 'value' => '10']);
});

test('it filters completed topics by study plan', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create();

    $ownPlan = StudyPlan::factory()->create(['owner_id' => $user->id]);
    $ownPlanSubject = StudyPlanSubject::factory()->create(['study_plan_id' => $ownPlan->id, 'subject_id' => $subject->id]);

    $otherPlan = StudyPlan::factory()->create(['owner_id' => $user->id]);
    $otherPlanSubject = StudyPlanSubject::factory()->create(['study_plan_id' => $otherPlan->id, 'subject_id' => $subject->id]);

    StudyPlanTopic::factory()->create([
        'study_plan_subject_id' => $ownPlanSubject->id,
        'status' => StudyPlanTopicStatus::Done,
        'studied_at' => today(),
    ]);
    StudyPlanTopic::factory()->create([
        'study_plan_subject_id' => $otherPlanSubject->id,
        'status' => StudyPlanTopicStatus::Done,
        'studied_at' => today(),
    ]);

    $filters = new ReportFilters(from: today(), to: today(), studyPlanId: $ownPlan->id);

    $report = app(CalculateEvolutionReport::class)->handle($user, $filters);

    expect($report['summary'])->toContain(['label' => 'Tópicos concluídos', 'value' => '1']);
});
