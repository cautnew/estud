<?php

use App\Jobs\NotifyDueReviewJob;
use App\Models\Review;
use Illuminate\Support\Facades\Queue;

test('it dispatches a job for every due, unnotified, pending review', function () {
    Queue::fake();

    $due = Review::factory()->scheduledFor(-1)->create();
    $dueToday = Review::factory()->scheduledFor(0)->create();
    $future = Review::factory()->scheduledFor(5)->create();
    $alreadyNotified = Review::factory()->scheduledFor(-1)->notified()->create();
    $completed = Review::factory()->scheduledFor(-1)->completed()->create();

    $this->artisan('reviews:notify-due')->assertSuccessful();

    Queue::assertPushed(NotifyDueReviewJob::class, 2);
    Queue::assertPushed(NotifyDueReviewJob::class, fn ($job) => $job->review->is($due));
    Queue::assertPushed(NotifyDueReviewJob::class, fn ($job) => $job->review->is($dueToday));
    Queue::assertNotPushed(NotifyDueReviewJob::class, fn ($job) => $job->review->is($future));
    Queue::assertNotPushed(NotifyDueReviewJob::class, fn ($job) => $job->review->is($alreadyNotified));
    Queue::assertNotPushed(NotifyDueReviewJob::class, fn ($job) => $job->review->is($completed));
});
