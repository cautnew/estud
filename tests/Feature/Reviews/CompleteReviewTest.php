<?php

use App\Actions\Reviews\CompleteReview;
use App\Enums\ReviewStatus;
use App\Models\Review;
use Illuminate\Validation\ValidationException;

test('completing a pending review marks it done and stamps completed_at', function () {
    $review = Review::factory()->create();

    (new CompleteReview)->handle($review);

    expect($review->status)->toBe(ReviewStatus::Completed);
    expect($review->completed_at)->not->toBeNull();
});

test('a completed review cannot be completed again', function () {
    $review = Review::factory()->completed()->create();

    expect(fn () => (new CompleteReview)->handle($review))
        ->toThrow(ValidationException::class);
});

test('a skipped review cannot be completed', function () {
    $review = Review::factory()->skipped()->create();

    expect(fn () => (new CompleteReview)->handle($review))
        ->toThrow(ValidationException::class);
});
