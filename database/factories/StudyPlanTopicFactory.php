<?php

namespace Database\Factories;

use App\Enums\StudyPlanTopicStatus;
use App\Models\StudyPlanSubject;
use App\Models\StudyPlanTopic;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudyPlanTopic>
 */
class StudyPlanTopicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'study_plan_subject_id' => StudyPlanSubject::factory(),
            'topic_id' => Topic::factory(),
            'order' => 0,
            'status' => StudyPlanTopicStatus::Pending,
        ];
    }
}
