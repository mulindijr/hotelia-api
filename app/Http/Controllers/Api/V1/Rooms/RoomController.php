<?php

namespace App\Http\Controllers\Api\V1\Rooms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Rooms\StoreRoomRequest;
use App\Http\Requests\Api\V1\Rooms\UpdateRoomRequest;
use App\Http\Resources\Api\V1\Rooms\RoomResource;
use App\Models\Hotel;
use App\Models\Room;
use App\Services\Room\RoomService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected RoomService $roomService
    ) {}

    /**
     * Display a listing of the hotel's rooms.
     */
    public function index(Request $request, Hotel $hotel): JsonResponse
    {
        if (!$request->user()->belongsToHotel($hotel->id) && !$request->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized hotel scope.');
        }

        $rooms = $hotel->rooms()->with('roomType')->get();

        return response()->json([
            'success' => true,
            'message' => 'Rooms Retrieved Successfully.',
            'data' => RoomResource::collection($rooms),
        ]);
    }

    /**
     * Store a newly created room.
     */
    public function store(StoreRoomRequest $request, Hotel $hotel): JsonResponse
    {
        if (!$request->user()->belongsToHotel($hotel->id) && !$request->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized hotel scope.');
        }

        $room = $this->roomService->create($hotel, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Room Created Successfully.',
            'data' => new RoomResource($room),
        ], 201);
    }

    /**
     * Display the specified room details.
     */
    public function show(Hotel $hotel, Room $room): JsonResponse
    {
        $this->authorize('view', $room);

        return response()->json([
            'success' => true,
            'message' => 'Room Retrieved Successfully.',
            'data' => new RoomResource($room->load('roomType')),
        ]);
    }

    /**
     * Update the specified room.
     */
    public function update(UpdateRoomRequest $request, Hotel $hotel, Room $room): JsonResponse
    {
        $this->authorize('update', $room);

        $updatedRoom = $this->roomService->update($room, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Room Updated Successfully.',
            'data' => new RoomResource($updatedRoom),
        ]);
    }

    /**
     * Remove the specified room.
     */
    public function destroy(Hotel $hotel, Room $room): JsonResponse
    {
        $this->authorize('delete', $room);

        $this->roomService->delete($room);

        return response()->json([
            'success' => true,
            'message' => 'Room Deleted Successfully.',
        ]);
    }
}
