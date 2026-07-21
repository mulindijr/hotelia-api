<?php

namespace App\Jobs;

use App\Constants\RoomStatus;
use App\Models\Room;
use App\Notifications\Housekeeping\StuckInCleaningNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotifyStuckCleaningRooms implements ShouldQueue
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
    public function handle(): void
    {
        $minutes = (int) env('ROOM_CLEANING_ALERT_MINUTES', 120);
        $threshold = now()->subMinutes($minutes);

        $stuckRooms = Room::where('status', RoomStatus::CLEANING)
            ->where('updated_at', '<=', $threshold)
            ->with(['hotel', 'hotel.users'])
            ->get();

        foreach ($stuckRooms as $room) {
            $hotel = $room->hotel;
            if (!$hotel) {
                continue;
            }

            // Find staff who are managers or housekeepers at the hotel
            $staff = $hotel->users()->role(['hotel_manager', 'housekeeper'])->get();

            if ($staff->isNotEmpty()) {
                Notification::send($staff, new StuckInCleaningNotification($room, $minutes));
                Log::info("Sent StuckInCleaningNotification for Room #{$room->room_number} (Hotel #{$hotel->id}).");
            }
        }
    }
}
