<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubjectStoreRequest;
use App\Http\Requests\SubjectUpdateRequest;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SubjectController extends Controller
{
    /**
     * List every subject, global and user-owned alike.
     */
    public function index(): Response
    {
        return Inertia::render('admin/subjects/index', [
            'subjects' => Subject::query()
                ->withCount('topics')
                ->with('owner:id,name')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Create a subject owned by the acting admin.
     */
    public function store(SubjectStoreRequest $request): RedirectResponse
    {
        Subject::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Disciplina criada.')]);

        return to_route('admin.subjects.index');
    }

    /**
     * Show a subject with its topics for management.
     */
    public function show(Subject $subject): Response
    {
        $subject->load(['topics' => fn ($query) => $query->orderBy('name'), 'owner:id,name']);

        return Inertia::render('admin/subjects/show', [
            'subject' => $subject,
        ]);
    }

    public function update(SubjectUpdateRequest $request, Subject $subject): RedirectResponse
    {
        $subject->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Disciplina atualizada.')]);

        return to_route('admin.subjects.show', $subject);
    }

    /**
     * Delete a subject. Global subjects are permanent, and user-owned
     * subjects can only be removed once they have no topics left.
     */
    public function destroy(Subject $subject): RedirectResponse
    {
        abort_if($subject->owner_id === null, 403, __('Disciplinas globais não podem ser excluídas.'));
        abort_if($subject->topics()->exists(), 422, __('Não é possível excluir uma disciplina que possui tópicos.'));

        $subject->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Disciplina excluída.')]);

        return to_route('admin.subjects.index');
    }
}
