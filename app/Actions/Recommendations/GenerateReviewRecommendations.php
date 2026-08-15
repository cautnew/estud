<?php

namespace App\Actions\Recommendations;

use App\Enums\ReviewStatus;
use App\Models\Review;
use App\Models\User;
use Carbon\CarbonInterface;

/**
 * Builds the "O que revisar hoje" and "O que está atrasado" panels from
 * the user's pending spaced-repetition reviews. Both panels share the same
 * base query (pending reviews already due), split by whether
 * `scheduled_for` is today or in the past.
 */
class GenerateReviewRecommendations
{
    /**
     * @return array{dueToday: array<int, array<string, mixed>>, overdue: array<int, array<string, mixed>>}
     */
    public function handle(User $user): array
    {
        $today = today();

        $reviews = Review::query()
            ->ownedBy($user)
            ->where('status', ReviewStatus::Pending)
            ->where('scheduled_for', '<=', $today)
            ->with(['studyPlanTopic.topic', 'studyPlanTopic.studyPlanSubject.subject'])
            ->orderBy('scheduled_for')
            ->get();

        [$overdue, $dueToday] = $reviews->partition(fn (Review $review): bool => $review->scheduled_for->lt($today));

        return [
            'dueToday' => $dueToday->map(fn (Review $review): array => $this->present($review, $today))->values()->all(),
            'overdue' => $overdue->map(fn (Review $review): array => $this->present($review, $today))->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Review $review, CarbonInterface $today): array
    {
        return [
            'id' => $review->id,
            'subject' => $review->studyPlanTopic->studyPlanSubject->subject->name,
            'topic' => $review->studyPlanTopic->topic->name,
            'scheduled_for' => $review->scheduled_for->toDateString(),
            'interval_days' => $review->interval_days,
            'days_overdue' => max((int) $review->scheduled_for->diffInDays($today), 0),
        ];
    }
}
