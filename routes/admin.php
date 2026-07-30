<?php

use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TopicController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:Admin'])->group(function () {
    Route::inertia('admin/dashboard', 'admin/dashboard')->name('admin.dashboard');

    Route::name('admin.')->prefix('admin')->group(function () {
        Route::resource('subjects', SubjectController::class)
            ->except(['create', 'edit']);

        Route::resource('subjects.topics', TopicController::class)
            ->shallow()
            ->only(['store', 'update', 'destroy']);
    });
});
