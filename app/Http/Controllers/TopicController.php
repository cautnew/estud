<?php

namespace App\Http\Controllers;

use App\Http\Requests\TopicStoreRequest;
use App\Http\Requests\TopicUpdateRequest;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class TopicController extends Controller
{
    /**
     * Add a topic to a subject the user owns. Users can never add topics
     * to global subjects or subjects owned by someone else.
     */
    public function store(TopicStoreRequest $request, Subject $subject): RedirectResponse
    {
        $subject->topics()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Tópico criado.')]);

        return to_route('subjects.show', $subject);
    }

    public function update(TopicUpdateRequest $request, Topic $topic): RedirectResponse
    {
        $topic->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Tópico atualizado.')]);

        return to_route('subjects.show', $topic->subject);
    }
}
