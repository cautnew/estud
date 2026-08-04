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
     * Notify a review's owner that it's due, unless it was already
     * notified (e.g. a previous run of the dispatching command).
     */
    public function __construct(
        public readonly Review $review,
    ) {}

    public function handle(): void
    {
        if ($this->review->notified_at !== null) {
            return;
        }

        $this->review->owner->notify(new ReviewDueNotification($this->review));

        $this->review->forceFill(['notified_at' => now()])->save();
    }
}
