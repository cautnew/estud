<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\CalculateDashboardStats;
use App\Enums\StudySessionStatus;
use App\Models\StudySession;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Render the advanced dashboard: study-time indicators and their
     * evolution, subjects/topics completed, review and goal pace, the
     * activity heatmap, monthly evolution, a period-over-period
     * comparison, accuracy-by-subject, and a study-time ranking — plus
     * the currently running/paused session (if any) so the stopwatch
     * widget can pick up where it left off.
     */
    public function index(Request $request, CalculateDashboardStats $calculateDashboardStats): Response
    {
        $user = $request->user();

        return Inertia::render('dashboard', [
            'stats' => $calculateDashboardStats->handle($user),
            'activeSession' => StudySession::query()
                ->ownedBy($user)
                ->with(['subject', 'topic'])
                ->whereIn('status', [StudySessionStatus::InProgress, StudySessionStatus::Paused])
                ->first(),
        ]);
    }
}
