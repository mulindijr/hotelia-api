<?php

namespace Tests\Unit\Policies;

use App\Models\Hotel;
use App\Models\User;
use App\Policies\HotelPolicy;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HotelPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected HotelPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->policy = new HotelPolicy;
    }

    public function test_super_admin_bypasses_all_checks(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $this->assertTrue($this->policy->before($superAdmin, 'viewAny'));
    }

    public function test_any_user_can_view_any_hotel(): void
    {
        $user = User::factory()->create();
        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_user_can_view_assigned_hotel(): void
    {
        $user = User::factory()->create();
        $hotel = Hotel::factory()->create();
        $hotel->users()->attach($user->id);

        $this->assertTrue($this->policy->view($user, $hotel));
    }

    public function test_user_cannot_view_unassigned_hotel(): void
    {
        $user = User::factory()->create();
        $hotel = Hotel::factory()->create();

        $this->assertFalse($this->policy->view($user, $hotel));
    }

    public function test_any_user_can_create_hotel(): void
    {
        $user = User::factory()->create();
        $this->assertTrue($this->policy->create($user));
    }

    public function test_user_can_update_assigned_hotel(): void
    {
        $user = User::factory()->create();
        $hotel = Hotel::factory()->create();
        $hotel->users()->attach($user->id);

        $this->assertTrue($this->policy->update($user, $hotel));
    }

    public function test_user_cannot_update_unassigned_hotel(): void
    {
        $user = User::factory()->create();
        $hotel = Hotel::factory()->create();

        $this->assertFalse($this->policy->update($user, $hotel));
    }

    public function test_user_can_delete_assigned_hotel(): void
    {
        $user = User::factory()->create();
        $hotel = Hotel::factory()->create();
        $hotel->users()->attach($user->id);

        $this->assertTrue($this->policy->delete($user, $hotel));
    }

    public function test_user_cannot_delete_unassigned_hotel(): void
    {
        $user = User::factory()->create();
        $hotel = Hotel::factory()->create();

        $this->assertFalse($this->policy->delete($user, $hotel));
    }

    public function test_user_cannot_restore_hotel(): void
    {
        $user = User::factory()->create();
        $hotel = Hotel::factory()->create();

        $this->assertFalse($this->policy->restore($user, $hotel));
    }

    public function test_user_cannot_force_delete_hotel(): void
    {
        $user = User::factory()->create();
        $hotel = Hotel::factory()->create();

        $this->assertFalse($this->policy->forceDelete($user, $hotel));
    }
}
