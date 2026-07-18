<?php

namespace Tests\Feature\Api\V1\Hotels;

use App\Models\Hotel;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\ApiTestCase;
use Tests\Traits\InteractsWithHotels;

class HotelTest extends ApiTestCase
{
    use InteractsWithHotels;

    /**
     * Test super admin can retrieve all hotels.
     */
    public function test_super_admin_can_retrieve_all_hotels(): void
    {
        $this->actingAsRole('super_admin');

        Hotel::factory()->count(3)->create();

        $response = $this->getJson(route('hotels.index'));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test hotel manager can only see their assigned hotels.
     */
    public function test_hotel_manager_can_only_retrieve_their_assigned_hotels(): void
    {
        $manager = $this->actingAsRole('hotel_manager');

        $assignedHotel = $this->createHotelForUser($manager);
        Hotel::factory()->create(); // Unassigned hotel

        $response = $this->getJson(route('hotels.index'));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $assignedHotel->id);
    }

    /**
     * Test creation of hotel by authorized user.
     */
    public function test_user_can_create_hotel_with_permission(): void
    {
        Storage::fake('public');

        $user = $this->actingAsRole('super_admin'); // super admin has "create hotels" permission

        $payload = [
            'name' => 'The Palms Resort',
            'slug' => 'the-palms-resort',
            'email' => 'contact@palms.com',
            'phone' => '123456789',
            'country' => 'Kenya',
            'city' => 'Mombasa',
            'address' => 'Shanzu Beach Rd',
            'description' => 'Beautiful beachside resort.',
            'logo' => UploadedFile::fake()->image('logo.png'),
            'is_active' => true,
        ];

        $response = $this->postJson(route('hotels.store'), $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'The Palms Resort');

        $this->assertDatabaseHas('hotels', [
            'name' => 'The Palms Resort',
            'slug' => 'the-palms-resort',
        ]);

        $hotel = Hotel::where('slug', 'the-palms-resort')->first();

        // Assert settings were automatically created
        $this->assertDatabaseHas('hotel_settings', [
            'hotel_id' => $hotel->id,
        ]);

        // Assert creating user is linked to the hotel
        $this->assertTrue($user->belongsToHotel($hotel));

        // Assert logo file exists on disk
        Storage::disk('public')->assertExists($hotel->logo);
    }

    /**
     * Test viewing assigned hotel details.
     */
    public function test_user_can_view_assigned_hotel(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $response = $this->getJson(route('hotels.show', $hotel));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $hotel->id);
    }

    /**
     * Test user cannot view unassigned hotel details.
     */
    public function test_user_cannot_view_unassigned_hotel(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = Hotel::factory()->create(); // Unassigned hotel

        $response = $this->getJson(route('hotels.show', $hotel));

        $response->assertStatus(403);
    }

    /**
     * Test updating assigned hotel.
     */
    public function test_user_can_update_assigned_hotel(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $payload = [
            'name' => 'Updated Palms Resort',
            'slug' => 'updated-palms-resort',
            'country' => 'Kenya',
            'city' => 'Diani',
            'address' => 'Diani Beach Rd',
        ];

        $response = $this->putJson(route('hotels.update', $hotel), $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Updated Palms Resort');

        $this->assertDatabaseHas('hotels', [
            'id' => $hotel->id,
            'name' => 'Updated Palms Resort',
        ]);
    }

    /**
     * Test deleting assigned hotel.
     */
    public function test_user_can_delete_assigned_hotel(): void
    {
        $user = $this->actingAsRole('super_admin');
        $hotel = $this->createHotelForUser($user);

        $response = $this->deleteJson(route('hotels.destroy', $hotel));

        $response->assertOk()
            ->assertJsonPath('success', true);

        // Hotel should be soft deleted
        $this->assertSoftDeleted('hotels', [
            'id' => $hotel->id,
        ]);
    }
}
