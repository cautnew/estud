<?php

namespace Database\Factories;

use App\Enums\ReviewStatus;
use App\Models\Review;
use App\Models\StudyPlanTopic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'study_plan_topic_id' => StudyPlanTopic::factory(),
            'scheduled_for' => now()->toDateString(),
            'interval_days' => 1,
            'status' => ReviewStatus::Pending,
            'completed_at' => null,
            'notified_at' => null,
        ];
    }

    public function scheduledFor(int $daysFromNow): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_for' => now()->addDays($daysFromNow)->toDateString(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReviewStatus::Completed,
            'completed_at' => now(),
        ]);
    }

    public function skipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReviewStatus::Skipped,
        ]);
    }

    public function notified(): static
    {
        return $this->state(fn (array $attributes) => [
            'notified_at' => now(),
        ]);
    }
}
