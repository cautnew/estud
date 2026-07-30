<?php

namespace Database\Factories;

use App\Models\StudyPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudyPlan>
 */
class StudyPlanFactory extends Factory
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
            'name' => fake()->unique()->words(3, true),
            'exam_board' => fake()->company(),
            'goal' => fake()->optional()->sentence(),
            'deadline' => fake()->optional()->dateTimeBetween('+1 month', '+1 year'),
            'exam_date' => fake()->optional()->dateTimeBetween('+1 month', '+1 year'),
            'is_public' => false,
        ];
    }

    /**
     * Indicate that the plan is published to the public library.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }
}
