<?php

use App\Http\Controllers\Api\V1\Billing\InvoiceController;
use App\Http\Controllers\Api\V1\Billing\PaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('hotels/{hotel}/bookings/{booking}')->scopeBindings()->group(function () {
    Route::get('/invoice', [InvoiceController::class, 'show'])->name('bookings.invoice.show');
    Route::get('/invoice/pdf', [InvoiceController::class, 'downloadPdf'])->name('bookings.invoice.pdf');
    Route::post('/invoice/regenerate', [InvoiceController::class, 'regenerate'])->name('bookings.invoice.regenerate');
    Route::get('/payments', [PaymentController::class, 'index'])->name('bookings.payments.index');
    Route::post('/payments', [PaymentController::class, 'store'])->name('bookings.payments.store');
    Route::get('/payments/{payment}/receipt/pdf', [PaymentController::class, 'downloadReceiptPdf'])->name('bookings.payments.receipt.pdf');
    Route::post('/payments/{payment}/status', [PaymentController::class, 'updateStatus'])->name('bookings.payments.status');
});
