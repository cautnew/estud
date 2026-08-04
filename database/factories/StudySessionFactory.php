<?php

namespace Database\Factories;

use App\Enums\StudySessionStatus;
use App\Models\StudySession;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudySession>
 */
class StudySessionFactory extends Factory
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
            'subject_id' => Subject::factory(),
            'topic_id' => null,
            'date' => now()->toDateString(),
            'status' => StudySessionStatus::InProgress,
            'started_at' => now(),
            'duration_seconds' => 0,
            'note' => null,
            'completed_at' => null,
        ];
    }

    public function paused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StudySessionStatus::Paused,
            'started_at' => null,
            'duration_seconds' => fake()->numberBetween(60, 3600),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StudySessionStatus::Completed,
            'started_at' => null,
            'duration_seconds' => fake()->numberBetween(300, 7200),
            'completed_at' => now(),
        ]);
    }
}
