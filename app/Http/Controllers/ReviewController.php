<?php

namespace App\Http\Controllers;

use App\Actions\Goals\RefreshGoalProgress;
use App\Actions\Reviews\CompleteReview;
use App\Actions\Reviews\SkipReview;
use App\Enums\ReviewStatus;
use App\Models\Review;
use App\Models\ReviewPreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    /**
     * Show the current user's review agenda: every pending review plus
     * their configured spaced-repetition intervals.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('reviews/index', [
            'reviews' => Review::query()
                ->ownedBy($user)
                ->where('status', ReviewStatus::Pending)
                ->with(['studyPlanTopic.topic', 'studyPlanTopic.studyPlanSubject.subject'])
                ->orderBy('scheduled_for')
                ->get(),
            'intervals' => ReviewPreference::query()->ownedBy($user)->first()?->intervals
                ?? config('reviews.default_intervals'),
        ]);
    }

    /**
     * Mark a pending review as done.
     */
    public function complete(Request $request, Review $review, CompleteReview $completeReview, RefreshGoalProgress $refreshGoalProgress): RedirectResponse
    {
        abort_unless($request->user()->can('complete', $review), 403);

        $completeReview->handle($review);
        $refreshGoalProgress->handle($request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Revisão concluída.')]);

        return back();
    }

    /**
     * Dismiss a pending review.
     */
    public function skip(Request $request, Review $review, SkipReview $skipReview): RedirectResponse
    {
        abort_unless($request->user()->can('skip', $review), 403);

        $skipReview->handle($review);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Revisão pulada.')]);

        return back();
    }
}
