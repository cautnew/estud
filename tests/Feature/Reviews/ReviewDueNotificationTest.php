<?php

use App\Models\Review;
use App\Notifications\ReviewDueNotification;

test('it builds a database notification with the review\'s subject, topic, and due date', function () {
    $review = Review::factory()->create(['scheduled_for' => '2026-08-15', 'interval_days' => 7]);
    $review->load('studyPlanTopic.topic', 'studyPlanTopic.studyPlanSubject.subject');

    $notification = new ReviewDueNotification($review);

    expect($notification->via($review->owner))->toBe(['database']);

    $data = $notification->toDatabase($review->owner);

    expect($data)->toMatchArray([
        'review_id' => $review->id,
        'subject' => $review->studyPlanTopic->studyPlanSubject->subject->name,
        'topic' => $review->studyPlanTopic->topic->name,
        'scheduled_for' => '2026-08-15',
        'interval_days' => 7,
    ]);
});
