<?php

namespace Tests\Feature\Api\V1\Users;

use App\Models\Hotel;
use App\Models\User;
use Tests\ApiTestCase;
use Tests\Traits\InteractsWithHotels;

class UserManagementTest extends ApiTestCase
{
    use InteractsWithHotels;

    /**
     * Test displaying staff members associated with a hotel.
     */
    public function test_user_can_view_hotel_staff_list(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        // Create an additional receptionist staff user
        $receptionist = User::factory()->create();
        $receptionist->assignRole('receptionist');
        $receptionist->hotels()->attach($hotel->id);

        $response = $this->getJson(route('hotels.users.index', $hotel));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'first_name',
                        'last_name',
                        'full_name',
                        'email',
                        'phone',
                        'is_active',
                        'roles',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);
    }

    /**
     * Test registering/associating new staff members.
     */
    public function test_user_can_create_staff(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $payload = [
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => 'alice.smith@example.com',
            'phone' => '+254700000000',
            'role' => 'housekeeper',
            'password' => 'secret123',
        ];

        $response = $this->postJson(route('hotels.users.store', $hotel), $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', 'alice.smith@example.com')
            ->assertJsonPath('data.roles.0', 'housekeeper');

        // Assert database values
        $this->assertDatabaseHas('users', [
            'email' => 'alice.smith@example.com',
            'phone' => '+254700000000',
        ]);

        $createdUser = User::where('email', 'alice.smith@example.com')->first();
        $this->assertTrue($createdUser->belongsToHotel($hotel->id));
        $this->assertTrue($createdUser->hasRole('housekeeper'));
    }

    /**
     * Test updating a staff member's profile parameters and permissions.
     */
    public function test_user_can_update_staff_role_and_profile(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $staff = User::factory()->create(['first_name' => 'Bob']);
        $staff->assignRole('housekeeper');
        $staff->hotels()->attach($hotel->id);

        $payload = [
            'first_name' => 'Robert',
            'role' => 'receptionist',
            'is_active' => false,
        ];

        $response = $this->putJson(route('hotels.users.update', [$hotel, $staff]), $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.first_name', 'Robert')
            ->assertJsonPath('data.is_active', false)
            ->assertJsonPath('data.roles.0', 'receptionist');

        $staff->refresh();
        $this->assertEquals('Robert', $staff->first_name);
        $this->assertFalse($staff->is_active);
        $this->assertTrue($staff->hasRole('receptionist'));
        $this->assertFalse($staff->hasRole('housekeeper'));
    }

    /**
     * Test detaching staff from a hotel.
     */
    public function test_user_can_delete_staff(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $staff = User::factory()->create();
        $staff->assignRole('housekeeper');
        $staff->hotels()->attach($hotel->id);

        $response = $this->deleteJson(route('hotels.users.destroy', [$hotel, $staff]));

        $response->assertOk()
            ->assertJsonPath('success', true);

        // Staff is no longer associated
        $this->assertFalse($staff->belongsToHotel($hotel->id));

        // Soft-deleted because it was only associated with this hotel
        $this->assertSoftDeleted('users', ['id' => $staff->id]);
    }

    /**
     * Test multi-hotel isolations for staff configurations.
     */
    public function test_security_isolations(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $otherHotel = Hotel::factory()->create();
        $otherStaff = User::factory()->create();
        $otherStaff->assignRole('housekeeper');
        $otherStaff->hotels()->attach($otherHotel->id);

        // Attempting to view users of another hotel
        $response = $this->getJson(route('hotels.users.index', $otherHotel));
        $response->assertStatus(403);

        // Attempting to update staff details on another hotel
        $response = $this->putJson(route('hotels.users.update', [$otherHotel, $otherStaff]), [
            'first_name' => 'Malicious',
        ]);
        $response->assertStatus(403);

        // Attempting to delete a super admin should fail
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');
        $superAdmin->hotels()->attach($hotel->id);

        $response = $this->deleteJson(route('hotels.users.destroy', [$hotel, $superAdmin]));
        $response->assertStatus(403);
    }
}
