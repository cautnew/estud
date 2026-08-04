<?php

namespace App\Http\Controllers;

use App\Enums\StudySessionStatus;
use App\Models\StudySession;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Summarize the current user's study activity: time studied today and
     * over the last 7 days, sessions completed this week, a per-subject
     * breakdown, and the currently running/paused session (if any) so the
     * stopwatch widget can pick up where it left off.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $today = today();
        $weekStart = $today->copy()->subDays(6);

        $recentSessions = StudySession::query()
            ->ownedBy($user)
            ->with('subject')
            ->whereDate('date', '>=', $weekStart)
            ->get();

        $todaySeconds = $recentSessions
            ->filter(fn (StudySession $session) => $session->date->isSameDay($today))
            ->sum(fn (StudySession $session) => $session->currentDurationSeconds());

        $weekSeconds = $recentSessions->sum(fn (StudySession $session) => $session->currentDurationSeconds());

        $completedThisWeek = $recentSessions->where('status', StudySessionStatus::Completed)->count();

        $secondsBySubject = $recentSessions
            ->groupBy(fn (StudySession $session) => $session->subject->name)
            ->map(fn ($sessions) => $sessions->sum(fn (StudySession $session) => $session->currentDurationSeconds()))
            ->sortDesc()
            ->take(5);

        return Inertia::render('dashboard', [
            'summary' => [
                'todaySeconds' => $todaySeconds,
                'weekSeconds' => $weekSeconds,
                'completedThisWeek' => $completedThisWeek,
                'bySubject' => $secondsBySubject
                    ->map(fn (int $seconds, string $subject) => ['subject' => $subject, 'seconds' => $seconds])
                    ->values(),
            ],
            'activeSession' => StudySession::query()
                ->ownedBy($user)
                ->with(['subject', 'topic'])
                ->whereIn('status', [StudySessionStatus::InProgress, StudySessionStatus::Paused])
                ->first(),
        ]);
    }
}
