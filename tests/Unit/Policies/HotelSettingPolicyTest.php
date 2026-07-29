<?php

namespace Tests\Unit\Policies;

use App\Models\Hotel;
use App\Models\HotelSetting;
use App\Models\User;
use App\Policies\HotelSettingPolicy;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HotelSettingPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected HotelSettingPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->policy = new HotelSettingPolicy;
    }

    public function test_super_admin_bypasses_all_checks(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $this->assertTrue($this->policy->before($superAdmin, 'view'));
    }

    public function test_view_returns_true_for_user_belonging_to_hotel(): void
    {
        $user = User::factory()->create();
        $hotel = Hotel::factory()->create();
        $hotel->users()->attach($user->id);

        $setting = HotelSetting::factory()->create(['hotel_id' => $hotel->id]);

        $this->assertTrue($this->policy->view($user, $setting, $hotel));
    }

    public function test_view_returns_false_when_setting_does_not_belong_to_hotel_context(): void
    {
        $user = User::factory()->create();
        $hotelA = Hotel::factory()->create();
        $hotelB = Hotel::factory()->create();
        $hotelA->users()->attach($user->id);

        $settingB = HotelSetting::factory()->create(['hotel_id' => $hotelB->id]);

        $this->assertFalse($this->policy->view($user, $settingB, $hotelA));
    }

    public function test_update_returns_false_when_setting_does_not_belong_to_hotel_context(): void
    {
        $user = User::factory()->create();
        $hotelA = Hotel::factory()->create();
        $hotelB = Hotel::factory()->create();
        $hotelA->users()->attach($user->id);

        $settingB = HotelSetting::factory()->create(['hotel_id' => $hotelB->id]);

        $this->assertFalse($this->policy->update($user, $settingB, $hotelA));
    }
}
