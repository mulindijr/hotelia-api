<?php

namespace App\Jobs;

use App\Constants\BookingStatus;
use App\Models\Booking;
use App\Services\Booking\BookingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoCancelStaleBookings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function backoff(): array
    {
        return [10, 30, 60];
    }

    /**
     * Execute the job.
     */
    public function handle(BookingService $bookingService): void
    {
        $minutes = (int) env('BOOKING_AUTO_CANCEL_MINUTES', 120);
        $threshold = now()->subMinutes($minutes)->toDateTimeString();

        $staleBookings = Booking::where('status', BookingStatus::PENDING)
            ->where('created_at', '<=', $threshold)
            ->get();

        foreach ($staleBookings as $booking) {
            try {
                $bookingService->cancel($booking);
                Log::info("Auto-cancelled stale pending booking #{$booking->id} (Ref: {$booking->booking_reference}).");
            } catch (\Throwable $e) {
                Log::error("Failed to auto-cancel stale booking #{$booking->id}: {$e->getMessage()}");
            }
        }
    }
}
