<?php

namespace Tests\Feature\Api\V1\Hotels;

use App\Models\Hotel;
use App\Models\HotelSetting;
use Tests\ApiTestCase;
use Tests\Traits\InteractsWithHotels;

class HotelSettingTest extends ApiTestCase
{
    use InteractsWithHotels;

    /**
     * Test viewing hotel settings.
     */
    public function test_user_can_view_assigned_hotel_settings(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $setting = HotelSetting::factory()->create(['hotel_id' => $hotel->id]);

        $response = $this->getJson(route('hotels.settings.show', $hotel));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.currency', $setting->currency);
    }

    /**
     * Test user cannot view settings of unassigned hotel.
     */
    public function test_user_cannot_view_unassigned_hotel_settings(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = Hotel::factory()->create(); // Unassigned hotel
        HotelSetting::factory()->create(['hotel_id' => $hotel->id]);

        $response = $this->getJson(route('hotels.settings.show', $hotel));

        $response->assertStatus(403);
    }

    /**
     * Test updating hotel settings.
     */
    public function test_user_can_update_assigned_hotel_settings(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);
        HotelSetting::factory()->create(['hotel_id' => $hotel->id]);

        $payload = [
            'currency' => 'USD',
            'timezone' => 'America/New_York',
            'tax_rate' => 8.25,
            'allow_overbooking' => true,
        ];

        $response = $this->putJson(route('hotels.settings.update', $hotel), $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.currency', 'USD')
            ->assertJsonPath('data.allow_overbooking', true);

        $this->assertDatabaseHas('hotel_settings', [
            'hotel_id' => $hotel->id,
            'currency' => 'USD',
            'allow_overbooking' => true,
        ]);
    }

    /**
     * Test validation constraints.
     */
    public function test_validation_errors_when_updating_invalid_settings(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);
        HotelSetting::factory()->create(['hotel_id' => $hotel->id]);

        $payload = [
            'currency' => 'US', // size:3
            'timezone' => 'Invalid/Timezone',
            'tax_rate' => -5,
        ];

        $response = $this->putJson(route('hotels.settings.update', $hotel), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['currency', 'timezone', 'tax_rate']);
    }
}
