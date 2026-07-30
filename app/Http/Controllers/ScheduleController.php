<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScheduleStoreRequest;
use App\Http\Requests\ScheduleUpdateRequest;
use App\Models\Schedule;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScheduleController extends Controller
{
    /**
     * List the current user's own schedules.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('schedules/index', [
            'schedules' => Schedule::query()
                ->ownedBy($request->user())
                ->withCount('scheduleItems')
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    /**
     * Create a schedule owned by the current user.
     */
    public function store(ScheduleStoreRequest $request): RedirectResponse
    {
        $schedule = Schedule::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Cronograma criado.')]);

        return to_route('schedules.show', $schedule);
    }

    /**
     * Show a schedule's items on the visual calendar. Owner-only.
     */
    public function show(Request $request, Schedule $schedule): Response
    {
        abort_unless($request->user()->can('view', $schedule), 403);

        $schedule->load('scheduleItems.subject');

        return Inertia::render('schedules/show', [
            'schedule' => $schedule,
            'availableSubjects' => Subject::query()
                ->visibleTo($request->user())
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Update a schedule's name/type. Only the owner may update it.
     */
    public function update(ScheduleUpdateRequest $request, Schedule $schedule): RedirectResponse
    {
        $schedule->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Cronograma atualizado.')]);

        return to_route('schedules.show', $schedule);
    }

    /**
     * Delete a schedule. Only the owner may delete it.
     */
    public function destroy(Request $request, Schedule $schedule): RedirectResponse
    {
        abort_unless($request->user()->can('delete', $schedule), 403);

        $schedule->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Cronograma excluído.')]);

        return to_route('schedules.index');
    }
}
