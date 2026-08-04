<?php

use App\Http\Controllers\QuestionLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('question-logs', QuestionLogController::class)
        ->parameters(['question-logs' => 'questionLog'])
        ->only(['index', 'store', 'update', 'destroy']);
});
