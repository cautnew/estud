<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ExamNotices\CalculateExamNoticeProgress;
use App\Actions\ExamNotices\DuplicateExamNotice;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExamNoticeStoreRequest;
use App\Http\Requests\ExamNoticeUpdateRequest;
use App\Models\ExamNotice;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExamNoticeController extends Controller
{
    /**
     * List every exam notice for administrative management.
     */
    public function index(): Response
    {
        return Inertia::render('admin/exam-notices/index', [
            'examNotices' => ExamNotice::query()
                ->withCount('examSubjects')
                ->with('owner:id,name')
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    /**
     * Register a new exam notice.
     */
    public function store(ExamNoticeStoreRequest $request): RedirectResponse
    {
        $examNotice = ExamNotice::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Edital cadastrado.')]);

        return to_route('admin.exam-notices.show', $examNotice);
    }

    /**
     * Show an exam notice's subjects/topics for management, along with
     * how much of it the acting admin has already studied.
     */
    public function show(Request $request, ExamNotice $examNotice, CalculateExamNoticeProgress $calculateExamNoticeProgress): Response
    {
        $examNotice->load(['examSubjects.subject', 'examSubjects.examTopics.topic', 'owner:id,name']);

        return Inertia::render('admin/exam-notices/show', [
            'examNotice' => $examNotice,
            'progress' => $calculateExamNoticeProgress->handle($examNotice, $request->user()),
            'availableSubjects' => Subject::query()
                ->with('topics')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Update an exam notice's details.
     */
    public function update(ExamNoticeUpdateRequest $request, ExamNotice $examNotice): RedirectResponse
    {
        $examNotice->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Edital atualizado.')]);

        return to_route('admin.exam-notices.show', $examNotice);
    }

    /**
     * Delete an exam notice and its subjects/topics.
     */
    public function destroy(Request $request, ExamNotice $examNotice): RedirectResponse
    {
        abort_unless($request->user()->can('delete', $examNotice), 403);

        $examNotice->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Edital excluído.')]);

        return to_route('admin.exam-notices.index');
    }

    /**
     * Clone an exam notice's subjects/topics into a brand new notice.
     */
    public function duplicate(Request $request, ExamNotice $examNotice, DuplicateExamNotice $duplicateExamNotice): RedirectResponse
    {
        abort_unless($request->user()->can('duplicate', $examNotice), 403);

        $copy = $duplicateExamNotice->handle($examNotice, $request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Edital clonado.')]);

        return to_route('admin.exam-notices.show', $copy);
    }
}
