<?php

namespace App\Jobs;

use App\Constants\Permissions;
use App\Constants\RoomStatus;
use App\Models\Room;
use App\Notifications\Housekeeping\StuckInCleaningNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
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
        $threshold = now()->subMinutes($minutes)->toDateTimeString();

        // Query rooms stuck in cleaning without eager loading all hotel users upfront
        $stuckRooms = Room::where('status', RoomStatus::CLEANING)
            ->where('updated_at', '<=', $threshold)
            ->with('hotel')
            ->get();

        if ($stuckRooms->isEmpty()) {
            return;
        }

        // Group by hotel to eliminate N+1 queries for recipient lookup
        $roomsByHotel = $stuckRooms->groupBy('hotel_id');

        foreach ($roomsByHotel as $hotelId => $rooms) {
            $hotel = $rooms->first()?->hotel;
            if (!$hotel) {
                continue;
            }

            // Permission-based recipient selection (decoupled from hardcoded role names)
            $recipients = $hotel->users()
                ->permission([Permissions::VIEW_HOUSEKEEPING, Permissions::MANAGE_HOUSEKEEPING])
                ->get();

            if ($recipients->isEmpty()) {
                continue;
            }

            foreach ($rooms as $room) {
                // Deduplication: Cooldown check to prevent repeated spam notifications for the same stuck room
                $cacheKey = "stuck_cleaning_notified:room:{$room->id}";
                if (Cache::has($cacheKey)) {
                    continue;
                }

                Notification::send($recipients, new StuckInCleaningNotification($room, $minutes));

                Cache::put($cacheKey, true, now()->addMinutes($minutes));

                Log::info("Sent StuckInCleaningNotification for Room #{$room->room_number} (Hotel #{$hotel->id}).");
            }
        }
    }
}

