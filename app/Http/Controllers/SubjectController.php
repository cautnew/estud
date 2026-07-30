<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubjectStoreRequest;
use App\Http\Requests\SubjectUpdateRequest;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubjectController extends Controller
{
    /**
     * List subjects visible to the current user: global subjects plus
     * their own.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('subjects/index', [
            'subjects' => Subject::query()
                ->visibleTo($request->user())
                ->withCount('topics')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Create a subject owned by the current user.
     */
    public function store(SubjectStoreRequest $request): RedirectResponse
    {
        Subject::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Disciplina criada.')]);

        return to_route('subjects.index');
    }

    /**
     * Show a subject with its topics. Global subjects and the user's own
     * subjects are visible; other users' subjects are not.
     */
    public function show(Request $request, Subject $subject): Response
    {
        abort_unless($request->user()->can('view', $subject), 403);

        $subject->load(['topics' => fn ($query) => $query->orderBy('name')]);

        return Inertia::render('subjects/show', [
            'subject' => $subject,
            'canManage' => $request->user()->can('update', $subject),
        ]);
    }

    /**
     * Update a subject. Only the owner may update it; global subjects
     * cannot be edited by regular users.
     */
    public function update(SubjectUpdateRequest $request, Subject $subject): RedirectResponse
    {
        $subject->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Disciplina atualizada.')]);

        return to_route('subjects.show', $subject);
    }
}
