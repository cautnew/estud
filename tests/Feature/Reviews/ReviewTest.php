<?php

use App\Enums\ReviewStatus;
use App\Models\Review;
use App\Models\StudyPlanTopic;
use App\Models\User;

test('guests cannot access the review agenda', function () {
    $response = $this->get(route('reviews.index'));

    $response->assertRedirect(route('login'));
});

test('a user only sees their own pending reviews in the agenda', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $own = Review::factory()->for($user, 'owner')->create();
    Review::factory()->create();
    Review::factory()->for($user, 'owner')->completed()->create();

    $response = $this->actingAs($user)->get(route('reviews.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('reviews', 1)
        ->where('reviews.0.id', $own->id)
    );
});

test('a user can complete their own review', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $review = Review::factory()->for($user, 'owner')->create();

    $response = $this->actingAs($user)->patch(route('reviews.complete', $review));

    $response->assertSessionHasNoErrors();
    expect($review->refresh()->status)->toBe(ReviewStatus::Completed);
});

test('a user can skip their own review', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $review = Review::factory()->for($user, 'owner')->create();

    $response = $this->actingAs($user)->patch(route('reviews.skip', $review));

    $response->assertSessionHasNoErrors();
    expect($review->refresh()->status)->toBe(ReviewStatus::Skipped);
});

test('a user cannot complete or skip another user\'s review', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $review = Review::factory()->create();

    $this->actingAs($user)->patch(route('reviews.complete', $review))->assertForbidden();
    $this->actingAs($user)->patch(route('reviews.skip', $review))->assertForbidden();
});

test('marking a study plan topic as done schedules its review chain', function () {
    $user = User::factory()->create();
    $user->assignRole('User');
    $studyPlanTopic = StudyPlanTopic::factory()->create();
    $studyPlanTopic->studyPlanSubject->studyPlan()->update(['owner_id' => $user->id]);

    $response = $this->actingAs($user)->put(route('plan-topics.update', $studyPlanTopic), [
        'status' => 'done',
        'studied_at' => '2026-08-01',
    ]);

    $response->assertSessionHasNoErrors();
    expect(Review::where('study_plan_topic_id', $studyPlanTopic->id)->count())->toBe(6);
    expect(Review::where('study_plan_topic_id', $studyPlanTopic->id)->first()->owner_id)->toBe($user->id);
});
