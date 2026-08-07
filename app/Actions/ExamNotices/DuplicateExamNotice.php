<?php

namespace App\Actions\ExamNotices;

use App\Models\ExamNotice;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DuplicateExamNotice
{
    /**
     * Copy an exam notice's subjects and topics into a brand new notice
     * owned by the acting admin.
     */
    public function handle(ExamNotice $notice, User $actor): ExamNotice
    {
        return DB::transaction(function () use ($notice, $actor) {
            $copy = new ExamNotice([
                'name' => $notice->name.' (cópia)',
                'exam_board' => $notice->exam_board,
                'exam_date' => $notice->exam_date,
            ]);

            $copy->forceFill(['owner_id' => $actor->id])->save();

            foreach ($notice->examSubjects()->with('examTopics')->get() as $examSubject) {
                $newExamSubject = $copy->examSubjects()->create([
                    'subject_id' => $examSubject->subject_id,
                    'order' => $examSubject->order,
                ]);

                foreach ($examSubject->examTopics as $examTopic) {
                    $newExamSubject->examTopics()->create([
                        'topic_id' => $examTopic->topic_id,
                        'order' => $examTopic->order,
                    ]);
                }
            }

            return $copy;
        });
    }
}
