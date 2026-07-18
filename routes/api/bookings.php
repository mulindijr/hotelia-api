<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Hotels\BookingController;

Route::middleware(['auth:sanctum'])->prefix('hotels/{hotel}/bookings')->group(function () {
    Route::get('/', [BookingController::class, 'index'])->middleware('permission:view bookings')->name('bookings.index');
    Route::post('/', [BookingController::class, 'store'])->middleware('permission:create bookings')->name('bookings.store');
    Route::get('/{booking}', [BookingController::class, 'show'])->middleware('permission:view bookings')->name('bookings.show');
    Route::put('/{booking}', [BookingController::class, 'update'])->middleware('permission:update bookings')->name('bookings.update');
    Route::post('/{booking}/cancel', [BookingController::class, 'cancel'])->middleware('permission:cancel bookings')->name('bookings.cancel');
    Route::post('/{booking}/check-in', [BookingController::class, 'checkIn'])->middleware('permission:check in guests')->name('bookings.check-in');
    Route::post('/{booking}/check-out', [BookingController::class, 'checkOut'])->middleware('permission:check out guests')->name('bookings.check-out');
});
