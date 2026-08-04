<?php

namespace App\Actions\Reviews;

use App\Enums\ReviewStatus;
use App\Models\Review;
use App\Models\ReviewPreference;
use App\Models\StudyPlanTopic;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GenerateReviewChain
{
    /**
     * Schedule a fresh chain of spaced-repetition reviews for a topic that
     * was just studied, using the owner's custom intervals if configured,
     * or the default D+1/D+7/D+15/D+30/D+60/D+90 pattern otherwise. Any
     * reviews still pending from a previous chain for this topic are
     * replaced; completed/skipped history is left untouched.
     *
     * @return Collection<int, Review>
     */
    public function handle(StudyPlanTopic $studyPlanTopic, User $owner): Collection
    {
        $anchor = $studyPlanTopic->studied_at?->copy()->startOfDay() ?? today();

        $intervals = ReviewPreference::query()->ownedBy($owner)->first()?->intervals
            ?? config('reviews.default_intervals');

        return DB::transaction(function () use ($studyPlanTopic, $owner, $anchor, $intervals) {
            $studyPlanTopic->reviews()->where('status', ReviewStatus::Pending)->delete();

            return collect($intervals)->map(function (int $days) use ($studyPlanTopic, $owner, $anchor) {
                $review = new Review([
                    'study_plan_topic_id' => $studyPlanTopic->id,
                    'scheduled_for' => $anchor->copy()->addDays($days),
                    'interval_days' => $days,
                ]);

                $review->forceFill([
                    'owner_id' => $owner->id,
                    'status' => ReviewStatus::Pending,
                ])->save();

                return $review;
            });
        });
    }
}
