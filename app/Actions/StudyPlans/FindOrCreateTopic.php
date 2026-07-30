<?php

namespace App\Actions\StudyPlans;

use App\Models\Subject;
use App\Models\Topic;

class FindOrCreateTopic
{
    /**
     * Resolve a topic by name (case-insensitive) within the given subject,
     * creating a new topic if none matches.
     */
    public function handle(string $name, Subject $subject): Topic
    {
        $name = trim($name);

        $topic = $subject->topics()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($topic !== null) {
            return $topic;
        }

        return $subject->topics()->create(['name' => $name]);
    }
}
