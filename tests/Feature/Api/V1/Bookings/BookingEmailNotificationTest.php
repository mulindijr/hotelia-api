<?php

namespace Tests\Feature\Api\V1\Bookings;

use App\Constants\BookingStatus;
use App\Constants\RoomStatus;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use App\Models\RoomType;
use App\Mail\Bookings\BookingCancelledMail;
use App\Mail\Bookings\BookingConfirmationMail;
use App\Mail\Bookings\BookingNoShowMail;
use App\Mail\Bookings\BookingUpdatedMail;
use Illuminate\Support\Facades\Mail;
use Tests\ApiTestCase;
use Tests\Traits\InteractsWithHotels;

class BookingEmailNotificationTest extends ApiTestCase
{
    use InteractsWithHotels;

    public function test_booking_creation_sends_confirmation_email_to_guest(): void
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

        $response = $this->postJson(route('bookings.store', $hotel), [
            'guest_id' => $guest->id,
            'check_in_date' => now()->addDays(2)->toDateString(),
            'check_out_date' => now()->addDays(4)->toDateString(),
            'rooms' => [$room->id],
            'status' => BookingStatus::CONFIRMED,
        ]);

        $response->assertCreated();

        Mail::assertSent(BookingConfirmationMail::class, function ($mail) use ($guest) {
            return $mail->hasTo($guest->email);
        });
    }

    public function test_booking_modification_sends_updated_email_to_guest(): void
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
        $booking->rooms()->attach($room->id, ['price_per_night' => $roomType->price_per_night]);

        $response = $this->putJson(route('bookings.update', [$hotel, $booking]), [
            'check_in_date' => now()->addDays(3)->toDateString(),
            'check_out_date' => now()->addDays(5)->toDateString(),
            'rooms' => [$room->id],
        ]);

        $response->assertOk();

        Mail::assertSent(BookingUpdatedMail::class, function ($mail) use ($guest) {
            return $mail->hasTo($guest->email);
        });
    }

    public function test_booking_cancellation_sends_cancelled_email_to_guest(): void
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
            'check_in_date' => now()->addDays(5),
            'check_out_date' => now()->addDays(7),
        ]);
        $booking->rooms()->attach($room->id, ['price_per_night' => $roomType->price_per_night]);

        $response = $this->postJson(route('bookings.cancel', [$hotel, $booking]));

        $response->assertOk();

        Mail::assertSent(BookingCancelledMail::class, function ($mail) use ($guest) {
            return $mail->hasTo($guest->email);
        });
    }

    public function test_booking_noshow_sends_noshow_email_to_guest(): void
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
            'check_in_date' => now()->subDays(1),
            'check_out_date' => now()->addDays(2),
        ]);
        $booking->rooms()->attach($room->id, ['price_per_night' => $roomType->price_per_night]);

        $response = $this->postJson(route('bookings.no-show', [$hotel, $booking]));

        $response->assertOk();

        Mail::assertSent(BookingNoShowMail::class, function ($mail) use ($guest) {
            return $mail->hasTo($guest->email);
        });
    }
}
