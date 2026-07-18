<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Hotels\ReportController;
use App\Constants\Permissions;

Route::middleware(['auth:sanctum'])->prefix('hotels/{hotel}/reports')->group(function () {
    Route::get('/dashboard', [ReportController::class, 'dashboard'])->middleware('permission:' . Permissions::VIEW_REPORTS)->name('hotels.reports.dashboard');
    Route::get('/revenue', [ReportController::class, 'revenue'])->middleware('permission:' . Permissions::VIEW_REPORTS)->name('hotels.reports.revenue');
});
