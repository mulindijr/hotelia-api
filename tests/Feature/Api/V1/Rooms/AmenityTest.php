<?php

namespace Tests\Feature\Api\V1\Rooms;

use App\Models\Amenity;
use Tests\ApiTestCase;

class AmenityTest extends ApiTestCase
{
    /**
     * Test viewing global amenities list.
     */
    public function test_user_can_view_amenities_list(): void
    {
        $this->actingAsRole('hotel_manager');

        Amenity::factory()->count(3)->create();

        $response = $this->getJson(route('amenities.index'));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test Super Admin can create global amenity.
     */
    public function test_super_admin_can_create_amenity(): void
    {
        $this->actingAsRole('super_admin');

        $payload = [
            'name' => 'High-Speed Wi-Fi',
            'description' => 'Up to 100 Mbps broadband connection.',
        ];

        $response = $this->postJson(route('amenities.store'), $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'High-Speed Wi-Fi');

        $this->assertDatabaseHas('amenities', [
            'name' => 'High-Speed Wi-Fi',
        ]);
    }

    /**
     * Test normal staff cannot create global amenity.
     */
    public function test_hotel_manager_cannot_create_amenity(): void
    {
        $this->actingAsRole('hotel_manager'); // lacks "create amenities" permission

        $payload = [
            'name' => 'Private Pool',
        ];

        $response = $this->postJson(route('amenities.store'), $payload);

        $response->assertStatus(403);
    }
}
