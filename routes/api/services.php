<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Hotels\ServiceController;

Route::middleware(['auth:sanctum'])->prefix('hotels/{hotel}/services')->group(function () {
    Route::get('/', [ServiceController::class, 'index'])->middleware('permission:view services')->name('services.index');
    Route::post('/', [ServiceController::class, 'store'])->middleware('permission:create services')->name('services.store');
    Route::get('/{service}', [ServiceController::class, 'show'])->middleware('permission:view services')->name('services.show');
    Route::put('/{service}', [ServiceController::class, 'update'])->middleware('permission:update services')->name('services.update');
    Route::delete('/{service}', [ServiceController::class, 'destroy'])->middleware('permission:delete services')->name('services.destroy');
});
