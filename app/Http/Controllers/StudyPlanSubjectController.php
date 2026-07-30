<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudyPlanSubjectStoreRequest;
use App\Models\StudyPlan;
use App\Models\StudyPlanSubject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudyPlanSubjectController extends Controller
{
    /**
     * Add a subject from the catalog to a plan the user owns.
     */
    public function store(StudyPlanSubjectStoreRequest $request, StudyPlan $studyPlan): RedirectResponse
    {
        $studyPlan->studyPlanSubjects()->create([
            'subject_id' => $request->validated('subject_id'),
            'order' => $studyPlan->studyPlanSubjects()->count(),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Disciplina adicionada ao plano.')]);

        return to_route('study-plans.show', $studyPlan);
    }

    /**
     * Remove a subject (and its topics) from a plan the user owns.
     */
    public function destroy(Request $request, StudyPlanSubject $studyPlanSubject): RedirectResponse
    {
        abort_unless($request->user()->can('delete', $studyPlanSubject), 403);

        $studyPlan = $studyPlanSubject->studyPlan;
        $studyPlanSubject->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Disciplina removida do plano.')]);

        return to_route('study-plans.show', $studyPlan);
    }
}
