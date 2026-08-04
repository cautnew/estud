<?php

namespace App\Http\Controllers;

use App\Actions\StudySessions\CompleteStudySession;
use App\Actions\StudySessions\PauseStudySession;
use App\Actions\StudySessions\ResumeStudySession;
use App\Actions\StudySessions\StartStudySession;
use App\Actions\StudySessions\SyncStudySessionDuration;
use App\Http\Requests\StudySessionCompleteRequest;
use App\Http\Requests\StudySessionStoreRequest;
use App\Http\Requests\StudySessionUpdateRequest;
use App\Models\StudySession;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudySessionController extends Controller
{
    /**
     * List the current user's study sessions, most recent first, along
     * with the subject/topic catalog needed to start a new one.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('study-sessions/index', [
            'studySessions' => StudySession::query()
                ->ownedBy($request->user())
                ->with(['subject', 'topic'])
                ->orderByDesc('date')
                ->orderByDesc('created_at')
                ->get(),
            'availableSubjects' => Subject::query()
                ->visibleTo($request->user())
                ->with('topics')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Start the stopwatch on a new study session owned by the current
     * user.
     */
    public function store(StudySessionStoreRequest $request, StartStudySession $startStudySession): RedirectResponse
    {
        $startStudySession->handle(
            owner: $request->user(),
            subject: Subject::findOrFail($request->validated('subject_id')),
            topic: $request->validated('topic_id') ? Topic::findOrFail($request->validated('topic_id')) : null,
            date: $request->validated('date'),
            note: $request->validated('note'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Sessão de estudo iniciada.')]);

        return to_route('study-sessions.index');
    }

    /**
     * Update a session's logged details (subject, topic, date, note).
     */
    public function update(StudySessionUpdateRequest $request, StudySession $studySession): RedirectResponse
    {
        $studySession->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Sessão de estudo atualizada.')]);

        return to_route('study-sessions.index');
    }

    /**
     * Delete a study session. Only the owner may delete it.
     */
    public function destroy(Request $request, StudySession $studySession): RedirectResponse
    {
        abort_unless($request->user()->can('delete', $studySession), 403);

        $studySession->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Sessão de estudo removida.')]);

        return to_route('study-sessions.index');
    }

    /**
     * Pause the running stopwatch.
     */
    public function pause(Request $request, StudySession $studySession, PauseStudySession $pauseStudySession): RedirectResponse
    {
        abort_unless($request->user()->can('pause', $studySession), 403);

        $pauseStudySession->handle($studySession);

        return back();
    }

    /**
     * Resume a paused stopwatch.
     */
    public function resume(Request $request, StudySession $studySession, ResumeStudySession $resumeStudySession): RedirectResponse
    {
        abort_unless($request->user()->can('resume', $studySession), 403);

        $resumeStudySession->handle($studySession);

        return back();
    }

    /**
     * Complete the session, stopping the stopwatch for good.
     */
    public function complete(StudySessionCompleteRequest $request, StudySession $studySession, CompleteStudySession $completeStudySession): RedirectResponse
    {
        $completeStudySession->handle($studySession, $request->validated('note'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Sessão de estudo concluída.')]);

        return back();
    }

    /**
     * Auto-save checkpoint, polled periodically by the frontend timer so
     * elapsed time isn't lost if the tab closes unexpectedly.
     */
    public function sync(Request $request, StudySession $studySession, SyncStudySessionDuration $syncStudySessionDuration): RedirectResponse
    {
        abort_unless($request->user()->can('sync', $studySession), 403);

        $syncStudySessionDuration->handle($studySession);

        return back();
    }
}
