<?php

use App\Actions\Reviews\SkipReview;
use App\Enums\ReviewStatus;
use App\Models\Review;
use Illuminate\Validation\ValidationException;

test('skipping a pending review marks it skipped', function () {
    $review = Review::factory()->create();

    (new SkipReview)->handle($review);

    expect($review->status)->toBe(ReviewStatus::Skipped);
});

test('a completed review cannot be skipped', function () {
    $review = Review::factory()->completed()->create();

    expect(fn () => (new SkipReview)->handle($review))
        ->toThrow(ValidationException::class);
});
