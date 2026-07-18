<?php

namespace Tests\Feature\Api\V1\Services;

use App\Models\Hotel;
use App\Models\Service;
use Tests\ApiTestCase;
use Tests\Traits\InteractsWithHotels;

class ServiceTest extends ApiTestCase
{
    use InteractsWithHotels;

    /**
     * Test viewing services list by hotel manager.
     */
    public function test_hotel_manager_can_view_services_list(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        Service::factory()->count(2)->create(['hotel_id' => $hotel->id]);

        $response = $this->getJson(route('services.index', $hotel));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    }

    /**
     * Test viewing services list by receptionist.
     */
    public function test_receptionist_can_view_services_list(): void
    {
        $user = $this->actingAsRole('receptionist');
        $hotel = $this->createHotelForUser($user);

        Service::factory()->count(2)->create(['hotel_id' => $hotel->id]);

        $response = $this->getJson(route('services.index', $hotel));

        $response->assertOk()
            ->assertJsonPath('success', true);
    }

    /**
     * Test scoping check.
     */
    public function test_user_cannot_view_unassigned_hotel_services(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = Hotel::factory()->create(); // Unassigned hotel

        $response = $this->getJson(route('services.index', $hotel));

        $response->assertStatus(403);
    }

    /**
     * Test creating a service.
     */
    public function test_hotel_manager_can_create_service(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $payload = [
            'name' => 'Premium Massage Treatment',
            'description' => 'A full body massage.',
            'price' => 85.00,
            'is_active' => true,
        ];

        $response = $this->postJson(route('services.store', $hotel), $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Premium Massage Treatment');

        $this->assertDatabaseHas('services', [
            'hotel_id' => $hotel->id,
            'name' => 'Premium Massage Treatment',
        ]);
    }

    /**
     * Test receptionist lacks create permission.
     */
    public function test_receptionist_cannot_create_service(): void
    {
        $user = $this->actingAsRole('receptionist');
        $hotel = $this->createHotelForUser($user);

        $payload = [
            'name' => 'Premium Massage Treatment',
            'price' => 85.00,
        ];

        $response = $this->postJson(route('services.store', $hotel), $payload);

        $response->assertStatus(403);
    }

    /**
     * Test updating a service.
     */
    public function test_user_can_update_service(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);
        $service = Service::factory()->create(['hotel_id' => $hotel->id]);

        $payload = [
            'name' => 'Updated Service Name',
            'price' => 99.99,
        ];

        $response = $this->putJson(route('services.update', [$hotel, $service]), $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Updated Service Name');

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Updated Service Name',
            'price' => 99.99,
        ]);
    }

    /**
     * Test deleting a service.
     */
    public function test_user_can_delete_service(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);
        $service = Service::factory()->create(['hotel_id' => $hotel->id]);

        $response = $this->deleteJson(route('services.destroy', [$hotel, $service]));

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('services', [
            'id' => $service->id,
        ]);
    }
}
