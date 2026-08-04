<?php

namespace App\Http\Controllers;

use App\Actions\Goals\RefreshGoalProgress;
use App\Http\Requests\GoalStoreRequest;
use App\Http\Requests\GoalUpdateRequest;
use App\Models\Goal;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GoalController extends Controller
{
    /**
     * List the current user's goals with their progress history, freshly
     * recomputed so the visual tracking always reflects the latest data.
     */
    public function index(Request $request, RefreshGoalProgress $refreshGoalProgress): Response
    {
        $user = $request->user();

        $refreshGoalProgress->handle($user);

        return Inertia::render('goals/index', [
            'goals' => Goal::query()
                ->ownedBy($user)
                ->with(['subject', 'goalProgress'])
                ->orderByDesc('created_at')
                ->get(),
            'availableSubjects' => Subject::query()
                ->visibleTo($user)
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Create a goal owned by the current user and compute its initial
     * progress right away.
     */
    public function store(GoalStoreRequest $request, RefreshGoalProgress $refreshGoalProgress): RedirectResponse
    {
        Goal::create($request->validated());

        $refreshGoalProgress->handle($request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Meta criada.')]);

        return to_route('goals.index');
    }

    /**
     * Update a goal's target/period/subject and recompute its progress
     * for the (possibly new) current period.
     */
    public function update(GoalUpdateRequest $request, Goal $goal, RefreshGoalProgress $refreshGoalProgress): RedirectResponse
    {
        $goal->update($request->validated());

        $refreshGoalProgress->handle($request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Meta atualizada.')]);

        return to_route('goals.index');
    }

    /**
     * Delete a goal. Only the owner may delete it.
     */
    public function destroy(Request $request, Goal $goal): RedirectResponse
    {
        abort_unless($request->user()->can('delete', $goal), 403);

        $goal->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Meta removida.')]);

        return to_route('goals.index');
    }
}
