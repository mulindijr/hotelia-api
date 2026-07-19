<?php

namespace Tests\Feature\Api\V1\Security;

use App\Models\FailedLoginAttempt;
use App\Models\LoginHistory;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;
use Tests\ApiTestCase;

class SecurityAndAuditTest extends ApiTestCase
{
    /**
     * Test retrieving personal login history.
     */
    public function test_user_can_view_login_history(): void
    {
        $user = $this->actingAsRole('receptionist');

        LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit Test Engine',
            'logged_in_at' => now(),
        ]);

        $response = $this->getJson(route('security.login-history'));

        $response->assertOk()
            ->assertJsonStructure(['data', 'current_page', 'total']);
    }

    /**
     * Test viewing failed login attempts logs.
     */
    public function test_user_can_view_failed_logins(): void
    {
        $admin = $this->actingAsRole('super_admin');

        FailedLoginAttempt::create([
            'email' => 'fake.attacker@example.com',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'TestBot/1.0',
            'attempted_at' => now(),
        ]);

        $response = $this->getJson(route('security.failed-logins'));

        $response->assertOk()
            ->assertJsonStructure(['data', 'current_page']);
    }

    /**
     * Test unlocking a locked user account.
     */
    public function test_admin_can_unlock_locked_user(): void
    {
        $admin = $this->actingAsRole('super_admin');

        $lockedUser = User::factory()->create([
            'locked_until' => now()->addMinutes(30),
            'failed_login_count' => 5,
        ]);

        $this->assertTrue($lockedUser->isLocked());

        $response = $this->postJson(route('admin.users.unlock', $lockedUser));

        $response->assertOk()
            ->assertJsonPath('message', "Account for {$lockedUser->full_name} has been unlocked successfully.");

        $lockedUser->refresh();
        $this->assertFalse($lockedUser->isLocked());
    }

    /**
     * Test querying activity audit logs.
     */
    public function test_admin_can_view_audit_logs(): void
    {
        $admin = $this->actingAsRole('super_admin');

        activity()
            ->useLog('system')
            ->performedOn($admin)
            ->causedBy($admin)
            ->log('Test system audit log entry');

        $response = $this->getJson(route('admin.audit-logs.index'));

        $response->assertOk()
            ->assertJsonStructure(['data', 'current_page']);
    }
}
