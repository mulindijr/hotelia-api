<?php

namespace Tests\Feature\Performance;

use App\Models\Hotel;

use App\Models\HotelSetting;
use App\Models\RoomType;
use App\Models\Service;

use App\Services\Hotel\AncillaryService;
use App\Services\Hotel\HotelSettingService;
use App\Services\Report\ReportService;
use App\Services\Room\RoomTypeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

use Tests\TestCase;

class PerformanceCachingTest extends TestCase
{
    use RefreshDatabase;

    public function test_hotel_settings_are_cached_and_invalidated_on_update(): void
    {
        $hotel = Hotel::factory()->create();
        $settingService = app(HotelSettingService::class);

        $cacheKey = "hotel:{$hotel->id}:settings";
        $this->assertFalse(Cache::has($cacheKey));

        // First call populates cache
        $settings = $settingService->getSettings($hotel);
        $this->assertTrue(Cache::has($cacheKey));
        $this->assertEquals($settings->currency, Cache::get($cacheKey)->currency);

        // Update settings invalidates cache
        $settingService->update($settings, ['currency' => 'USD']);
        $this->assertFalse(Cache::has($cacheKey));
    }

    public function test_room_types_are_cached_and_invalidated_on_mutation(): void
    {
        $hotel = Hotel::factory()->create();
        $roomTypeService = app(RoomTypeService::class);

        $cacheKey = "hotel:{$hotel->id}:room_types";
        $this->assertFalse(Cache::has($cacheKey));

        // First call populates cache
        $roomTypes = $roomTypeService->getRoomTypes($hotel);
        $this->assertTrue(Cache::has($cacheKey));

        // Creating room type invalidates cache
        $newRoomType = $roomTypeService->create($hotel, [
            'name' => 'Deluxe Suite',
            'base_price' => 150.00,
            'capacity' => 2,
            'beds' => 1,
        ]);
        $this->assertFalse(Cache::has($cacheKey));

        // Fetching populates cache again
        $roomTypeService->getRoomTypes($hotel);
        $this->assertTrue(Cache::has($cacheKey));

        // Updating room type invalidates cache
        $roomTypeService->update($newRoomType, ['name' => 'Presidential Suite']);
        $this->assertFalse(Cache::has($cacheKey));

        // Deleting room type invalidates cache
        $roomTypeService->getRoomTypes($hotel);
        $this->assertTrue(Cache::has($cacheKey));

        $roomTypeService->delete($newRoomType);
        $this->assertFalse(Cache::has($cacheKey));
    }

    public function test_ancillary_services_are_cached_and_invalidated_on_mutation(): void
    {
        $hotel = Hotel::factory()->create();
        $ancillaryService = app(AncillaryService::class);

        $cacheKey = "hotel:{$hotel->id}:services";
        $this->assertFalse(Cache::has($cacheKey));

        // Fetching populates cache
        $ancillaryService->getServices($hotel);
        $this->assertTrue(Cache::has($cacheKey));

        // Creating service invalidates cache
        $service = $ancillaryService->create($hotel, [
            'name' => 'Airport Transfer',
            'price' => 50.00,
        ]);
        $this->assertFalse(Cache::has($cacheKey));
    }

    public function test_dashboard_stats_are_cached(): void
    {
        $hotel = Hotel::factory()->create();
        $reportService = app(ReportService::class);

        $cacheKey = "hotel:{$hotel->id}:dashboard_stats";
        $this->assertFalse(Cache::has($cacheKey));

        // Fetching dashboard stats caches the result for 5 minutes
        $stats = $reportService->getDashboardStats($hotel);
        $this->assertTrue(Cache::has($cacheKey));
        $this->assertEquals($stats, Cache::get($cacheKey));
    }
}
