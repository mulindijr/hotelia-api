<?php

namespace Tests\Feature\Api\V1\Billing;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Service;
use Tests\ApiTestCase;
use Tests\Traits\InteractsWithHotels;

class BillingTest extends ApiTestCase
{
    use InteractsWithHotels;

    /**
     * Test retrieving and auto-generating invoice.
     */
    public function test_user_can_view_and_generate_invoice(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        // Create hotel setting with tax rate of 16%
        $hotel->settings()->create([
            'tax_rate' => 16.00,
            'currency' => 'USD',
            'timezone' => 'UTC',
        ]);

        $guest = Guest::factory()->create();
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id, 'base_price' => 100.00]);
        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
        ]);
        $service = Service::factory()->create(['hotel_id' => $hotel->id, 'price' => 25.00]);

        $booking = Booking::factory()->create([
            'hotel_id' => $hotel->id,
            'guest_id' => $guest->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDays(2)->toDateString(), // 2 nights
            'total_amount' => 250.00,
        ]);
        $booking->rooms()->attach($room->id, ['price_per_night' => 100.00]);
        $booking->services()->attach($service->id, ['quantity' => 2, 'price' => 25.00]);

        // Expected subtotal: (100 * 2) + (25 * 2) = 250
        // Expected tax: 250 * 0.16 = 40
        // Expected total: 290

        $response = $this->getJson(route('bookings.invoice.show', [$hotel, $booking]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.subtotal', 250)
            ->assertJsonPath('data.tax_amount', 40)
            ->assertJsonPath('data.total_amount', 290)
            ->assertJsonPath('data.status', 'unpaid')
            ->assertJsonCount(2, 'data.items');
    }

    /**
     * Test invoice items regeneration.
     */
    public function test_invoice_regeneration(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $guest = Guest::factory()->create();
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id, 'base_price' => 100.00]);
        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
        ]);

        $booking = Booking::factory()->create([
            'hotel_id' => $hotel->id,
            'guest_id' => $guest->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDays(1)->toDateString(), // 1 night
            'total_amount' => 100.00,
        ]);
        $booking->rooms()->attach($room->id, ['price_per_night' => 100.00]);

        // Generate initial invoice
        $this->getJson(route('bookings.invoice.show', [$hotel, $booking]))->assertOk();

        // Attach a service later
        $service = Service::factory()->create(['hotel_id' => $hotel->id, 'price' => 50.00]);
        $booking->services()->attach($service->id, ['quantity' => 1, 'price' => 50.00]);

        // Trigger manual regeneration
        $response = $this->postJson(route('bookings.invoice.regenerate', [$hotel, $booking]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.subtotal', 150)
            ->assertJsonCount(2, 'data.items');
    }

    /**
     * Test logging payment updates invoice status.
     */
    public function test_user_can_log_payment_and_invoice_syncs(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);
        // Create hotel setting with tax rate of 0%
        $hotel->settings()->create([
            'tax_rate' => 0.00,
            'currency' => 'USD',
            'timezone' => 'UTC',
        ]);

        $guest = Guest::factory()->create();
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id, 'base_price' => 100.00]);
        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
        ]);

        $booking = Booking::factory()->create([
            'hotel_id' => $hotel->id,
            'guest_id' => $guest->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDays(1)->toDateString(), // 1 night -> 100 total
        ]);
        $booking->rooms()->attach($room->id, ['price_per_night' => 100.00]);

        // Generate invoice: 100
        $this->getJson(route('bookings.invoice.show', [$hotel, $booking]))->assertOk();

        // Log partial payment: 40
        $response = $this->postJson(route('bookings.payments.store', [$hotel, $booking]), [
            'amount' => 40.00,
            'payment_method' => 'cash',
        ]);

        $response->assertCreated();

        // Assert invoice is partial
        $this->getJson(route('bookings.invoice.show', [$hotel, $booking]))
            ->assertJsonPath('data.status', 'partial');

        // Log final payment: 60
        $this->postJson(route('bookings.payments.store', [$hotel, $booking]), [
            'amount' => 60.00,
            'payment_method' => 'mpesa',
        ])->assertCreated();

        // Assert invoice is paid
        $this->getJson(route('bookings.invoice.show', [$hotel, $booking]))
            ->assertJsonPath('data.status', 'paid');
    }

    /**
     * Test unauthorized hotel access limits.
     */
    public function test_unauthorized_user_cannot_view_billing(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = Hotel::factory()->create(); // Unassigned hotel

        $booking = Booking::factory()->create(['hotel_id' => $hotel->id]);

        $response = $this->getJson(route('bookings.invoice.show', [$hotel, $booking]));

        $response->assertStatus(403);
    }
}
