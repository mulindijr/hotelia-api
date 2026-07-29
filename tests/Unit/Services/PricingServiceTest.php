<?php

namespace Tests\Unit\Services;

use App\Models\Hotel;
use App\Models\PricingRule;
use App\Models\RatePlan;
use App\Models\RoomType;
use App\Services\Pricing\PricingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PricingService $pricingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pricingService = new PricingService;
    }

    public function test_returns_base_price_when_no_plans_or_rules_exist(): void
    {
        $hotel = Hotel::factory()->create();
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id, 'base_price' => 100.00]);

        $price = $this->pricingService->calculateNightlyPrice(
            $roomType,
            null,
            Carbon::parse('2026-08-01')
        );

        $this->assertEquals(100.00, $price);
    }

    public function test_applies_rate_plan_percentage_discount(): void
    {
        $hotel = Hotel::factory()->create();
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id, 'base_price' => 200.00]);
        $ratePlan = RatePlan::create([
            'hotel_id' => $hotel->id,
            'name' => 'Non-Refundable',
            'code' => 'NONREF',
            'modifier_type' => 'percentage',
            'modifier_value' => -15.00, // 15% discount
            'is_active' => true,
        ]);

        $price = $this->pricingService->calculateNightlyPrice(
            $roomType,
            $ratePlan,
            Carbon::parse('2026-08-01')
        );

        // 200 - 15% = 170
        $this->assertEquals(170.00, $price);
    }

    public function test_applies_pricing_rule_weekend_markup(): void
    {
        $hotel = Hotel::factory()->create();
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id, 'base_price' => 100.00]);

        PricingRule::create([
            'hotel_id' => $hotel->id,
            'name' => 'Weekend Surge',
            'days_of_week' => ['saturday', 'sunday'],
            'price_modifier_type' => 'percentage',
            'price_modifier_value' => 20.00, // +20%
            'priority' => 1,
            'is_active' => true,
        ]);

        // 2026-08-01 is Saturday
        $saturdayPrice = $this->pricingService->calculateNightlyPrice(
            $roomType,
            null,
            Carbon::parse('2026-08-01')
        );

        // 2026-08-03 is Monday
        $mondayPrice = $this->pricingService->calculateNightlyPrice(
            $roomType,
            null,
            Carbon::parse('2026-08-03')
        );

        $this->assertEquals(120.00, $saturdayPrice);
        $this->assertEquals(100.00, $mondayPrice);
    }
}
