<?php

use App\Constants\Permissions;
use App\Http\Controllers\Api\V1\Reports\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('hotels/{hotel}/reports')->scopeBindings()->group(function () {
    Route::get('/dashboard', [ReportController::class, 'dashboard'])->middleware('permission:'.Permissions::VIEW_REPORTS)->name('hotels.reports.dashboard');
    Route::get('/revenue', [ReportController::class, 'revenue'])->middleware('permission:'.Permissions::VIEW_REPORTS)->name('hotels.reports.revenue');
});
