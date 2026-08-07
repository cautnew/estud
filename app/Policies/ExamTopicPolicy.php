<?php

namespace App\Policies;

use App\Models\ExamSubject;
use App\Models\ExamTopic;
use App\Models\User;

class ExamTopicPolicy
{
    /**
     * Exam topics are managed exclusively through the admin panel; every
     * ability here is denied for regular users and granted to Admins only
     * via the Gate::before bypass.
     */
    public function create(User $user, ExamSubject $examSubject): bool
    {
        return false;
    }

    public function delete(User $user, ExamTopic $examTopic): bool
    {
        return false;
    }
}
