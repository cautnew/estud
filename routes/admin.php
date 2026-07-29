<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:Admin'])->group(function () {
    Route::inertia('admin/dashboard', 'admin/dashboard')->name('admin.dashboard');
});
