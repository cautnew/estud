<?php

namespace Database\Factories;

use App\Enums\StudyCycleType;
use App\Models\StudyCycle;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudyCycle>
 */
class StudyCycleFactory extends Factory
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
            'name' => fake()->unique()->words(2, true),
            'type' => StudyCycleType::Manual,
        ];
    }

    public function manual(): static
    {
        return $this->state(fn (array $attributes) => ['type' => StudyCycleType::Manual]);
    }

    public function automatic(): static
    {
        return $this->state(fn (array $attributes) => ['type' => StudyCycleType::Automatic]);
    }
}
