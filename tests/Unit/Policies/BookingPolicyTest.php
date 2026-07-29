<?php

namespace Tests\Unit\Policies;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\User;
use App\Policies\BookingPolicy;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected BookingPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->policy = new BookingPolicy;
    }

    public function test_super_admin_bypasses_all_checks(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $this->assertTrue($this->policy->before($superAdmin, 'viewAny'));
    }

    public function test_view_any_returns_true_for_user_belonging_to_hotel(): void
    {
        $user = User::factory()->create();
        $hotel = Hotel::factory()->create();
        $hotel->users()->attach($user->id);

        $this->assertTrue($this->policy->viewAny($user, $hotel));
    }

    public function test_create_returns_true_for_user_belonging_to_hotel(): void
    {
        $user = User::factory()->create();
        $hotel = Hotel::factory()->create();
        $hotel->users()->attach($user->id);

        $this->assertTrue($this->policy->create($user, $hotel));
    }

    public function test_view_returns_false_when_booking_does_not_belong_to_hotel_context(): void
    {
        $user = User::factory()->create();
        $hotelA = Hotel::factory()->create();
        $hotelB = Hotel::factory()->create();
        $hotelA->users()->attach($user->id);

        $bookingB = Booking::factory()->create(['hotel_id' => $hotelB->id]);

        $this->assertFalse($this->policy->view($user, $bookingB, $hotelA));
    }

    public function test_update_returns_false_when_booking_does_not_belong_to_hotel_context(): void
    {
        $user = User::factory()->create();
        $hotelA = Hotel::factory()->create();
        $hotelB = Hotel::factory()->create();
        $hotelA->users()->attach($user->id);

        $bookingB = Booking::factory()->create(['hotel_id' => $hotelB->id]);

        $this->assertFalse($this->policy->update($user, $bookingB, $hotelA));
    }

    public function test_cancel_returns_false_when_booking_does_not_belong_to_hotel_context(): void
    {
        $user = User::factory()->create();
        $hotelA = Hotel::factory()->create();
        $hotelB = Hotel::factory()->create();
        $hotelA->users()->attach($user->id);

        $bookingB = Booking::factory()->create(['hotel_id' => $hotelB->id]);

        $this->assertFalse($this->policy->cancel($user, $bookingB, $hotelA));
    }

    public function test_check_in_returns_false_when_booking_does_not_belong_to_hotel_context(): void
    {
        $user = User::factory()->create();
        $hotelA = Hotel::factory()->create();
        $hotelB = Hotel::factory()->create();
        $hotelA->users()->attach($user->id);

        $bookingB = Booking::factory()->create(['hotel_id' => $hotelB->id]);

        $this->assertFalse($this->policy->checkIn($user, $bookingB, $hotelA));
    }

    public function test_check_out_returns_false_when_booking_does_not_belong_to_hotel_context(): void
    {
        $user = User::factory()->create();
        $hotelA = Hotel::factory()->create();
        $hotelB = Hotel::factory()->create();
        $hotelA->users()->attach($user->id);

        $bookingB = Booking::factory()->create(['hotel_id' => $hotelB->id]);

        $this->assertFalse($this->policy->checkOut($user, $bookingB, $hotelA));
    }
}
