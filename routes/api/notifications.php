<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Notifications\NotificationController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
});
