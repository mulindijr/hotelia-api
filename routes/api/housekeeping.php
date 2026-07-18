<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Hotels\HousekeepingController;

Route::middleware(['auth:sanctum'])->prefix('hotels/{hotel}/housekeeping')->group(function () {
    Route::get('/', [HousekeepingController::class, 'index'])->middleware('permission:view housekeeping')->name('housekeeping.index');
    Route::post('/', [HousekeepingController::class, 'store'])->middleware('permission:manage housekeeping')->name('housekeeping.store');
    Route::get('/{task}', [HousekeepingController::class, 'show'])->middleware('permission:view housekeeping')->name('housekeeping.show');
    Route::put('/{task}', [HousekeepingController::class, 'update'])->middleware('permission:manage housekeeping')->name('housekeeping.update');
    Route::delete('/{task}', [HousekeepingController::class, 'destroy'])->middleware('permission:manage housekeeping')->name('housekeeping.destroy');
});
