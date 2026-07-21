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

class RoomTypeController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected RoomTypeService $roomTypeService
    ) {}

    /**
     * Display a listing of the hotel's room types.
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
     * Store a newly created room type.
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
     * Display the specified room type details.
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
     * Update the specified room type.
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
     * Remove the specified room type.
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
