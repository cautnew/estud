<?php

namespace Database\Factories;

use App\Models\QuestionLog;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuestionLog>
 */
class QuestionLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $total = fake()->numberBetween(5, 30);
        $correct = fake()->numberBetween(0, $total);

        return [
            'owner_id' => User::factory(),
            'subject_id' => Subject::factory(),
            'topic_id' => null,
            'date' => now()->toDateString(),
            'total' => $total,
            'correct' => $correct,
            'incorrect' => $total - $correct,
        ];
    }
}
