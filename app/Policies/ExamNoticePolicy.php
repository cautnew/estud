<?php

namespace App\Policies;

use App\Models\ExamNotice;
use App\Models\User;

class ExamNoticePolicy
{
    /**
     * Exam notices are managed exclusively through the admin panel; every
     * ability here is denied for regular users and granted to Admins only
     * via the Gate::before bypass.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function view(User $user, ExamNotice $examNotice): bool
    {
        return false;
    }

    public function update(User $user, ExamNotice $examNotice): bool
    {
        return false;
    }

    public function delete(User $user, ExamNotice $examNotice): bool
    {
        return false;
    }

    public function duplicate(User $user, ExamNotice $examNotice): bool
    {
        return false;
    }
}
