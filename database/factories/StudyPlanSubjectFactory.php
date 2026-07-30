<?php

namespace Database\Factories;

use App\Models\StudyPlan;
use App\Models\StudyPlanSubject;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudyPlanSubject>
 */
class StudyPlanSubjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'study_plan_id' => StudyPlan::factory(),
            'subject_id' => Subject::factory(),
            'order' => 0,
        ];
    }
}
