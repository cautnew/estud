<?php

use App\Http\Controllers\RecommendationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('recommendations', [RecommendationController::class, 'index'])->name('recommendations.index');
});
