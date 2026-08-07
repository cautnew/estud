<?php

namespace Database\Factories;

use App\Models\ExamSubject;
use App\Models\ExamTopic;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamTopic>
 */
class ExamTopicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'exam_subject_id' => ExamSubject::factory(),
            'topic_id' => Topic::factory(),
            'order' => 0,
        ];
    }
}
