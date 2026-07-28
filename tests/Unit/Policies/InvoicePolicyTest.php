<?php

namespace Tests\Unit\Policies;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Invoice;
use App\Models\User;
use App\Policies\InvoicePolicy;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoicePolicyTest extends TestCase
{
    use RefreshDatabase;

    protected InvoicePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->policy = new InvoicePolicy();
    }

    public function test_super_admin_bypasses_all_checks(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $this->assertTrue($this->policy->before($superAdmin, 'view'));
    }

    public function test_user_can_view_invoice_in_same_hotel(): void
    {
        $hotel = Hotel::factory()->create();
        $user = User::factory()->create();
        $user->hotels()->attach($hotel->id);

        $booking = Booking::factory()->create(['hotel_id' => $hotel->id]);
        $invoice = Invoice::create([
            'invoice_number' => 'INV-1234',
            'booking_id' => $booking->id,
            'subtotal' => 100.00,
            'tax_amount' => 10.00,
            'total_amount' => 110.00,
            'status' => 'unpaid',
        ]);

        $this->assertTrue($this->policy->view($user, $invoice));
    }

    public function test_user_cannot_view_invoice_in_different_hotel(): void
    {
        $hotelA = Hotel::factory()->create();
        $hotelB = Hotel::factory()->create();

        $user = User::factory()->create();
        $user->hotels()->attach($hotelA->id);

        $booking = Booking::factory()->create(['hotel_id' => $hotelB->id]);
        $invoice = Invoice::create([
            'invoice_number' => 'INV-1234',
            'booking_id' => $booking->id,
            'subtotal' => 100.00,
            'tax_amount' => 10.00,
            'total_amount' => 110.00,
            'status' => 'unpaid',
        ]);

        $this->assertFalse($this->policy->view($user, $invoice));
    }

    public function test_user_can_manage_invoice_in_same_hotel(): void
    {
        $hotel = Hotel::factory()->create();
        $user = User::factory()->create();
        $user->hotels()->attach($hotel->id);

        $booking = Booking::factory()->create(['hotel_id' => $hotel->id]);
        $invoice = Invoice::create([
            'invoice_number' => 'INV-1234',
            'booking_id' => $booking->id,
            'subtotal' => 100.00,
            'tax_amount' => 10.00,
            'total_amount' => 110.00,
            'status' => 'unpaid',
        ]);

        $this->assertTrue($this->policy->manage($user, $invoice));
    }

    public function test_user_cannot_manage_invoice_in_different_hotel(): void
    {
        $hotelA = Hotel::factory()->create();
        $hotelB = Hotel::factory()->create();

        $user = User::factory()->create();
        $user->hotels()->attach($hotelA->id);

        $booking = Booking::factory()->create(['hotel_id' => $hotelB->id]);
        $invoice = Invoice::create([
            'invoice_number' => 'INV-1234',
            'booking_id' => $booking->id,
            'subtotal' => 100.00,
            'tax_amount' => 10.00,
            'total_amount' => 110.00,
            'status' => 'unpaid',
        ]);

        $this->assertFalse($this->policy->manage($user, $invoice));
    }
}
