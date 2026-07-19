<?php

namespace Tests\Feature\Api\V1\Reports;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\HousekeepingTask;
use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomType;
use Tests\ApiTestCase;
use Tests\Traits\InteractsWithHotels;

class ReportTest extends ApiTestCase
{
    use InteractsWithHotels;

    /**
     * Test daily operational statistics dashboard calculations.
     */
    public function test_user_can_view_dashboard_stats(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);

        // Create rooms with statuses
        Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'status' => 'occupied',
        ]);
        Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);
        $roomCleaning = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'status' => 'cleaning',
        ]);

        // Housekeeping task
        HousekeepingTask::factory()->create([
            'room_id' => $roomCleaning->id,
            'status' => 'pending',
        ]);

        // Maintenance request
        MaintenanceRequest::factory()->create([
            'room_id' => $roomCleaning->id,
            'status' => 'open',
        ]);

        $response = $this->getJson(route('hotels.reports.dashboard', $hotel));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_rooms', 3)
            ->assertJsonPath('data.occupied_rooms', 1)
            ->assertJsonPath('data.occupancy_rate', 33.33)
            ->assertJsonPath('data.housekeeping_tasks.pending', 1)
            ->assertJsonPath('data.active_maintenance_requests', 1);
    }

    /**
     * Test revenue calculations.
     */
    public function test_user_can_view_revenue_stats(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);
        $hotel->settings()->create(['tax_rate' => 0.00]);

        $guest = Guest::factory()->create();
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id, 'base_price' => 100.00]);
        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
        ]);

        // Create booking (1 night stay -> 100 price)
        $booking = Booking::factory()->create([
            'hotel_id' => $hotel->id,
            'guest_id' => $guest->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDays(1)->toDateString(),
            'status' => 'checked_in',
        ]);
        $booking->rooms()->attach($room->id, ['price_per_night' => 100.00]);

        // Add completed payment: 100
        Payment::factory()->create([
            'booking_id' => $booking->id,
            'amount' => 100.00,
            'status' => 'completed',
            'payment_method' => 'mpesa',
        ]);

        // Generate invoice
        Invoice::factory()->create([
            'booking_id' => $booking->id,
            'subtotal' => 100.00,
            'tax_amount' => 0.00,
            'total_amount' => 100.00,
            'status' => 'paid',
        ]);

        $response = $this->getJson(route('hotels.reports.revenue', $hotel));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.payments_collected', 100)
            ->assertJsonPath('data.total_invoiced', 100)
            ->assertJsonPath('data.adr', 100)
            ->assertJsonPath('data.payment_methods.mpesa', 100);
    }

    /**
     * Test reports security isolation.
     */
    public function test_reports_security_isolations(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = Hotel::factory()->create(); // Unassigned hotel

        $response = $this->getJson(route('hotels.reports.dashboard', $hotel));

        $response->assertStatus(403);
    }
}
