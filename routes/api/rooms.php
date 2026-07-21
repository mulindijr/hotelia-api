<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Rooms\AmenityController;
use App\Http\Controllers\Api\V1\Rooms\RoomTypeController;
use App\Http\Controllers\Api\V1\Rooms\RoomController;
use App\Constants\Permissions;

Route::middleware(['auth:sanctum'])->group(function () {

    // 1. Global Amenities Routes
    Route::prefix('amenities')->group(function () {
        Route::get('/', [AmenityController::class, 'index'])->middleware('permission:' . Permissions::VIEW_AMENITIES)->name('amenities.index');
        Route::post('/', [AmenityController::class, 'store'])->middleware('permission:' . Permissions::CREATE_AMENITIES)->name('amenities.store');
        Route::put('/{amenity}', [AmenityController::class, 'update'])->middleware('permission:' . Permissions::UPDATE_AMENITIES)->name('amenities.update');
        Route::delete('/{amenity}', [AmenityController::class, 'destroy'])->middleware('permission:' . Permissions::DELETE_AMENITIES)->name('amenities.destroy');
    });

    // 2. Nested Room Types Routes under Hotels
    Route::prefix('hotels/{hotel}/room-types')->scopeBindings()->group(function () {
        Route::get('/', [RoomTypeController::class, 'index'])->middleware('permission:' . Permissions::VIEW_ROOM_TYPES)->name('room-types.index');
        Route::post('/', [RoomTypeController::class, 'store'])->middleware('permission:' . Permissions::CREATE_ROOM_TYPES)->name('room-types.store');
        Route::get('/{roomType}', [RoomTypeController::class, 'show'])->middleware('permission:' . Permissions::VIEW_ROOM_TYPES)->name('room-types.show');
        Route::put('/{roomType}', [RoomTypeController::class, 'update'])->middleware('permission:' . Permissions::UPDATE_ROOM_TYPES)->name('room-types.update');
        Route::delete('/{roomType}', [RoomTypeController::class, 'destroy'])->middleware('permission:' . Permissions::DELETE_ROOM_TYPES)->name('room-types.destroy');
    });

    // 3. Nested Rooms Routes under Hotels
    Route::prefix('hotels/{hotel}/rooms')->scopeBindings()->group(function () {
        Route::get('/', [RoomController::class, 'index'])->middleware('permission:' . Permissions::VIEW_ROOMS)->name('rooms.index');
        Route::post('/', [RoomController::class, 'store'])->middleware('permission:' . Permissions::CREATE_ROOMS)->name('rooms.store');
        Route::get('/{room}', [RoomController::class, 'show'])->middleware('permission:' . Permissions::VIEW_ROOMS)->name('rooms.show');
        Route::put('/{room}', [RoomController::class, 'update'])->middleware('permission:' . Permissions::UPDATE_ROOMS)->name('rooms.update');
        Route::delete('/{room}', [RoomController::class, 'destroy'])->middleware('permission:' . Permissions::DELETE_ROOMS)->name('rooms.destroy');
    });
});
