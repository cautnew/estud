<?php

use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ScheduleItemController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('schedules', ScheduleController::class)
        ->parameters(['schedules' => 'schedule'])
        ->except(['create', 'edit']);

    Route::resource('schedules.schedule-items', ScheduleItemController::class)
        ->parameters(['schedules' => 'schedule', 'schedule-items' => 'scheduleItem'])
        ->shallow()
        ->only(['store', 'update', 'destroy']);

    Route::patch('schedule-items/{scheduleItem}/move', [ScheduleItemController::class, 'move'])
        ->name('schedule-items.move');
});
