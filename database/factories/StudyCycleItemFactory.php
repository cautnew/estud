<?php

namespace Database\Factories;

use App\Models\StudyCycle;
use App\Models\StudyCycleItem;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudyCycleItem>
 */
class StudyCycleItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'study_cycle_id' => StudyCycle::factory(),
            'subject_id' => Subject::factory(),
            'difficulty' => fake()->numberBetween(1, 5),
            'importance' => fake()->numberBetween(1, 5),
            'progress' => fake()->numberBetween(0, 100),
            'last_studied_at' => null,
            'order' => 0,
        ];
    }

    /**
     * Mark the item as studied a given number of days ago.
     */
    public function studiedDaysAgo(int $days): static
    {
        return $this->state(fn (array $attributes) => [
            'last_studied_at' => now()->subDays($days),
        ]);
    }
}
