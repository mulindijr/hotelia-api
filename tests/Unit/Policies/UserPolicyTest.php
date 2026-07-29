<?php

namespace Tests\Unit\Policies;

use App\Models\Hotel;
use App\Models\User;
use App\Policies\UserPolicy;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected UserPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->policy = new UserPolicy;
    }

    public function test_super_admin_bypasses_all_checks(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $this->assertTrue($this->policy->before($superAdmin, 'viewAny'));
    }

    public function test_user_can_view_target_user_in_same_hotel(): void
    {
        $hotel = Hotel::factory()->create();

        $user = User::factory()->create();
        $user->hotels()->attach($hotel->id);

        $targetUser = User::factory()->create();
        $targetUser->hotels()->attach($hotel->id);

        $this->assertTrue($this->policy->view($user, $targetUser));
    }

    public function test_user_cannot_view_target_user_in_different_hotel(): void
    {
        $hotelA = Hotel::factory()->create();
        $hotelB = Hotel::factory()->create();

        $user = User::factory()->create();
        $user->hotels()->attach($hotelA->id);

        $targetUser = User::factory()->create();
        $targetUser->hotels()->attach($hotelB->id);

        $this->assertFalse($this->policy->view($user, $targetUser));
    }

    public function test_user_can_update_target_user_in_same_hotel(): void
    {
        $hotel = Hotel::factory()->create();

        $user = User::factory()->create();
        $user->hotels()->attach($hotel->id);

        $targetUser = User::factory()->create();
        $targetUser->hotels()->attach($hotel->id);

        $this->assertTrue($this->policy->update($user, $targetUser));
    }

    public function test_user_cannot_update_target_user_in_different_hotel(): void
    {
        $hotelA = Hotel::factory()->create();
        $hotelB = Hotel::factory()->create();

        $user = User::factory()->create();
        $user->hotels()->attach($hotelA->id);

        $targetUser = User::factory()->create();
        $targetUser->hotels()->attach($hotelB->id);

        $this->assertFalse($this->policy->update($user, $targetUser));
    }

    public function test_user_cannot_update_super_admin(): void
    {
        $hotel = Hotel::factory()->create();

        $user = User::factory()->create();
        $user->hotels()->attach($hotel->id);

        $targetUser = User::factory()->create();
        $targetUser->assignRole('super_admin');
        $targetUser->hotels()->attach($hotel->id);

        $this->assertFalse($this->policy->update($user, $targetUser));
    }

    public function test_user_can_delete_target_user_in_same_hotel(): void
    {
        $hotel = Hotel::factory()->create();

        $user = User::factory()->create();
        $user->hotels()->attach($hotel->id);

        $targetUser = User::factory()->create();
        $targetUser->hotels()->attach($hotel->id);

        $this->assertTrue($this->policy->delete($user, $targetUser));
    }

    public function test_user_cannot_delete_target_user_in_different_hotel(): void
    {
        $hotelA = Hotel::factory()->create();
        $hotelB = Hotel::factory()->create();

        $user = User::factory()->create();
        $user->hotels()->attach($hotelA->id);

        $targetUser = User::factory()->create();
        $targetUser->hotels()->attach($hotelB->id);

        $this->assertFalse($this->policy->delete($user, $targetUser));
    }

    public function test_user_cannot_delete_super_admin(): void
    {
        $hotel = Hotel::factory()->create();

        $user = User::factory()->create();
        $user->hotels()->attach($hotel->id);

        $targetUser = User::factory()->create();
        $targetUser->assignRole('super_admin');
        $targetUser->hotels()->attach($hotel->id);

        $this->assertFalse($this->policy->delete($user, $targetUser));
    }
}
