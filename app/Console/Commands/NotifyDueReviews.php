<?php

namespace App\Console\Commands;

use App\Enums\ReviewStatus;
use App\Jobs\NotifyDueReviewJob;
use App\Models\Review;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('reviews:notify-due')]
#[Description('Dispatch notifications for spaced-repetition reviews that are due today or overdue')]
class NotifyDueReviews extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $due = Review::query()
            ->where('status', ReviewStatus::Pending)
            ->whereNull('notified_at')
            ->whereDate('scheduled_for', '<=', today())
            ->get();

        $due->each(fn (Review $review) => NotifyDueReviewJob::dispatch($review));

        $this->info("Dispatched {$due->count()} review notification(s).");

        return self::SUCCESS;
    }
}
