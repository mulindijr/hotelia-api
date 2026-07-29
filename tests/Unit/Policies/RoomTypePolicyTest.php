<?php

namespace Tests\Unit\Policies;

use App\Models\Hotel;
use App\Models\RoomType;
use App\Models\User;
use App\Policies\RoomTypePolicy;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomTypePolicyTest extends TestCase
{
    use RefreshDatabase;

    protected RoomTypePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->policy = new RoomTypePolicy;
    }

    public function test_super_admin_bypasses_all_checks(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $this->assertTrue($this->policy->before($superAdmin, 'viewAny'));
        $this->assertTrue($this->policy->before($superAdmin, 'view'));
    }

    public function test_view_any_returns_true_for_user_belonging_to_hotel(): void
    {
        $user = User::factory()->create();
        $hotel = Hotel::factory()->create();
        $hotel->users()->attach($user->id);

        $this->assertTrue($this->policy->viewAny($user, $hotel));
    }

    public function test_view_any_returns_false_for_unassigned_user(): void
    {
        $user = User::factory()->create();
        $hotel = Hotel::factory()->create();

        $this->assertFalse($this->policy->viewAny($user, $hotel));
    }

    public function test_create_returns_true_for_user_belonging_to_hotel(): void
    {
        $user = User::factory()->create();
        $hotel = Hotel::factory()->create();
        $hotel->users()->attach($user->id);

        $this->assertTrue($this->policy->create($user, $hotel));
    }

    public function test_create_returns_false_for_unassigned_user(): void
    {
        $user = User::factory()->create();
        $hotel = Hotel::factory()->create();

        $this->assertFalse($this->policy->create($user, $hotel));
    }

    public function test_view_returns_false_when_room_type_does_not_belong_to_hotel_context(): void
    {
        $user = User::factory()->create();
        $hotelA = Hotel::factory()->create();
        $hotelB = Hotel::factory()->create();
        $hotelA->users()->attach($user->id);

        $roomTypeB = RoomType::factory()->create(['hotel_id' => $hotelB->id]);

        $this->assertFalse($this->policy->view($user, $roomTypeB, $hotelA));
    }

    public function test_update_returns_false_when_room_type_does_not_belong_to_hotel_context(): void
    {
        $user = User::factory()->create();
        $hotelA = Hotel::factory()->create();
        $hotelB = Hotel::factory()->create();
        $hotelA->users()->attach($user->id);

        $roomTypeB = RoomType::factory()->create(['hotel_id' => $hotelB->id]);

        $this->assertFalse($this->policy->update($user, $roomTypeB, $hotelA));
    }

    public function test_delete_returns_false_when_room_type_does_not_belong_to_hotel_context(): void
    {
        $user = User::factory()->create();
        $hotelA = Hotel::factory()->create();
        $hotelB = Hotel::factory()->create();
        $hotelA->users()->attach($user->id);

        $roomTypeB = RoomType::factory()->create(['hotel_id' => $hotelB->id]);

        $this->assertFalse($this->policy->delete($user, $roomTypeB, $hotelA));
    }
}
