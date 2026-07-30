<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudyPlanTopicStoreRequest;
use App\Http\Requests\StudyPlanTopicUpdateRequest;
use App\Models\StudyPlanSubject;
use App\Models\StudyPlanTopic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudyPlanTopicController extends Controller
{
    /**
     * Add a topic from the catalog to a plan subject the user owns.
     */
    public function store(StudyPlanTopicStoreRequest $request, StudyPlanSubject $studyPlanSubject): RedirectResponse
    {
        $studyPlanSubject->studyPlanTopics()->create([
            'topic_id' => $request->validated('topic_id'),
            'order' => $studyPlanSubject->studyPlanTopics()->count(),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Tópico adicionado ao plano.')]);

        return to_route('study-plans.show', $studyPlanSubject->studyPlan);
    }

    /**
     * Update a topic's progress (status, studied date, notes).
     */
    public function update(StudyPlanTopicUpdateRequest $request, StudyPlanTopic $studyPlanTopic): RedirectResponse
    {
        $studyPlanTopic->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Tópico atualizado.')]);

        return to_route('study-plans.show', $studyPlanTopic->studyPlanSubject->studyPlan);
    }

    /**
     * Remove a topic from a plan subject the user owns.
     */
    public function destroy(Request $request, StudyPlanTopic $studyPlanTopic): RedirectResponse
    {
        abort_unless($request->user()->can('delete', $studyPlanTopic), 403);

        $studyPlan = $studyPlanTopic->studyPlanSubject->studyPlan;
        $studyPlanTopic->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Tópico removido do plano.')]);

        return to_route('study-plans.show', $studyPlan);
    }
}
