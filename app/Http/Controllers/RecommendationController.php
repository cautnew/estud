<?php

namespace App\Http\Controllers;

use App\Actions\Recommendations\CalculateHealthScore;
use App\Actions\Recommendations\GenerateReviewRecommendations;
use App\Actions\Recommendations\GenerateStudyRecommendations;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class RecommendationController extends Controller
{
    /**
     * The Health Score and "what to study" recommendations are derived
     * from structural data (schedule, cycle, plan, historical windows)
     * that changes slowly, so they're cached briefly per user. Review
     * recommendations reflect the reviews the user just completed/skipped,
     * so they're always computed live.
     */
    private const int STRUCTURAL_CACHE_SECONDS = 300;

    /**
     * Render the recommendation engine: what to study today, what to
     * review today, what's overdue, and the Health Score summarizing
     * overall study health.
     */
    public function index(
        Request $request,
        CalculateHealthScore $calculateHealthScore,
        GenerateStudyRecommendations $generateStudyRecommendations,
        GenerateReviewRecommendations $generateReviewRecommendations,
    ): Response {
        $user = $request->user();
        $reviews = $generateReviewRecommendations->handle($user);

        return Inertia::render('recommendations', [
            'healthScore' => Cache::remember(
                $this->cacheKey('health-score', $user),
                self::STRUCTURAL_CACHE_SECONDS,
                fn () => $calculateHealthScore->handle($user),
            ),
            'studyToday' => Cache::remember(
                $this->cacheKey('study-recommendations', $user),
                self::STRUCTURAL_CACHE_SECONDS,
                fn () => $generateStudyRecommendations->handle($user),
            ),
            'reviewToday' => $reviews['dueToday'],
            'overdue' => $reviews['overdue'],
        ]);
    }

    private function cacheKey(string $prefix, User $user): string
    {
        return "{$prefix}:{$user->id}";
    }
}
