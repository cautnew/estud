<?php

namespace Database\Factories;

use App\Models\Goal;
use App\Models\GoalProgress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GoalProgress>
 */
class GoalProgressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'goal_id' => Goal::factory(),
            'period_start' => now()->startOfWeek()->toDateString(),
            'period_end' => now()->endOfWeek()->toDateString(),
            'current_value' => 0,
            'achieved' => false,
        ];
    }

    public function achieved(): static
    {
        return $this->state(fn (array $attributes) => ['achieved' => true]);
    }
}
