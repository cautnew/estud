<?php

use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TopicController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('subjects', SubjectController::class)
        ->except(['create', 'edit', 'destroy']);

    Route::resource('subjects.topics', TopicController::class)
        ->shallow()
        ->only(['store', 'update']);
});
