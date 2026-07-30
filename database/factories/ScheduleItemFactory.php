<?php

namespace Database\Factories;

use App\Models\Schedule;
use App\Models\ScheduleItem;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScheduleItem>
 */
class ScheduleItemFactory extends Factory
{
    /**
     * Default state assumes a weekly schedule. Use `onDayOfMonth()` or
     * `daily()` to configure the item for other schedule types.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'schedule_id' => Schedule::factory(),
            'subject_id' => Subject::factory(),
            'day_of_week' => fake()->numberBetween(0, 6),
            'day_of_month' => null,
            'start_time' => fake()->time('H:i'),
            'duration_minutes' => fake()->randomElement([30, 45, 60, 90, 120]),
            'priority' => 0,
        ];
    }

    /**
     * Configure the item for a monthly/quarterly schedule.
     */
    public function onDayOfMonth(int $day): static
    {
        return $this->state(fn (array $attributes) => [
            'day_of_week' => null,
            'day_of_month' => $day,
        ]);
    }

    /**
     * Configure the item for a daily schedule.
     */
    public function daily(): static
    {
        return $this->state(fn (array $attributes) => [
            'day_of_week' => null,
            'day_of_month' => null,
        ]);
    }
}
