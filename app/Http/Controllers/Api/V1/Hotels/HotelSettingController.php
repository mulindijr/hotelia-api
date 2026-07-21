<?php

namespace App\Http\Controllers\Api\V1\Hotels;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Hotels\UpdateHotelSettingRequest;
use App\Http\Resources\Api\V1\Hotels\HotelSettingResource;
use App\Models\Hotel;
use App\Services\Hotel\HotelSettingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Hotel Settings",
 *     description="Hotel configuration and localization settings endpoints"
 * )
 */
class HotelSettingController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected HotelSettingService $hotelSettingService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/hotels/{hotel}/settings",
     *     summary="Retrieve hotel configuration settings",
     *     tags={"Hotel Settings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="hotel", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Hotel settings retrieved successfully"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function show(Hotel $hotel): JsonResponse
    {
        $setting = $this->hotelSettingService->getSettings($hotel);

        $this->authorize('view', [$setting, $hotel]);

        return response()->json([
            'success' => true,
            'message' => 'Hotel Settings Retrieved Successfully.',
            'data' => new HotelSettingResource($setting),
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/hotels/{hotel}/settings",
     *     summary="Update hotel configuration settings",
     *     tags={"Hotel Settings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="hotel", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="currency", type="string", example="KES"),
     *             @OA\Property(property="timezone", type="string", example="Africa/Nairobi"),
     *             @OA\Property(property="language", type="string", example="en")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Hotel settings updated successfully"),
     *     @OA\Response(response=422, description="Validation failed")
     * )
     */
    public function update(UpdateHotelSettingRequest $request, Hotel $hotel): JsonResponse
    {
        $setting = $this->hotelSettingService->getSettings($hotel);

        $this->authorize('update', [$setting, $hotel]);

        $updatedSetting = $this->hotelSettingService->update($setting, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Hotel Settings Updated Successfully.',
            'data' => new HotelSettingResource($updatedSetting),
        ]);
    }
}
