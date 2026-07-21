<?php

namespace Tests\Feature\Jobs;

use App\Constants\BookingStatus;
use App\Jobs\AutoCancelStaleBookings;
use App\Models\Booking;

use App\Models\Guest;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\TestCase;

class AutoCancelStaleBookingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_cancel_stale_bookings_cancels_old_pending_bookings(): void
    {
        $hotel = Hotel::factory()->create();
        $guest = Guest::factory()->create(['hotel_id' => $hotel->id]);
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);
        $room = Room::factory()->create(['hotel_id' => $hotel->id, 'room_type_id' => $roomType->id]);

        // Stale booking (created 3 hours ago)
        $staleBooking = Booking::create([
            'booking_reference' => 'BK-STALE01',
            'hotel_id' => $hotel->id,
            'guest_id' => $guest->id,
            'check_in_date' => now()->addDay()->toDateString(),
            'check_out_date' => now()->addDays(3)->toDateString(),
            'total_amount' => 200.00,
            'status' => BookingStatus::PENDING,
            'created_at' => now()->subHours(3),
        ]);
        $staleBooking->rooms()->attach($room->id, ['price_per_night' => 100.00]);

        // Recent pending booking (created 10 minutes ago)
        $recentBooking = Booking::create([
            'booking_reference' => 'BK-RECENT01',
            'hotel_id' => $hotel->id,
            'guest_id' => $guest->id,
            'check_in_date' => now()->addDay()->toDateString(),
            'check_out_date' => now()->addDays(3)->toDateString(),
            'total_amount' => 200.00,
            'status' => BookingStatus::PENDING,
            'created_at' => now()->subMinutes(10),
        ]);
        $recentBooking->rooms()->attach($room->id, ['price_per_night' => 100.00]);

        // Dispatch job
        AutoCancelStaleBookings::dispatchSync();

        $this->assertEquals(BookingStatus::CANCELLED, $staleBooking->fresh()->status);
        $this->assertEquals(BookingStatus::PENDING, $recentBooking->fresh()->status);
    }
}
