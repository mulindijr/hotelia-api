<?php

namespace Tests\Feature\Api\V1\Pricing;

use App\Models\PricingRule;
use Tests\ApiTestCase;
use Tests\Traits\InteractsWithHotels;

class PricingRuleTest extends ApiTestCase
{
    use InteractsWithHotels;

    public function test_user_can_list_pricing_rules(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        PricingRule::create([
            'hotel_id' => $hotel->id,
            'name' => 'Summer Peak',
            'start_date' => '2026-06-01',
            'end_date' => '2026-08-31',
            'price_modifier_type' => 'percentage',
            'price_modifier_value' => 25.00,
        ]);

        $response = $this->getJson("/api/v1/hotels/{$hotel->id}/pricing-rules");

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Summer Peak')
            ->assertJsonPath('data.0.price_modifier_value', 25);
    }

    public function test_user_can_create_pricing_rule(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $payload = [
            'name' => 'Weekend Markup',
            'days_of_week' => ['friday', 'saturday'],
            'price_modifier_type' => 'percentage',
            'price_modifier_value' => 15.00,
            'priority' => 10,
        ];

        $response = $this->postJson("/api/v1/hotels/{$hotel->id}/pricing-rules", $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Weekend Markup');

        $this->assertDatabaseHas('pricing_rules', [
            'hotel_id' => $hotel->id,
            'name' => 'Weekend Markup',
        ]);
    }
}
