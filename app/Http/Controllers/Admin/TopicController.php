<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TopicStoreRequest;
use App\Http\Requests\TopicUpdateRequest;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class TopicController extends Controller
{
    public function store(TopicStoreRequest $request, Subject $subject): RedirectResponse
    {
        $subject->topics()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Tópico criado.')]);

        return to_route('admin.subjects.show', $subject);
    }

    public function update(TopicUpdateRequest $request, Topic $topic): RedirectResponse
    {
        $topic->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Tópico atualizado.')]);

        return to_route('admin.subjects.show', $topic->subject);
    }

    /**
     * Delete a topic. Only reachable by Admins; regular users can never
     * delete topics.
     */
    public function destroy(Topic $topic): RedirectResponse
    {
        $subject = $topic->subject;

        $topic->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Tópico excluído.')]);

        return to_route('admin.subjects.show', $subject);
    }
}
