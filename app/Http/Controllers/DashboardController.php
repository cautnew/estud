<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\CalculateDashboardStats;
use App\Enums\StudySessionStatus;
use App\Models\StudySession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Stats are the most expensive part of the dashboard (heatmap, monthly
     * evolution, period comparison, radar, ranking all scan historical
     * data), so they're cached briefly per user. The active session is
     * never cached, since the stopwatch widget needs it live.
     */
    private const int STATS_CACHE_SECONDS = 120;

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
            'stats' => Cache::remember(
                $this->cacheKey($user),
                self::STATS_CACHE_SECONDS,
                fn () => $calculateDashboardStats->handle($user),
            ),
            'activeSession' => StudySession::query()
                ->ownedBy($user)
                ->with(['subject', 'topic'])
                ->whereIn('status', [StudySessionStatus::InProgress, StudySessionStatus::Paused])
                ->first(),
        ]);
    }

    private function cacheKey(User $user): string
    {
        return "dashboard-stats:{$user->id}";
    }
}
