<?php

namespace Tests\Unit\Policies;

use App\Models\Guest;
use App\Models\User;
use App\Policies\GuestPolicy;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GuestPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected GuestPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->policy = new GuestPolicy();
    }

    public function test_super_admin_bypasses_all_checks(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $this->assertTrue($this->policy->before($superAdmin, 'viewAny'));
    }

    public function test_any_user_can_view_any_guest(): void
    {
        $user = User::factory()->create();
        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_any_user_can_view_guest(): void
    {
        $user = User::factory()->create();
        $guest = Guest::factory()->create();
        $this->assertTrue($this->policy->view($user, $guest));
    }

    public function test_any_user_can_create_guest(): void
    {
        $user = User::factory()->create();
        $this->assertTrue($this->policy->create($user));
    }

    public function test_any_user_can_update_guest(): void
    {
        $user = User::factory()->create();
        $guest = Guest::factory()->create();
        $this->assertTrue($this->policy->update($user, $guest));
    }

    public function test_any_user_can_delete_guest(): void
    {
        $user = User::factory()->create();
        $guest = Guest::factory()->create();
        $this->assertTrue($this->policy->delete($user, $guest));
    }
}
