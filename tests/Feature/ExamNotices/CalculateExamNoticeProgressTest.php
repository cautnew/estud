<?php

use App\Actions\ExamNotices\CalculateExamNoticeProgress;
use App\Enums\StudyPlanTopicStatus;
use App\Models\ExamNotice;
use App\Models\ExamSubject;
use App\Models\ExamTopic;
use App\Models\StudyPlan;
use App\Models\StudyPlanSubject;
use App\Models\StudyPlanTopic;
use App\Models\Topic;
use App\Models\User;

test('a notice with no topics has zero percentages', function () {
    $user = User::factory()->create();
    $notice = ExamNotice::factory()->create();

    $progress = app(CalculateExamNoticeProgress::class)->handle($notice, $user);

    expect($progress)->toBe([
        'studiedTopics' => 0,
        'totalTopics' => 0,
        'studiedPercentage' => 0.0,
        'remainingPercentage' => 0.0,
    ]);
});

test('it computes the percentage of the notice\'s topics the user has already studied', function () {
    $user = User::factory()->create();
    $notice = ExamNotice::factory()->create();
    $examSubject = ExamSubject::factory()->for($notice)->create();

    $studiedTopic = Topic::factory()->create();
    $pendingTopic = Topic::factory()->create();
    ExamTopic::factory()->for($examSubject)->create(['topic_id' => $studiedTopic->id]);
    ExamTopic::factory()->for($examSubject)->create(['topic_id' => $pendingTopic->id]);

    $plan = StudyPlan::factory()->for($user, 'owner')->create();
    $planSubject = StudyPlanSubject::factory()->for($plan)->create();
    StudyPlanTopic::factory()->for($planSubject)->create([
        'topic_id' => $studiedTopic->id,
        'status' => StudyPlanTopicStatus::Done,
    ]);

    $progress = app(CalculateExamNoticeProgress::class)->handle($notice, $user);

    expect($progress)->toBe([
        'studiedTopics' => 1,
        'totalTopics' => 2,
        'studiedPercentage' => 50.0,
        'remainingPercentage' => 50.0,
    ]);
});

test('progress from another user\'s study plans is never counted', function () {
    $user = User::factory()->create();
    $notice = ExamNotice::factory()->create();
    $examSubject = ExamSubject::factory()->for($notice)->create();
    $topic = Topic::factory()->create();
    ExamTopic::factory()->for($examSubject)->create(['topic_id' => $topic->id]);

    $otherUser = User::factory()->create();
    $plan = StudyPlan::factory()->for($otherUser, 'owner')->create();
    $planSubject = StudyPlanSubject::factory()->for($plan)->create();
    StudyPlanTopic::factory()->for($planSubject)->create([
        'topic_id' => $topic->id,
        'status' => StudyPlanTopicStatus::Done,
    ]);

    $progress = app(CalculateExamNoticeProgress::class)->handle($notice, $user);

    expect($progress['studiedTopics'])->toBe(0);
    expect($progress['studiedPercentage'])->toBe(0.0);
});

test('a topic repeated across two exam subjects is only counted once', function () {
    $user = User::factory()->create();
    $notice = ExamNotice::factory()->create();
    $firstExamSubject = ExamSubject::factory()->for($notice)->create();
    $secondExamSubject = ExamSubject::factory()->for($notice)->create();
    $sharedTopic = Topic::factory()->create();

    ExamTopic::factory()->for($firstExamSubject)->create(['topic_id' => $sharedTopic->id]);
    ExamTopic::factory()->for($secondExamSubject)->create(['topic_id' => $sharedTopic->id]);

    $progress = app(CalculateExamNoticeProgress::class)->handle($notice, $user);

    expect($progress['totalTopics'])->toBe(1);
});
