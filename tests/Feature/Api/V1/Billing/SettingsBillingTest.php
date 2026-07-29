<?php

namespace Tests\Feature\Api\V1\Billing;

use App\Constants\BookingStatus;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\Hotel\HotelSettingService;
use Carbon\Carbon;
use Tests\ApiTestCase;
use Tests\Traits\InteractsWithHotels;

class SettingsBillingTest extends ApiTestCase
{
    use InteractsWithHotels;

    public function test_early_checkin_fee_calculation(): void
    {
        $user = $this->actingAsRole('super_admin');
        $hotel = $this->createHotelForUser($user);

        // Set settings
        $settings = app(HotelSettingService::class)->getSettings($hotel);
        app(HotelSettingService::class)->update($settings, [
            'check_in_time' => '14:00',
            'early_checkin_fee' => 50.00,
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
            'check_in_date' => now()->addDay()->toDateString(),
            'check_out_date' => now()->addDays(3)->toDateString(), // 2 nights
            'status' => BookingStatus::CONFIRMED,
        ]);
        $booking->rooms()->attach($room->id, ['price_per_night' => 100.00]);

        // Mock time to early check-in (10:00 AM on check-in day)
        $earlyCheckInTime = Carbon::parse($booking->check_in_date->toDateString().' 10:00:00');
        Carbon::setTestNow($earlyCheckInTime);

        // Perform check-in
        $this->postJson(route('bookings.check-in', [$hotel, $booking]))->assertOk();

        // Retrieve invoice and check early check-in fee is added
        $response = $this->getJson(route('bookings.invoice.show', [$hotel, $booking]));
        $response->assertOk();

        // 2 nights * 100 = 200 subtotal before fees. early check-in fee is 50. New subtotal = 250.
        // Tax is 16% on 250 = 40. Total = 290.
        $response->assertJsonPath('data.subtotal', 250)
            ->assertJsonPath('data.total_amount', 290);

        $this->assertDatabaseHas('invoice_items', [
            'description' => 'Early Check-in Fee',
            'total_price' => 50.00,
        ]);

        Carbon::setTestNow(); // Reset test time
    }

    public function test_late_checkout_fee_calculation(): void
    {
        $user = $this->actingAsRole('super_admin');
        $hotel = $this->createHotelForUser($user);

        // Set settings
        $settings = app(HotelSettingService::class)->getSettings($hotel);
        app(HotelSettingService::class)->update($settings, [
            'check_out_time' => '11:00',
            'default_checkout_grace_minutes' => 30,
            'late_checkout_fee' => 75.00,
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
            'check_in_date' => now()->subDays(2)->toDateString(),
            'check_out_date' => now()->toDateString(),
            'status' => BookingStatus::CHECKED_IN, // Must be checked-in to check-out
        ]);
        $booking->rooms()->attach($room->id, ['price_per_night' => 100.00]);

        // Mock time to late check-out (12:00 PM on check-out day, which is > 11:30 grace window limit)
        $lateCheckOutTime = Carbon::parse($booking->check_out_date->toDateString().' 12:00:00');
        Carbon::setTestNow($lateCheckOutTime);

        // Perform check-out
        $this->postJson(route('bookings.check-out', [$hotel, $booking]))->assertOk();

        // Retrieve invoice and check late check-out fee is added
        $response = $this->getJson(route('bookings.invoice.show', [$hotel, $booking]));
        $response->assertOk();

        // 2 nights * 100 = 200 subtotal before fees. late check-out fee is 75. New subtotal = 275.
        // Tax is 16% on 275 = 44. Total = 319.
        $response->assertJsonPath('data.subtotal', 275)
            ->assertJsonPath('data.total_amount', 319);

        $this->assertDatabaseHas('invoice_items', [
            'description' => 'Late Check-out Fee',
            'total_price' => 75.00,
        ]);

        Carbon::setTestNow(); // Reset test time
    }
}
