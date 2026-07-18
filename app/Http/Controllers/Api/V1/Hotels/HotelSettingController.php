<?php

namespace App\Http\Controllers\Api\V1\Hotels;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Hotels\UpdateHotelSettingRequest;
use App\Http\Resources\Api\V1\Hotels\HotelSettingResource;
use App\Models\Hotel;
use App\Services\Hotel\HotelSettingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class HotelSettingController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected HotelSettingService $hotelSettingService
    ) {}

    /**
     * Display the specified hotel settings.
     */
    public function show(Hotel $hotel): JsonResponse
    {
        // Guarantee settings record exists
        $setting = $hotel->settings ?: $hotel->settings()->create([
            'currency' => 'KES',
            'timezone' => 'Africa/Nairobi',
            'language' => 'en',
        ]);

        $this->authorize('view', $setting);

        return response()->json([
            'success' => true,
            'message' => 'Hotel Settings Retrieved Successfully.',
            'data' => new HotelSettingResource($setting),
        ]);
    }

    /**
     * Update the specified hotel settings.
     */
    public function update(UpdateHotelSettingRequest $request, Hotel $hotel): JsonResponse
    {
        // Guarantee settings record exists
        $setting = $hotel->settings ?: $hotel->settings()->create([
            'currency' => 'KES',
            'timezone' => 'Africa/Nairobi',
            'language' => 'en',
        ]);

        $this->authorize('update', $setting);

        $updatedSetting = $this->hotelSettingService->update($setting, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Hotel Settings Updated Successfully.',
            'data' => new HotelSettingResource($updatedSetting),
        ]);
    }
}
