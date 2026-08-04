<?php

namespace App\Http\Controllers;

use App\Actions\QuestionLogs\CalculateQuestionStats;
use App\Http\Requests\QuestionLogStoreRequest;
use App\Http\Requests\QuestionLogUpdateRequest;
use App\Models\QuestionLog;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuestionLogController extends Controller
{
    /**
     * List the current user's question logs plus the indicators (accuracy
     * rate, historical evolution, average per subject) used to chart them.
     */
    public function index(Request $request, CalculateQuestionStats $calculateQuestionStats): Response
    {
        $user = $request->user();

        return Inertia::render('question-logs/index', [
            'questionLogs' => QuestionLog::query()
                ->ownedBy($user)
                ->with(['subject', 'topic'])
                ->orderByDesc('date')
                ->orderByDesc('created_at')
                ->get(),
            'availableSubjects' => Subject::query()
                ->visibleTo($user)
                ->with('topics')
                ->orderBy('name')
                ->get(),
            'stats' => $calculateQuestionStats->handle($user),
        ]);
    }

    /**
     * Log a batch of resolved questions for a subject/topic.
     */
    public function store(QuestionLogStoreRequest $request): RedirectResponse
    {
        QuestionLog::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Questões registradas.')]);

        return to_route('question-logs.index');
    }

    /**
     * Update a logged entry.
     */
    public function update(QuestionLogUpdateRequest $request, QuestionLog $questionLog): RedirectResponse
    {
        $questionLog->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Registro atualizado.')]);

        return to_route('question-logs.index');
    }

    /**
     * Delete a logged entry. Only the owner may delete it.
     */
    public function destroy(Request $request, QuestionLog $questionLog): RedirectResponse
    {
        abort_unless($request->user()->can('delete', $questionLog), 403);

        $questionLog->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Registro removido.')]);

        return to_route('question-logs.index');
    }
}
