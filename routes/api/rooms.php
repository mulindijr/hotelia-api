<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Rooms\AmenityController;
use App\Http\Controllers\Api\V1\Rooms\RoomTypeController;
use App\Http\Controllers\Api\V1\Rooms\RoomController;

Route::middleware(['auth:sanctum'])->group(function () {

    // 1. Global Amenities Routes
    Route::prefix('amenities')->group(function () {
        Route::get('/', [AmenityController::class, 'index'])->middleware('permission:view amenities')->name('amenities.index');
        Route::post('/', [AmenityController::class, 'store'])->middleware('permission:create amenities')->name('amenities.store');
        Route::put('/{amenity}', [AmenityController::class, 'update'])->middleware('permission:update amenities')->name('amenities.update');
        Route::delete('/{amenity}', [AmenityController::class, 'destroy'])->middleware('permission:delete amenities')->name('amenities.destroy');
    });

    // 2. Nested Room Types Routes under Hotels
    Route::prefix('hotels/{hotel}/room-types')->group(function () {
        Route::get('/', [RoomTypeController::class, 'index'])->middleware('permission:view room types')->name('room-types.index');
        Route::post('/', [RoomTypeController::class, 'store'])->middleware('permission:create room types')->name('room-types.store');
        Route::get('/{room_type}', [RoomTypeController::class, 'show'])->middleware('permission:view room types')->name('room-types.show');
        Route::put('/{room_type}', [RoomTypeController::class, 'update'])->middleware('permission:update room types')->name('room-types.update');
        Route::delete('/{room_type}', [RoomTypeController::class, 'destroy'])->middleware('permission:delete room types')->name('room-types.destroy');
    });

    // 3. Nested Rooms Routes under Hotels
    Route::prefix('hotels/{hotel}/rooms')->group(function () {
        Route::get('/', [RoomController::class, 'index'])->middleware('permission:view rooms')->name('rooms.index');
        Route::post('/', [RoomController::class, 'store'])->middleware('permission:create rooms')->name('rooms.store');
        Route::get('/{room}', [RoomController::class, 'show'])->middleware('permission:view rooms')->name('rooms.show');
        Route::put('/{room}', [RoomController::class, 'update'])->middleware('permission:update rooms')->name('rooms.update');
        Route::delete('/{room}', [RoomController::class, 'destroy'])->middleware('permission:delete rooms')->name('rooms.destroy');
    });
});
