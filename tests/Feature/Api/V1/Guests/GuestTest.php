<?php

namespace Tests\Feature\Api\V1\Guests;

use App\Models\Guest;
use Tests\ApiTestCase;

class GuestTest extends ApiTestCase
{
    /**
     * Test listing guests.
     */
    public function test_user_can_view_guests_list(): void
    {
        $this->actingAsRole('receptionist');

        Guest::factory()->count(3)->create();

        $response = $this->getJson(route('guests.index'));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test searching guests.
     */
    public function test_user_can_search_guests_by_name(): void
    {
        $this->actingAsRole('receptionist');

        Guest::factory()->create([
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => 'alice@smith.com',
        ]);
        Guest::factory()->create([
            'first_name' => 'Bob',
            'last_name' => 'Jones',
            'email' => 'bob@jones.com',
        ]);

        // Search for Alice
        $response = $this->getJson(route('guests.index', ['filter[search]' => 'Alice']));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.first_name', 'Alice');
    }

    /**
     * Test creating a guest.
     */
    public function test_user_can_create_guest(): void
    {
        $this->actingAsRole('receptionist');

        $payload = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+254712345678',
            'nationality' => 'Kenyan',
        ];

        $response = $this->postJson(route('guests.store'), $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.first_name', 'John');

        $this->assertDatabaseHas('guests', [
            'email' => 'john.doe@example.com',
        ]);
    }

    /**
     * Test unique email validation on creation.
     */
    public function test_user_cannot_create_guest_with_duplicate_email(): void
    {
        $this->actingAsRole('receptionist');

        Guest::factory()->create(['email' => 'duplicate@example.com']);

        $payload = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'duplicate@example.com',
            'phone' => '+254787654321',
        ];

        $response = $this->postJson(route('guests.store'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test updating a guest.
     */
    public function test_user_can_update_guest(): void
    {
        $this->actingAsRole('receptionist');

        $guest = Guest::factory()->create(['first_name' => 'OldName']);

        $payload = [
            'first_name' => 'NewName',
        ];

        $response = $this->putJson(route('guests.update', $guest), $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.first_name', 'NewName');

        $this->assertDatabaseHas('guests', [
            'id' => $guest->id,
            'first_name' => 'NewName',
        ]);
    }

    /**
     * Test deleting a guest.
     */
    public function test_user_can_delete_guest(): void
    {
        $this->actingAsRole('super_admin');

        $guest = Guest::factory()->create();

        $response = $this->deleteJson(route('guests.destroy', $guest));

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('guests', [
            'id' => $guest->id,
        ]);
    }

    /**
     * Test hotel manager cannot delete a guest.
     */
    public function test_hotel_manager_cannot_delete_guest(): void
    {
        $this->actingAsRole('hotel_manager');

        $guest = Guest::factory()->create();

        $response = $this->deleteJson(route('guests.destroy', $guest));

        $response->assertStatus(403);
    }

    /**
     * Test receptionist cannot delete a guest.
     */
    public function test_receptionist_cannot_delete_guest(): void
    {
        $this->actingAsRole('receptionist');

        $guest = Guest::factory()->create();

        $response = $this->deleteJson(route('guests.destroy', $guest));

        $response->assertStatus(403);
    }
}
