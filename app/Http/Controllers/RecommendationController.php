<?php

namespace App\Http\Controllers;

use App\Actions\Recommendations\CalculateHealthScore;
use App\Actions\Recommendations\GenerateReviewRecommendations;
use App\Actions\Recommendations\GenerateStudyRecommendations;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecommendationController extends Controller
{
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
            'healthScore' => $calculateHealthScore->handle($user),
            'studyToday' => $generateStudyRecommendations->handle($user),
            'reviewToday' => $reviews['dueToday'],
            'overdue' => $reviews['overdue'],
        ]);
    }
}
