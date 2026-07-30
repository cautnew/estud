<?php

namespace App\Actions\StudyPlans;

use App\Models\Subject;
use App\Models\User;

class FindOrCreateSubject
{
    /**
     * Resolve a subject by name (case-insensitive) among those visible to
     * the user, creating a new owner-scoped subject if none matches.
     */
    public function handle(string $name, User $user): Subject
    {
        $name = trim($name);

        $subject = Subject::query()
            ->visibleTo($user)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($subject !== null) {
            return $subject;
        }

        return Subject::create([
            'owner_id' => $user->id,
            'name' => $name,
        ]);
    }
}
