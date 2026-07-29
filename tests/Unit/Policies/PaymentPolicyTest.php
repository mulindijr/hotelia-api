<?php

namespace Tests\Unit\Policies;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Payment;
use App\Models\User;
use App\Policies\PaymentPolicy;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->policy = new PaymentPolicy;
    }

    public function test_super_admin_bypasses_all_checks(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $this->assertTrue($this->policy->before($superAdmin, 'view'));
    }

    public function test_user_can_view_payment_in_same_hotel(): void
    {
        $hotel = Hotel::factory()->create();
        $user = User::factory()->create();
        $user->hotels()->attach($hotel->id);

        $booking = Booking::factory()->create(['hotel_id' => $hotel->id]);
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => 100.00,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        $this->assertTrue($this->policy->view($user, $payment));
    }

    public function test_user_cannot_view_payment_in_different_hotel(): void
    {
        $hotelA = Hotel::factory()->create();
        $hotelB = Hotel::factory()->create();

        $user = User::factory()->create();
        $user->hotels()->attach($hotelA->id);

        $booking = Booking::factory()->create(['hotel_id' => $hotelB->id]);
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => 100.00,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        $this->assertFalse($this->policy->view($user, $payment));
    }

    public function test_user_can_update_payment_in_same_hotel(): void
    {
        $hotel = Hotel::factory()->create();
        $user = User::factory()->create();
        $user->hotels()->attach($hotel->id);

        $booking = Booking::factory()->create(['hotel_id' => $hotel->id]);
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => 100.00,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        $this->assertTrue($this->policy->update($user, $payment));
    }

    public function test_user_cannot_update_payment_in_different_hotel(): void
    {
        $hotelA = Hotel::factory()->create();
        $hotelB = Hotel::factory()->create();

        $user = User::factory()->create();
        $user->hotels()->attach($hotelA->id);

        $booking = Booking::factory()->create(['hotel_id' => $hotelB->id]);
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => 100.00,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        $this->assertFalse($this->policy->update($user, $payment));
    }
}
