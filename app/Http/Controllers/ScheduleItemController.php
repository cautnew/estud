<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScheduleItemMoveRequest;
use App\Http\Requests\ScheduleItemStoreRequest;
use App\Http\Requests\ScheduleItemUpdateRequest;
use App\Models\Schedule;
use App\Models\ScheduleItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScheduleItemController extends Controller
{
    /**
     * Add an item (subject block) to a schedule the user owns.
     */
    public function store(ScheduleItemStoreRequest $request, Schedule $schedule): RedirectResponse
    {
        $schedule->scheduleItems()->create([
            ...$request->validated(),
            'priority' => $request->validated('priority') ?? $schedule->scheduleItems()->count(),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Item adicionado ao cronograma.')]);

        return to_route('schedules.show', $schedule);
    }

    /**
     * Update an item's day/time/duration/priority fields.
     */
    public function update(ScheduleItemUpdateRequest $request, ScheduleItem $scheduleItem): RedirectResponse
    {
        $scheduleItem->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Item atualizado.')]);

        return to_route('schedules.show', $scheduleItem->schedule);
    }

    /**
     * Remove an item from a schedule the user owns.
     */
    public function destroy(Request $request, ScheduleItem $scheduleItem): RedirectResponse
    {
        abort_unless($request->user()->can('delete', $scheduleItem), 403);

        $schedule = $scheduleItem->schedule;
        $scheduleItem->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Item removido do cronograma.')]);

        return to_route('schedules.show', $schedule);
    }

    /**
     * Move/resize an item via the drag-and-drop calendar UI: updates the
     * day placement, start time, duration, and priority in one request.
     * Distinct from update() so the frontend has a single, purpose-built
     * endpoint for the drag-and-drop interaction, with no partial-field
     * ambiguity and an explicit `move` ability in the policy layer.
     */
    public function move(ScheduleItemMoveRequest $request, ScheduleItem $scheduleItem): RedirectResponse
    {
        $scheduleItem->update($request->validated());

        return back();
    }
}
