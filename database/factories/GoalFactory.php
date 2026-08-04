<?php

namespace Database\Factories;

use App\Enums\GoalPeriod;
use App\Enums\GoalType;
use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Goal>
 */
class GoalFactory extends Factory
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
            'subject_id' => null,
            'type' => GoalType::Hours,
            'period' => GoalPeriod::Weekly,
            'target' => 10,
        ];
    }

    public function ofType(GoalType $type): static
    {
        return $this->state(fn (array $attributes) => ['type' => $type]);
    }

    public function withPeriod(GoalPeriod $period): static
    {
        return $this->state(fn (array $attributes) => ['period' => $period]);
    }
}
