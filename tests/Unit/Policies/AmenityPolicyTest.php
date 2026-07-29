<?php

namespace Tests\Unit\Policies;

use App\Models\Amenity;
use App\Models\User;
use App\Policies\AmenityPolicy;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AmenityPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected AmenityPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->policy = new AmenityPolicy;
    }

    public function test_super_admin_bypasses_all_checks(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $this->assertTrue($this->policy->before($superAdmin, 'viewAny'));
    }

    public function test_any_user_can_view_any_amenity(): void
    {
        $user = User::factory()->create();
        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_any_user_can_view_amenity(): void
    {
        $user = User::factory()->create();
        $amenity = Amenity::factory()->create();
        $this->assertTrue($this->policy->view($user, $amenity));
    }

    public function test_any_user_can_create_amenity(): void
    {
        $user = User::factory()->create();
        $this->assertTrue($this->policy->create($user));
    }

    public function test_any_user_can_update_amenity(): void
    {
        $user = User::factory()->create();
        $amenity = Amenity::factory()->create();
        $this->assertTrue($this->policy->update($user, $amenity));
    }

    public function test_any_user_can_delete_amenity(): void
    {
        $user = User::factory()->create();
        $amenity = Amenity::factory()->create();
        $this->assertTrue($this->policy->delete($user, $amenity));
    }
}
