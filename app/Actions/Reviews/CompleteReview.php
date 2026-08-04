<?php

namespace App\Actions\Reviews;

use App\Enums\ReviewStatus;
use App\Models\Review;
use Illuminate\Validation\ValidationException;

class CompleteReview
{
    /**
     * Mark a pending review as done.
     */
    public function handle(Review $review): Review
    {
        if ($review->status !== ReviewStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => 'Apenas revisões pendentes podem ser concluídas.',
            ]);
        }

        $review->forceFill([
            'status' => ReviewStatus::Completed,
            'completed_at' => now(),
        ])->save();

        return $review;
    }
}
