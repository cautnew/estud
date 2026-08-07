<?php

namespace Database\Factories;

use App\Models\ExamNotice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamNotice>
 */
class ExamNoticeFactory extends Factory
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
            'exam_date' => null,
        ];
    }
}
