<?php

namespace App\Http\Controllers;

use App\Actions\Reviews\UpdateReviewPreference;
use App\Http\Requests\ReviewPreferenceUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class ReviewPreferenceController extends Controller
{
    /**
     * Customize the intervals used to schedule future review chains.
     * Only affects reviews generated after this change.
     */
    public function update(ReviewPreferenceUpdateRequest $request, UpdateReviewPreference $updateReviewPreference): RedirectResponse
    {
        $updateReviewPreference->handle($request->user(), $request->validated('intervals'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Intervalos de revisão atualizados.')]);

        return back();
    }
}
