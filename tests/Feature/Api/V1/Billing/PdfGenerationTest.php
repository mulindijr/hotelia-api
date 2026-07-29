<?php

namespace Tests\Feature\Api\V1\Billing;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomType;
use Tests\ApiTestCase;
use Tests\Traits\InteractsWithHotels;

class PdfGenerationTest extends ApiTestCase
{
    use InteractsWithHotels;

    public function test_user_can_download_invoice_pdf(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id, 'base_price' => 150.00]);
        $room = Room::factory()->create(['hotel_id' => $hotel->id, 'room_type_id' => $roomType->id]);
        $guest = Guest::factory()->create();

        $booking = Booking::create([
            'booking_reference' => 'BK-PDF101',
            'hotel_id' => $hotel->id,
            'guest_id' => $guest->id,
            'check_in_date' => '2026-09-01',
            'check_out_date' => '2026-09-03',
            'total_amount' => 300.00,
            'status' => 'confirmed',
        ]);
        $booking->rooms()->attach($room->id, ['price_per_night' => 150.00]);

        $response = $this->getJson("/api/v1/hotels/{$hotel->id}/bookings/{$booking->id}/invoice/pdf");

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_user_can_download_payment_receipt_pdf(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $guest = Guest::factory()->create();
        $booking = Booking::create([
            'booking_reference' => 'BK-PDF102',
            'hotel_id' => $hotel->id,
            'guest_id' => $guest->id,
            'check_in_date' => '2026-09-01',
            'check_out_date' => '2026-09-03',
            'total_amount' => 300.00,
            'status' => 'confirmed',
        ]);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => 150.00,
            'payment_method' => 'card',
            'transaction_reference' => 'TXN-998877',
            'status' => 'completed',
        ]);

        $response = $this->getJson("/api/v1/hotels/{$hotel->id}/bookings/{$booking->id}/payments/{$payment->id}/receipt/pdf");

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_user_cannot_download_unassigned_hotel_invoice_pdf(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = Hotel::factory()->create(); // Unassigned hotel

        $guest = Guest::factory()->create();
        $booking = Booking::create([
            'booking_reference' => 'BK-PDF103',
            'hotel_id' => $hotel->id,
            'guest_id' => $guest->id,
            'check_in_date' => '2026-09-01',
            'check_out_date' => '2026-09-03',
            'total_amount' => 300.00,
            'status' => 'confirmed',
        ]);

        $response = $this->getJson("/api/v1/hotels/{$hotel->id}/bookings/{$booking->id}/invoice/pdf");

        $response->assertStatus(403);
    }
}
