<?php

namespace Database\Factories;

use App\Models\ExamNotice;
use App\Models\ExamSubject;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamSubject>
 */
class ExamSubjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'exam_notice_id' => ExamNotice::factory(),
            'subject_id' => Subject::factory(),
            'order' => 0,
        ];
    }
}
