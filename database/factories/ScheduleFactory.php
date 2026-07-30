<?php

namespace Database\Factories;

use App\Enums\ScheduleType;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
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
            'type' => ScheduleType::Weekly,
        ];
    }

    public function daily(): static
    {
        return $this->state(fn (array $attributes) => ['type' => ScheduleType::Daily]);
    }

    public function weekly(): static
    {
        return $this->state(fn (array $attributes) => ['type' => ScheduleType::Weekly]);
    }

    public function biweekly(): static
    {
        return $this->state(fn (array $attributes) => ['type' => ScheduleType::Biweekly]);
    }

    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => ['type' => ScheduleType::Monthly]);
    }

    public function quarterly(): static
    {
        return $this->state(fn (array $attributes) => ['type' => ScheduleType::Quarterly]);
    }
}
