<?php

use App\Http\Controllers\StudySessionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('study-sessions', StudySessionController::class)
        ->parameters(['study-sessions' => 'studySession'])
        ->only(['index', 'store', 'update', 'destroy']);

    Route::patch('study-sessions/{studySession}/pause', [StudySessionController::class, 'pause'])
        ->name('study-sessions.pause');

    Route::patch('study-sessions/{studySession}/resume', [StudySessionController::class, 'resume'])
        ->name('study-sessions.resume');

    Route::patch('study-sessions/{studySession}/complete', [StudySessionController::class, 'complete'])
        ->name('study-sessions.complete');

    Route::patch('study-sessions/{studySession}/sync', [StudySessionController::class, 'sync'])
        ->name('study-sessions.sync');
});
