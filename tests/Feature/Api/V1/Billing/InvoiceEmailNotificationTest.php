<?php

namespace Tests\Feature\Api\V1\Billing;

use App\Constants\BookingStatus;
use App\Constants\RoomStatus;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use App\Models\RoomType;
use App\Mail\Billing\InvoiceGeneratedMail;
use App\Mail\Billing\InvoicePaidMail;
use App\Services\Billing\BillingService;
use Illuminate\Support\Facades\Mail;
use Tests\ApiTestCase;
use Tests\Traits\InteractsWithHotels;

class InvoiceEmailNotificationTest extends ApiTestCase
{
    use InteractsWithHotels;

    public function test_invoice_generation_sends_email_to_guest(): void
    {
        Mail::fake();

        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $guest = Guest::factory()->create([
            'email' => 'guest@example.com',
        ]);
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);
        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'status' => RoomStatus::AVAILABLE,
        ]);

        $booking = Booking::factory()->create([
            'hotel_id' => $hotel->id,
            'guest_id' => $guest->id,
            'status' => BookingStatus::CONFIRMED,
            'check_in_date' => now()->addDays(2),
            'check_out_date' => now()->addDays(4),
        ]);
        $booking->rooms()->attach($room->id, ['price_per_night' => $roomType->base_price]);

        $billingService = app(BillingService::class);
        $invoice = $billingService->regenerateInvoice($booking);

        Mail::assertSent(InvoiceGeneratedMail::class, function ($mail) use ($guest) {
            return $mail->hasTo($guest->email);
        });
    }

    public function test_invoice_payment_sends_email_to_guest(): void
    {
        Mail::fake();

        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $guest = Guest::factory()->create([
            'email' => 'guest@example.com',
        ]);
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);
        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'status' => RoomStatus::AVAILABLE,
        ]);

        $booking = Booking::factory()->create([
            'hotel_id' => $hotel->id,
            'guest_id' => $guest->id,
            'status' => BookingStatus::CONFIRMED,
            'check_in_date' => now()->addDays(2),
            'check_out_date' => now()->addDays(4),
        ]);
        $booking->rooms()->attach($room->id, ['price_per_night' => $roomType->base_price]);

        $billingService = app(BillingService::class);
        $invoice = $billingService->regenerateInvoice($booking);

        $response = $this->postJson(route('bookings.payments.store', [$hotel, $booking]), [
            'amount' => $invoice->total_amount,
            'payment_method' => 'cash',
        ]);

        $response->assertCreated();

        Mail::assertSent(InvoicePaidMail::class, function ($mail) use ($guest) {
            return $mail->hasTo($guest->email);
        });
    }
}
