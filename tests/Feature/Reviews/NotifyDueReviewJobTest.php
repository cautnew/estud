<?php

use App\Jobs\NotifyDueReviewJob;
use App\Models\Review;
use App\Notifications\ReviewDueNotification;
use Illuminate\Support\Facades\Notification;

test('it notifies the review\'s owner and stamps notified_at', function () {
    Notification::fake();
    $review = Review::factory()->create();

    (new NotifyDueReviewJob($review))->handle();

    Notification::assertSentTo($review->owner, ReviewDueNotification::class);
    expect($review->fresh()->notified_at)->not->toBeNull();
});

test('it does not notify twice for an already-notified review', function () {
    Notification::fake();
    $review = Review::factory()->notified()->create();

    (new NotifyDueReviewJob($review))->handle();

    Notification::assertNothingSent();
});
