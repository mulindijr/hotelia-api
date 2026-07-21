<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Hotels\HotelController;
use App\Constants\Permissions;

Route::middleware(['auth:sanctum'])->prefix('hotels')->group(function () {

  Route::get('/', [HotelController::class, 'index'])->middleware('permission:' . Permissions::VIEW_HOTELS)->name('hotels.index');

  Route::post('/', [HotelController::class, 'store'])->middleware('permission:' . Permissions::CREATE_HOTELS)->name('hotels.store');

  Route::get('/{hotel}', [HotelController::class, 'show'])->middleware('permission:' . Permissions::VIEW_HOTELS)->name('hotels.show');

  Route::match(['put', 'patch'], '/{hotel}', [HotelController::class, 'update'])->middleware('permission:' . Permissions::UPDATE_HOTELS)->name('hotels.update');

  Route::delete('/{hotel}', [HotelController::class, 'destroy'])->middleware('permission:' . Permissions::DELETE_HOTELS)->name('hotels.destroy');

  // Hotel settings nested routes
  Route::get('/{hotel}/settings', [\App\Http\Controllers\Api\V1\Hotels\HotelSettingController::class, 'show'])
      ->middleware('permission:' . Permissions::VIEW_HOTELS)
      ->name('hotels.settings.show');

  Route::match(['put', 'patch'], '/{hotel}/settings', [\App\Http\Controllers\Api\V1\Hotels\HotelSettingController::class, 'update'])
      ->middleware('permission:' . Permissions::UPDATE_HOTELS)
      ->name('hotels.settings.update');
});
