<?php

namespace Tests\Unit\Policies;

use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use App\Policies\RoomPolicy;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoomPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected RoomPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->policy = new RoomPolicy();
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

    public function test_view_returns_false_when_room_does_not_belong_to_hotel_context(): void
    {
        $user = User::factory()->create();
        $hotelA = Hotel::factory()->create();
        $hotelB = Hotel::factory()->create();
        $hotelA->users()->attach($user->id);

        $roomB = Room::factory()->create(['hotel_id' => $hotelB->id]);

        $this->assertFalse($this->policy->view($user, $roomB, $hotelA));
    }

    public function test_update_returns_false_when_room_does_not_belong_to_hotel_context(): void
    {
        $user = User::factory()->create();
        $hotelA = Hotel::factory()->create();
        $hotelB = Hotel::factory()->create();
        $hotelA->users()->attach($user->id);

        $roomB = Room::factory()->create(['hotel_id' => $hotelB->id]);

        $this->assertFalse($this->policy->update($user, $roomB, $hotelA));
    }

    public function test_delete_returns_false_when_room_does_not_belong_to_hotel_context(): void
    {
        $user = User::factory()->create();
        $hotelA = Hotel::factory()->create();
        $hotelB = Hotel::factory()->create();
        $hotelA->users()->attach($user->id);

        $roomB = Room::factory()->create(['hotel_id' => $hotelB->id]);

        $this->assertFalse($this->policy->delete($user, $roomB, $hotelA));
    }
}
