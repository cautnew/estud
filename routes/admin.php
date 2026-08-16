<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\ExamNoticeController;
use App\Http\Controllers\Admin\ExamNoticeImportController;
use App\Http\Controllers\Admin\ExamSubjectController;
use App\Http\Controllers\Admin\ExamTopicController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TopicController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:Admin'])->group(function () {
    Route::inertia('admin/dashboard', 'admin/dashboard')->name('admin.dashboard');

    Route::name('admin.')->prefix('admin')->group(function () {
        Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');

        Route::resource('subjects', SubjectController::class)
            ->except(['create', 'edit']);

        Route::resource('subjects.topics', TopicController::class)
            ->shallow()
            ->only(['store', 'update', 'destroy']);

        // Registered before the resource so "import" isn't swallowed by
        // the {examNotice} show route.
        Route::get('exam-notices/import', [ExamNoticeImportController::class, 'create'])
            ->name('exam-notices.import.create');
        Route::post('exam-notices/import', [ExamNoticeImportController::class, 'store'])
            ->name('exam-notices.import.store');

        Route::resource('exam-notices', ExamNoticeController::class)
            ->parameters(['exam-notices' => 'examNotice'])
            ->except(['create', 'edit']);

        Route::post('exam-notices/{examNotice}/duplicate', [ExamNoticeController::class, 'duplicate'])
            ->name('exam-notices.duplicate');

        Route::resource('exam-notices.exam-subjects', ExamSubjectController::class)
            ->parameters(['exam-notices' => 'examNotice', 'exam-subjects' => 'examSubject'])
            ->shallow()
            ->only(['store', 'destroy']);

        Route::resource('exam-subjects.exam-topics', ExamTopicController::class)
            ->parameters(['exam-subjects' => 'examSubject', 'exam-topics' => 'examTopic'])
            ->shallow()
            ->only(['store', 'destroy']);
    });
});
