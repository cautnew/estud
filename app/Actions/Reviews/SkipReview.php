<?php

namespace App\Actions\Reviews;

use App\Enums\ReviewStatus;
use App\Models\Review;
use Illuminate\Validation\ValidationException;

class SkipReview
{
    /**
     * Dismiss a pending review without marking it as studied.
     */
    public function handle(Review $review): Review
    {
        if ($review->status !== ReviewStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => 'Apenas revisões pendentes podem ser puladas.',
            ]);
        }

        $review->forceFill([
            'status' => ReviewStatus::Skipped,
        ])->save();

        return $review;
    }
}
