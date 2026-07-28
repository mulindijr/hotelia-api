<?php

namespace Tests\Feature\Api\V1\Pagination;

use App\Constants\BookingStatus;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\HousekeepingTask;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaginationFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Hotel $hotel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // Create user with super_admin role to bypass all gates
        $this->user = User::factory()->create();
        $this->user->assignRole('super_admin');
        
        $this->hotel = Hotel::factory()->create();
        $this->user->hotels()->attach($this->hotel->id);
    }

    public function test_bookings_pagination_and_filtering(): void
    {
        $guest = Guest::factory()->create();
        $roomType = RoomType::factory()->create(['hotel_id' => $this->hotel->id]);
        $room = Room::factory()->create(['hotel_id' => $this->hotel->id, 'room_type_id' => $roomType->id]);

        // Create 20 bookings
        for ($i = 0; $i < 20; $i++) {
            $booking = Booking::create([
                'booking_reference' => "BK-PAG{$i}",
                'hotel_id' => $this->hotel->id,
                'guest_id' => $guest->id,
                'check_in_date' => now()->addDays($i)->toDateString(),
                'check_out_date' => now()->addDays($i + 2)->toDateString(),
                'status' => $i % 2 === 0 ? BookingStatus::CONFIRMED : BookingStatus::PENDING,
                'total_amount' => 100.00,
            ]);
            $booking->rooms()->attach($room->id, ['price_per_night' => 50.00]);
        }

        // Test pagination limit 5
        $response = $this->actingAs($this->user)
            ->getJson(route('bookings.index', [$this->hotel, 'per_page' => 5]));

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 20)
            ->assertJsonPath('meta.per_page', 5);

        // Test status filtering
        $response = $this->actingAs($this->user)
            ->getJson(route('bookings.index', [$this->hotel, 'filter[status]' => BookingStatus::CONFIRMED]));

        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 10);
    }

    public function test_rooms_pagination_and_filtering(): void
    {
        $roomType = RoomType::factory()->create(['hotel_id' => $this->hotel->id]);

        // Create 20 rooms
        for ($i = 0; $i < 20; $i++) {
            Room::factory()->create([
                'hotel_id' => $this->hotel->id,
                'room_type_id' => $roomType->id,
                'status' => $i % 2 === 0 ? 'available' : 'occupied',
            ]);
        }

        // Test pagination limit 8
        $response = $this->actingAs($this->user)
            ->getJson(route('rooms.index', [$this->hotel, 'per_page' => 8]));

        $response->assertOk()
            ->assertJsonCount(8, 'data')
            ->assertJsonPath('meta.total', 20)
            ->assertJsonPath('meta.per_page', 8);

        // Test status filtering
        $response = $this->actingAs($this->user)
            ->getJson(route('rooms.index', [$this->hotel, 'filter[status]' => 'available']));

        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 10);
    }

    public function test_room_types_pagination_and_filtering(): void
    {
        // Create 20 room types
        for ($i = 0; $i < 20; $i++) {
            RoomType::factory()->create([
                'hotel_id' => $this->hotel->id,
                'name' => "RoomType-{$i}",
            ]);
        }

        // Test pagination limit 10
        $response = $this->actingAs($this->user)
            ->getJson(route('room-types.index', [$this->hotel, 'per_page' => 10]));

        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 20);

        // Test filter name
        $response = $this->actingAs($this->user)
            ->getJson(route('room-types.index', [$this->hotel, 'filter[name]' => 'RoomType-5']));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_services_pagination_and_filtering(): void
    {
        // Create 20 services
        for ($i = 0; $i < 20; $i++) {
            Service::factory()->create([
                'hotel_id' => $this->hotel->id,
                'name' => "Service-{$i}",
                'is_active' => $i % 2 === 0,
            ]);
        }

        // Test pagination limit 6
        $response = $this->actingAs($this->user)
            ->getJson(route('services.index', [$this->hotel, 'per_page' => 6]));

        $response->assertOk()
            ->assertJsonCount(6, 'data')
            ->assertJsonPath('meta.total', 20);

        // Test active filtering
        $response = $this->actingAs($this->user)
            ->getJson(route('services.index', [$this->hotel, 'filter[is_active]' => true]));

        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 10);
    }

    public function test_housekeeping_pagination_and_filtering(): void
    {
        $roomType = RoomType::factory()->create(['hotel_id' => $this->hotel->id]);

        // Create 20 rooms and tasks
        for ($i = 0; $i < 20; $i++) {
            $room = Room::factory()->create([
                'hotel_id' => $this->hotel->id,
                'room_type_id' => $roomType->id,
            ]);
            HousekeepingTask::create([
                'room_id' => $room->id,
                'status' => $i % 2 === 0 ? 'pending' : 'completed',
                'scheduled_at' => now(),
            ]);
        }

        // Test pagination limit 5
        $response = $this->actingAs($this->user)
            ->getJson(route('housekeeping.index', [$this->hotel, 'per_page' => 5]));

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 20);

        // Test status filtering
        $response = $this->actingAs($this->user)
            ->getJson(route('housekeeping.index', [$this->hotel, 'filter[status]' => 'pending']));

        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 10);
    }

    public function test_maintenance_pagination_and_filtering(): void
    {
        $roomType = RoomType::factory()->create(['hotel_id' => $this->hotel->id]);

        // Create 20 rooms and requests
        for ($i = 0; $i < 20; $i++) {
            $room = Room::factory()->create([
                'hotel_id' => $this->hotel->id,
                'room_type_id' => $roomType->id,
            ]);
            MaintenanceRequest::create([
                'room_id' => $room->id,
                'reported_by' => $this->user->id,
                'description' => 'Fix something',
                'priority' => $i % 2 === 0 ? 'high' : 'low',
                'status' => 'pending',
            ]);
        }

        // Test pagination limit 5
        $response = $this->actingAs($this->user)
            ->getJson(route('maintenance.index', [$this->hotel, 'per_page' => 5]));

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 20);

        // Test priority filtering
        $response = $this->actingAs($this->user)
            ->getJson(route('maintenance.index', [$this->hotel, 'filter[priority]' => 'high']));

        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 10);
    }

    public function test_payments_pagination_and_filtering(): void
    {
        $guest = Guest::factory()->create();
        $roomType = RoomType::factory()->create(['hotel_id' => $this->hotel->id]);
        $room = Room::factory()->create(['hotel_id' => $this->hotel->id, 'room_type_id' => $roomType->id]);

        $booking = Booking::create([
            'booking_reference' => 'BK-PAYMENTS',
            'hotel_id' => $this->hotel->id,
            'guest_id' => $guest->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDays(2)->toDateString(),
            'status' => BookingStatus::CONFIRMED,
            'total_amount' => 100.00,
        ]);
        $booking->rooms()->attach($room->id, ['price_per_night' => 50.00]);

        // Create 20 payments
        for ($i = 0; $i < 20; $i++) {
            Payment::create([
                'booking_id' => $booking->id,
                'amount' => 10.00,
                'payment_method' => $i % 2 === 0 ? 'cash' : 'mpesa',
                'status' => 'completed',
            ]);
        }

        // Test pagination limit 5
        $response = $this->actingAs($this->user)
            ->getJson(route('bookings.payments.index', [$this->hotel, $booking, 'per_page' => 5]));

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 20);

        // Test payment method filtering
        $response = $this->actingAs($this->user)
            ->getJson(route('bookings.payments.index', [$this->hotel, $booking, 'filter[payment_method]' => 'cash']));

        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 10);
    }
}
