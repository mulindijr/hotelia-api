<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Enforce JSON response headers for API testing
        $this->withHeaders([
            'Accept' => 'application/json',
        ]);
    }

    /**
     * Authenticate a user via Sanctum.
     */
    protected function actingAsUser(User $user, array $abilities = ['*']): self
    {
        Sanctum::actingAs($user, $abilities);
        return $this;
    }

    /**
     * Create and authenticate a user with a specific role.
     */
    protected function actingAsRole(string $role): User
    {
        // Ensure roles & permissions are seeded before assigning
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);

        $user = User::factory()->create();
        $user->assignRole($role);
        
        $this->actingAsUser($user);
        
        return $user;
    }
}
