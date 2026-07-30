<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\ScheduleItem;
use App\Models\User;

class ScheduleItemPolicy
{
    /**
     * A user may only add items to a schedule they own; Admin bypasses
     * via Gate::before.
     */
    public function create(User $user, Schedule $schedule): bool
    {
        return $schedule->owner_id === $user->id;
    }

    /**
     * A user may only update items on a schedule they own; Admin bypasses
     * via Gate::before.
     */
    public function update(User $user, ScheduleItem $scheduleItem): bool
    {
        return $scheduleItem->schedule->owner_id === $user->id;
    }

    /**
     * A user may only remove items from a schedule they own; Admin
     * bypasses via Gate::before.
     */
    public function delete(User $user, ScheduleItem $scheduleItem): bool
    {
        return $scheduleItem->schedule->owner_id === $user->id;
    }

    /**
     * Moving/reordering an item requires the same ownership as updating it.
     */
    public function move(User $user, ScheduleItem $scheduleItem): bool
    {
        return $this->update($user, $scheduleItem);
    }
}
