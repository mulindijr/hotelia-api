<?php

namespace Tests\Unit\Policies;

use App\Models\Hotel;
use App\Models\Service;
use App\Models\User;
use App\Policies\ServicePolicy;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicePolicyTest extends TestCase
{
    use RefreshDatabase;

    protected ServicePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->policy = new ServicePolicy;
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

    public function test_view_returns_false_when_service_does_not_belong_to_hotel_context(): void
    {
        $user = User::factory()->create();
        $hotelA = Hotel::factory()->create();
        $hotelB = Hotel::factory()->create();
        $hotelA->users()->attach($user->id);

        $serviceB = Service::factory()->create(['hotel_id' => $hotelB->id]);

        $this->assertFalse($this->policy->view($user, $serviceB, $hotelA));
    }

    public function test_update_returns_false_when_service_does_not_belong_to_hotel_context(): void
    {
        $user = User::factory()->create();
        $hotelA = Hotel::factory()->create();
        $hotelB = Hotel::factory()->create();
        $hotelA->users()->attach($user->id);

        $serviceB = Service::factory()->create(['hotel_id' => $hotelB->id]);

        $this->assertFalse($this->policy->update($user, $serviceB, $hotelA));
    }

    public function test_delete_returns_false_when_service_does_not_belong_to_hotel_context(): void
    {
        $user = User::factory()->create();
        $hotelA = Hotel::factory()->create();
        $hotelB = Hotel::factory()->create();
        $hotelA->users()->attach($user->id);

        $serviceB = Service::factory()->create(['hotel_id' => $hotelB->id]);

        $this->assertFalse($this->policy->delete($user, $serviceB, $hotelA));
    }
}
