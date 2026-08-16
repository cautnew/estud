<?php

namespace App\Jobs;

use App\Models\Review;
use App\Notifications\ReviewDueNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifyDueReviewJob implements ShouldQueue
{
    use Queueable;

    /**
     * Notification delivery can hit transient failures (mail/queue
     * backend hiccups), so retry a few times with backoff before landing
     * in the failed_jobs table for manual/`queue:retry` attention.
     */
    public int $tries = 3;

    public int $timeout = 30;

    /**
     * Notify a review's owner that it's due, unless it was already
     * notified (e.g. a previous run of the dispatching command).
     */
    public function __construct(
        public readonly Review $review,
    ) {}

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function handle(): void
    {
        if ($this->review->notified_at !== null) {
            return;
        }

        $this->review->owner->notify(new ReviewDueNotification($this->review));

        $this->review->forceFill(['notified_at' => now()])->save();
    }
}
