<?php

use App\Http\Controllers\GoalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('goals', GoalController::class)
        ->only(['index', 'store', 'update', 'destroy']);
});
