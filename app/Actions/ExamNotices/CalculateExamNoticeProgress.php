<?php

namespace App\Actions\ExamNotices;

use App\Enums\StudyPlanTopicStatus;
use App\Models\ExamNotice;
use App\Models\ExamTopic;
use App\Models\StudyPlanTopic;
use App\Models\User;

class CalculateExamNoticeProgress
{
    /**
     * How much of this notice's distinct topics the given user has already
     * marked as done in any of their study plans.
     *
     * @return array{studiedTopics: int, totalTopics: int, studiedPercentage: float, remainingPercentage: float}
     */
    public function handle(ExamNotice $notice, User $user): array
    {
        $topicIds = ExamTopic::query()
            ->whereHas('examSubject', fn ($query) => $query->where('exam_notice_id', $notice->id))
            ->pluck('topic_id')
            ->unique();

        $totalTopics = $topicIds->count();

        if ($totalTopics === 0) {
            return [
                'studiedTopics' => 0,
                'totalTopics' => 0,
                'studiedPercentage' => 0.0,
                'remainingPercentage' => 0.0,
            ];
        }

        $studiedTopics = StudyPlanTopic::query()
            ->whereIn('topic_id', $topicIds)
            ->where('status', StudyPlanTopicStatus::Done)
            ->whereHas('studyPlanSubject.studyPlan', fn ($query) => $query->where('owner_id', $user->id))
            ->distinct()
            ->count('topic_id');

        $studiedPercentage = round(($studiedTopics / $totalTopics) * 100, 2);

        return [
            'studiedTopics' => $studiedTopics,
            'totalTopics' => $totalTopics,
            'studiedPercentage' => $studiedPercentage,
            'remainingPercentage' => round(100 - $studiedPercentage, 2),
        ];
    }
}
