<?php

namespace App\Http\Controllers;

use App\Actions\StudyPlans\DuplicateStudyPlan;
use App\Http\Requests\StudyPlanStoreRequest;
use App\Http\Requests\StudyPlanUpdateRequest;
use App\Models\StudyPlan;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudyPlanController extends Controller
{
    /**
     * List the current user's own study plans.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('study-plans/index', [
            'studyPlans' => StudyPlan::query()
                ->ownedBy($request->user())
                ->withCount('studyPlanSubjects')
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    /**
     * Browse the public library of plans published by any user.
     */
    public function library(): Response
    {
        return Inertia::render('study-plans/library', [
            'studyPlans' => StudyPlan::query()
                ->public()
                ->with('owner:id,name')
                ->withCount('studyPlanSubjects')
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    /**
     * Create a study plan owned by the current user.
     */
    public function store(StudyPlanStoreRequest $request): RedirectResponse
    {
        $studyPlan = StudyPlan::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Plano de estudos criado.')]);

        return to_route('study-plans.show', $studyPlan);
    }

    /**
     * Show a plan's subjects and topics. Owners see their own plans;
     * anyone may view a plan once it is published to the public library.
     */
    public function show(Request $request, StudyPlan $studyPlan): Response
    {
        abort_unless($request->user()->can('view', $studyPlan), 403);

        $studyPlan->load([
            'studyPlanSubjects.subject.topics',
            'studyPlanSubjects.studyPlanTopics.topic',
        ]);

        $linkedSubjectIds = $studyPlan->studyPlanSubjects->pluck('subject_id');

        return Inertia::render('study-plans/show', [
            'studyPlan' => $studyPlan,
            'availableSubjects' => Subject::query()
                ->visibleTo($request->user())
                ->whereNotIn('id', $linkedSubjectIds)
                ->orderBy('name')
                ->get(),
            'canManage' => $request->user()->can('update', $studyPlan),
        ]);
    }

    /**
     * Update a plan. Only the owner may update it, including publishing
     * or unpublishing it from the public library.
     */
    public function update(StudyPlanUpdateRequest $request, StudyPlan $studyPlan): RedirectResponse
    {
        $studyPlan->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Plano de estudos atualizado.')]);

        return to_route('study-plans.show', $studyPlan);
    }

    /**
     * Delete a plan. Only the owner may delete it.
     */
    public function destroy(Request $request, StudyPlan $studyPlan): RedirectResponse
    {
        abort_unless($request->user()->can('delete', $studyPlan), 403);

        $studyPlan->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Plano de estudos excluído.')]);

        return to_route('study-plans.index');
    }

    /**
     * Duplicate a plan into a brand new plan owned by the current user,
     * with all progress reset. Used both to duplicate one's own plan and
     * to import/clone a plan from the public library.
     */
    public function duplicate(Request $request, StudyPlan $studyPlan, DuplicateStudyPlan $duplicateStudyPlan): RedirectResponse
    {
        abort_unless($request->user()->can('duplicate', $studyPlan), 403);

        $copy = $duplicateStudyPlan->handle($studyPlan, $request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Plano de estudos duplicado.')]);

        return to_route('study-plans.show', $copy);
    }
}
