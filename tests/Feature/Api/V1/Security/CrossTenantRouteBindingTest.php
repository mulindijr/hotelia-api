<?php

namespace Tests\Feature\Api\V1\Security;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\HousekeepingTask;
use App\Models\MaintenanceRequest;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Service;
use App\Models\User;
use Tests\ApiTestCase;
use Tests\Traits\InteractsWithHotels;

class CrossTenantRouteBindingTest extends ApiTestCase
{
    use InteractsWithHotels;

    /**
     * Test scoped route model binding returns 404 for all nested resources when cross-tenant ID is supplied.
     */
    public function test_scoped_route_binding_rejects_cross_tenant_resources(): void
    {
        $user = $this->actingAsRole('super_admin');

        $hotelA = $this->createHotelForUser($user);
        $hotelB = Hotel::factory()->create();

        // Create resources belonging to Hotel B
        $roomTypeB = RoomType::factory()->create(['hotel_id' => $hotelB->id]);
        $roomB = Room::factory()->create(['hotel_id' => $hotelB->id, 'room_type_id' => $roomTypeB->id]);
        $bookingB = Booking::factory()->create(['hotel_id' => $hotelB->id]);
        $serviceB = Service::factory()->create(['hotel_id' => $hotelB->id]);
        $taskB = HousekeepingTask::factory()->create(['room_id' => $roomB->id]);
        $maintenanceB = MaintenanceRequest::factory()->create(['room_id' => $roomB->id]);

        // 1. Room Types
        $this->getJson(route('room-types.show', [$hotelA, $roomTypeB]))->assertNotFound();
        $this->putJson(route('room-types.update', [$hotelA, $roomTypeB]), ['name' => 'Hack'])->assertNotFound();
        $this->deleteJson(route('room-types.destroy', [$hotelA, $roomTypeB]))->assertNotFound();

        // 2. Rooms
        $this->getJson(route('rooms.show', [$hotelA, $roomB]))->assertNotFound();
        $this->putJson(route('rooms.update', [$hotelA, $roomB]), ['room_number' => '999'])->assertNotFound();
        $this->deleteJson(route('rooms.destroy', [$hotelA, $roomB]))->assertNotFound();

        // 3. Bookings
        $this->getJson(route('bookings.show', [$hotelA, $bookingB]))->assertNotFound();
        $this->putJson(route('bookings.update', [$hotelA, $bookingB]), [])->assertNotFound();

        // 4. Services
        $this->getJson(route('services.show', [$hotelA, $serviceB]))->assertNotFound();
        $this->putJson(route('services.update', [$hotelA, $serviceB]), ['name' => 'Hack'])->assertNotFound();
        $this->deleteJson(route('services.destroy', [$hotelA, $serviceB]))->assertNotFound();

        // 5. Housekeeping Tasks
        $this->getJson(route('housekeeping.show', [$hotelA, $taskB]))->assertNotFound();
        $this->putJson(route('housekeeping.update', [$hotelA, $taskB]), [])->assertNotFound();
        $this->deleteJson(route('housekeeping.destroy', [$hotelA, $taskB]))->assertNotFound();

        // 6. Maintenance Requests
        $this->getJson(route('maintenance.show', [$hotelA, $maintenanceB]))->assertNotFound();
        $this->putJson(route('maintenance.update', [$hotelA, $maintenanceB]), [])->assertNotFound();
        $this->deleteJson(route('maintenance.destroy', [$hotelA, $maintenanceB]))->assertNotFound();
    }
}
