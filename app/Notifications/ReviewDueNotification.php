<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReviewDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Review $review,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $topic = $this->review->studyPlanTopic->topic;
        $subject = $this->review->studyPlanTopic->studyPlanSubject->subject;

        return [
            'review_id' => $this->review->id,
            'subject' => $subject->name,
            'topic' => $topic->name,
            'scheduled_for' => $this->review->scheduled_for->toDateString(),
            'interval_days' => $this->review->interval_days,
        ];
    }
}
