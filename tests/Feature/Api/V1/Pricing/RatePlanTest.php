<?php

namespace Tests\Feature\Api\V1\Pricing;

use App\Models\RatePlan;
use Tests\ApiTestCase;
use Tests\Traits\InteractsWithHotels;

class RatePlanTest extends ApiTestCase
{
    use InteractsWithHotels;

    public function test_user_can_list_rate_plans(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        RatePlan::create([
            'hotel_id' => $hotel->id,
            'name' => 'Standard Rate',
            'code' => 'STD',
            'modifier_type' => 'percentage',
            'modifier_value' => 0.00,
        ]);

        $response = $this->getJson("/api/v1/hotels/{$hotel->id}/rate-plans");

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Standard Rate')
            ->assertJsonPath('data.0.code', 'STD');
    }

    public function test_user_can_create_rate_plan(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $payload = [
            'name' => 'Non-Refundable',
            'code' => 'NONREF',
            'modifier_type' => 'percentage',
            'modifier_value' => -10.00,
            'cancellation_policy' => 'non_refundable',
            'meal_plan' => 'breakfast',
        ];

        $response = $this->postJson("/api/v1/hotels/{$hotel->id}/rate-plans", $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.code', 'NONREF');

        $this->assertDatabaseHas('rate_plans', [
            'hotel_id' => $hotel->id,
            'code' => 'NONREF',
        ]);
    }
}
