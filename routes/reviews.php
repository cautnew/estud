<?php

use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewPreferenceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::patch('reviews/{review}/complete', [ReviewController::class, 'complete'])->name('reviews.complete');
    Route::patch('reviews/{review}/skip', [ReviewController::class, 'skip'])->name('reviews.skip');

    Route::patch('review-preferences', [ReviewPreferenceController::class, 'update'])->name('review-preferences.update');
});
