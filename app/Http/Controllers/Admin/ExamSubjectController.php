<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExamSubjectStoreRequest;
use App\Models\ExamNotice;
use App\Models\ExamSubject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExamSubjectController extends Controller
{
    /**
     * Add a subject from the catalog to an exam notice.
     */
    public function store(ExamSubjectStoreRequest $request, ExamNotice $examNotice): RedirectResponse
    {
        $examNotice->examSubjects()->create([
            'subject_id' => $request->validated('subject_id'),
            'order' => $examNotice->examSubjects()->count(),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Disciplina adicionada ao edital.')]);

        return to_route('admin.exam-notices.show', $examNotice);
    }

    /**
     * Remove a subject (and its topics) from an exam notice.
     */
    public function destroy(Request $request, ExamSubject $examSubject): RedirectResponse
    {
        abort_unless($request->user()->can('delete', $examSubject), 403);

        $examNotice = $examSubject->examNotice;
        $examSubject->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Disciplina removida do edital.')]);

        return to_route('admin.exam-notices.show', $examNotice);
    }
}
