<?php

use App\Actions\Recommendations\GenerateReviewRecommendations;
use App\Models\Review;
use App\Models\StudyPlanSubject;
use App\Models\StudyPlanTopic;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;

test('it splits pending due reviews into due-today and overdue buckets', function () {
    $user = User::factory()->create();
    $subject = Subject::factory()->create(['name' => 'Direito Civil']);
    $topic = Topic::factory()->create(['subject_id' => $subject->id, 'name' => 'Posse']);
    $planSubject = StudyPlanSubject::factory()->create(['subject_id' => $subject->id]);
    $planTopic = StudyPlanTopic::factory()->create(['study_plan_subject_id' => $planSubject->id, 'topic_id' => $topic->id]);

    $dueToday = Review::factory()->create([
        'owner_id' => $user->id,
        'study_plan_topic_id' => $planTopic->id,
        'scheduled_for' => today(),
    ]);
    $overdue = Review::factory()->create([
        'owner_id' => $user->id,
        'study_plan_topic_id' => $planTopic->id,
        'scheduled_for' => today()->subDays(3),
    ]);
    // Not due yet -- must not appear in either bucket.
    Review::factory()->create([
        'owner_id' => $user->id,
        'study_plan_topic_id' => $planTopic->id,
        'scheduled_for' => today()->addDays(2),
    ]);
    // Already completed -- must not appear even though it's "due".
    Review::factory()->completed()->create([
        'owner_id' => $user->id,
        'study_plan_topic_id' => $planTopic->id,
        'scheduled_for' => today()->subDay(),
    ]);

    $result = app(GenerateReviewRecommendations::class)->handle($user);

    expect($result['dueToday'])->toHaveCount(1);
    expect($result['dueToday'][0]['id'])->toBe($dueToday->id);
    expect($result['dueToday'][0]['subject'])->toBe('Direito Civil');
    expect($result['dueToday'][0]['topic'])->toBe('Posse');
    expect($result['dueToday'][0]['days_overdue'])->toBe(0);

    expect($result['overdue'])->toHaveCount(1);
    expect($result['overdue'][0]['id'])->toBe($overdue->id);
    expect($result['overdue'][0]['days_overdue'])->toBe(3);
});

test("it never leaks another user's reviews", function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Review::factory()->create(['owner_id' => $other->id, 'scheduled_for' => today()]);

    $result = app(GenerateReviewRecommendations::class)->handle($user);

    expect($result['dueToday'])->toBe([]);
    expect($result['overdue'])->toBe([]);
});
