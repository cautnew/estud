<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExamTopicStoreRequest;
use App\Models\ExamSubject;
use App\Models\ExamTopic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExamTopicController extends Controller
{
    /**
     * Add a topic from the catalog to an exam subject.
     */
    public function store(ExamTopicStoreRequest $request, ExamSubject $examSubject): RedirectResponse
    {
        $examSubject->examTopics()->create([
            'topic_id' => $request->validated('topic_id'),
            'order' => $examSubject->examTopics()->count(),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Tópico adicionado ao edital.')]);

        return to_route('admin.exam-notices.show', $examSubject->examNotice);
    }

    /**
     * Remove a topic from an exam subject.
     */
    public function destroy(Request $request, ExamTopic $examTopic): RedirectResponse
    {
        abort_unless($request->user()->can('delete', $examTopic), 403);

        $examNotice = $examTopic->examSubject->examNotice;
        $examTopic->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Tópico removido do edital.')]);

        return to_route('admin.exam-notices.show', $examNotice);
    }
}
