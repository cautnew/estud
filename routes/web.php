<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
require __DIR__.'/subjects.php';
require __DIR__.'/study-plans.php';
require __DIR__.'/schedules.php';
require __DIR__.'/study-sessions.php';
require __DIR__.'/reviews.php';
require __DIR__.'/question-logs.php';
require __DIR__.'/goals.php';
require __DIR__.'/recommendations.php';
require __DIR__.'/reports.php';
