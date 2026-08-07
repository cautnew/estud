<?php

namespace App\Policies;

use App\Models\ExamNotice;
use App\Models\ExamSubject;
use App\Models\User;

class ExamSubjectPolicy
{
    /**
     * Exam subjects are managed exclusively through the admin panel; every
     * ability here is denied for regular users and granted to Admins only
     * via the Gate::before bypass.
     */
    public function create(User $user, ExamNotice $examNotice): bool
    {
        return false;
    }

    public function delete(User $user, ExamSubject $examSubject): bool
    {
        return false;
    }
}
