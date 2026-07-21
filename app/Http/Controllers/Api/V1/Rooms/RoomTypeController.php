<?php

namespace App\Http\Controllers\Api\V1\Rooms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Rooms\StoreRoomTypeRequest;
use App\Http\Requests\Api\V1\Rooms\UpdateRoomTypeRequest;
use App\Http\Resources\Api\V1\Rooms\RoomTypeResource;
use App\Models\Hotel;
use App\Models\RoomType;
use App\Services\Room\RoomTypeService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Room Types",
 *     description="Room type categories and pricing management"
 * )
 */
class RoomTypeController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected RoomTypeService $roomTypeService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/hotels/{hotel}/room-types",
     *     summary="List all room types for a hotel",
     *     tags={"Room Types"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="hotel", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Room types list retrieved"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request, Hotel $hotel): JsonResponse
    {
        $this->authorize('viewAny', [RoomType::class, $hotel]);

        $roomTypes = $this->roomTypeService->getRoomTypes($hotel);

        return response()->json([
            'success' => true,
            'message' => 'Room Types Retrieved Successfully.',
            'data' => RoomTypeResource::collection($roomTypes),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/hotels/{hotel}/room-types",
     *     summary="Create a new room type",
     *     tags={"Room Types"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="hotel", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "base_price", "capacity", "beds"},
     *             @OA\Property(property="name", type="string", example="Executive Suite"),
     *             @OA\Property(property="base_price", type="number", format="float", example=150.00),
     *             @OA\Property(property="capacity", type="integer", example=2),
     *             @OA\Property(property="beds", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Room type created successfully"),
     *     @OA\Response(response=422, description="Validation failed")
     * )
     */
    public function store(StoreRoomTypeRequest $request, Hotel $hotel): JsonResponse
    {
        $this->authorize('create', [RoomType::class, $hotel]);

        $roomType = $this->roomTypeService->create($hotel, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Room Type Created Successfully.',
            'data' => new RoomTypeResource($roomType),
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/hotels/{hotel}/room-types/{roomType}",
     *     summary="Get room type details",
     *     tags={"Room Types"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="hotel", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="roomType", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Room type details retrieved"),
     *     @OA\Response(response=404, description="Room type not found")
     * )
     */
    public function show(Hotel $hotel, RoomType $roomType): JsonResponse
    {
        $this->authorize('view', [$roomType, $hotel]);

        return response()->json([
            'success' => true,
            'message' => 'Room Type Retrieved Successfully.',
            'data' => new RoomTypeResource($roomType->load('amenities')),
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/hotels/{hotel}/room-types/{roomType}",
     *     summary="Update room type details",
     *     tags={"Room Types"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="hotel", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="roomType", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Room type updated successfully"),
     *     @OA\Response(response=422, description="Validation failed")
     * )
     */
    public function update(UpdateRoomTypeRequest $request, Hotel $hotel, RoomType $roomType): JsonResponse
    {
        $this->authorize('update', [$roomType, $hotel]);

        $updatedRoomType = $this->roomTypeService->update($roomType, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Room Type Updated Successfully.',
            'data' => new RoomTypeResource($updatedRoomType),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/hotels/{hotel}/room-types/{roomType}",
     *     summary="Delete a room type",
     *     tags={"Room Types"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="hotel", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="roomType", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Room type deleted successfully"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function destroy(Hotel $hotel, RoomType $roomType): JsonResponse
    {
        $this->authorize('delete', [$roomType, $hotel]);

        $this->roomTypeService->delete($roomType);

        return response()->json([
            'success' => true,
            'message' => 'Room Type Deleted Successfully.',
        ]);
    }
}
