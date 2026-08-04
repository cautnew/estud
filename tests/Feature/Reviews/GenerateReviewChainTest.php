<?php

use App\Actions\Reviews\CompleteReview;
use App\Actions\Reviews\GenerateReviewChain;
use App\Enums\ReviewStatus;
use App\Models\Review;
use App\Models\ReviewPreference;
use App\Models\StudyPlanTopic;
use App\Models\User;

test('generating a chain schedules the default D+1/D+7/D+15/D+30/D+60/D+90 reviews from the studied date', function () {
    $user = User::factory()->create();
    $studyPlanTopic = StudyPlanTopic::factory()->create(['studied_at' => '2026-08-01']);

    $reviews = (new GenerateReviewChain)->handle($studyPlanTopic, $user);

    expect($reviews)->toHaveCount(6);
    expect($reviews->pluck('interval_days')->all())->toBe([1, 7, 15, 30, 60, 90]);
    expect($reviews->pluck('scheduled_for')->map->toDateString()->all())->toBe([
        '2026-08-02', '2026-08-08', '2026-08-16', '2026-08-31', '2026-09-30', '2026-10-30',
    ]);
    expect($reviews->every(fn (Review $review) => $review->owner_id === $user->id))->toBeTrue();
    expect($reviews->every(fn (Review $review) => $review->status === ReviewStatus::Pending))->toBeTrue();
});

test('a topic without a studied date is anchored to today', function () {
    $user = User::factory()->create();
    $studyPlanTopic = StudyPlanTopic::factory()->create(['studied_at' => null]);

    $reviews = (new GenerateReviewChain)->handle($studyPlanTopic, $user);

    expect($reviews->first()->scheduled_for->toDateString())->toBe(today()->addDay()->toDateString());
});

test('a user\'s custom intervals are used instead of the default pattern', function () {
    $user = User::factory()->create();
    ReviewPreference::factory()->for($user, 'owner')->create(['intervals' => [2, 5]]);
    $studyPlanTopic = StudyPlanTopic::factory()->create(['studied_at' => '2026-08-01']);

    $reviews = (new GenerateReviewChain)->handle($studyPlanTopic, $user);

    expect($reviews->pluck('interval_days')->all())->toBe([2, 5]);
});

test('regenerating a chain replaces pending reviews but keeps completed/skipped history', function () {
    $user = User::factory()->create();
    $studyPlanTopic = StudyPlanTopic::factory()->create(['studied_at' => '2026-08-01']);

    (new GenerateReviewChain)->handle($studyPlanTopic, $user);
    $completed = $studyPlanTopic->reviews()->first();
    (new CompleteReview)->handle($completed);

    (new GenerateReviewChain)->handle($studyPlanTopic, $user);

    expect(Review::find($completed->id))->not->toBeNull();
    expect($studyPlanTopic->reviews()->where('status', ReviewStatus::Pending)->count())->toBe(6);
    expect($studyPlanTopic->reviews()->count())->toBe(7);
});
