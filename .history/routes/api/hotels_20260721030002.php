<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Hotels\HotelController;

Route::middleware(['auth:sanctum'])->prefix('hotels')->group(function () {

  Route::get('/', [HotelController::class, 'index'])->middleware('permission:view hotels')->name('hotels.index');

  Route::post('/', [HotelController::class, 'store'])->middleware('permission:create hotels')->name('hotels.store');

  Route::get('/{hotel}', [HotelController::class, 'show'])->middleware('permission:view hotels')->name('hotels.show');

  Route::put('/{hotel}', [HotelController::class, 'update'])->middleware('permission:update hotels')->name('hotels.update');
  Route::pa('/{hotel}', [HotelController::class, 'update'])->middleware('permission:update hotels')->name('hotels.update');

  Route::delete('/{hotel}', [HotelController::class, 'destroy'])->middleware('permission:delete hotels')->name('hotels.destroy');

  // Hotel settings nested routes
  Route::get('/{hotel}/settings', [\App\Http\Controllers\Api\V1\Hotels\HotelSettingController::class, 'show'])
      ->middleware('permission:view hotels')
      ->name('hotels.settings.show');

  Route::put('/{hotel}/settings', [\App\Http\Controllers\Api\V1\Hotels\HotelSettingController::class, 'update'])
      ->middleware('permission:update hotels')
      ->name('hotels.settings.update');
});
