<?php

namespace App\Actions\StudyPlans;

use App\Enums\StudyPlanTopicStatus;
use App\Models\StudyPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DuplicateStudyPlan
{
    /**
     * Copy a plan (owned by the caller, or public) into a brand new plan
     * owned by the acting user, with every topic's progress reset.
     */
    public function handle(StudyPlan $plan, User $actor): StudyPlan
    {
        return DB::transaction(function () use ($plan, $actor) {
            $copy = StudyPlan::create([
                'owner_id' => $actor->id,
                'name' => $plan->name.' (cópia)',
                'exam_board' => $plan->exam_board,
                'goal' => $plan->goal,
                'deadline' => $plan->deadline,
                'exam_date' => $plan->exam_date,
                'is_public' => false,
            ]);

            foreach ($plan->studyPlanSubjects()->with('studyPlanTopics')->get() as $subject) {
                $newSubject = $copy->studyPlanSubjects()->create([
                    'subject_id' => $subject->subject_id,
                    'order' => $subject->order,
                ]);

                foreach ($subject->studyPlanTopics as $topic) {
                    $newSubject->studyPlanTopics()->create([
                        'topic_id' => $topic->topic_id,
                        'order' => $topic->order,
                        'notes' => $topic->notes,
                        'status' => StudyPlanTopicStatus::Pending,
                        'studied_at' => null,
                    ]);
                }
            }

            return $copy;
        });
    }
}
