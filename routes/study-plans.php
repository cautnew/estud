<?php

use App\Http\Controllers\StudyPlanController;
use App\Http\Controllers\StudyPlanImportController;
use App\Http\Controllers\StudyPlanSubjectController;
use App\Http\Controllers\StudyPlanTopicController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    // Registered before the resource so "library"/"import" aren't
    // swallowed by the {studyPlan} show route.
    Route::get('study-plans/library', [StudyPlanController::class, 'library'])->name('study-plans.library');
    Route::get('study-plans/import', [StudyPlanImportController::class, 'create'])->name('study-plans.import.create');
    Route::post('study-plans/import', [StudyPlanImportController::class, 'store'])->name('study-plans.import.store');

    Route::resource('study-plans', StudyPlanController::class)
        ->parameters(['study-plans' => 'studyPlan'])
        ->except(['create', 'edit']);

    Route::post('study-plans/{studyPlan}/duplicate', [StudyPlanController::class, 'duplicate'])
        ->name('study-plans.duplicate');

    Route::resource('study-plans.plan-subjects', StudyPlanSubjectController::class)
        ->parameters(['study-plans' => 'studyPlan', 'plan-subjects' => 'studyPlanSubject'])
        ->shallow()
        ->only(['store', 'destroy']);

    Route::resource('plan-subjects.plan-topics', StudyPlanTopicController::class)
        ->parameters(['plan-subjects' => 'studyPlanSubject', 'plan-topics' => 'studyPlanTopic'])
        ->shallow()
        ->only(['store', 'update', 'destroy']);
});
