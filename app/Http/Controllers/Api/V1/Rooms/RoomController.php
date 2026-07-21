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

use OpenApi\Attributes as OA;

#[OA\Tag(name: "Rooms", description: "Physical hotel room inventory management")]
class RoomController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected RoomService $roomService
    ) {}

    #[OA\Get(
        path: "/api/v1/hotels/{hotel}/rooms",
        summary: "List all rooms in a hotel",
        tags: ["Rooms"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "hotel", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Rooms list retrieved"),
            new OA\Response(response: 403, description: "Forbidden")
        ]
    )]
    public function index(Request $request, Hotel $hotel): JsonResponse
    {
        $this->authorize('viewAny', [Room::class, $hotel]);

        $rooms = $hotel->rooms()->with('roomType')->get();

        return response()->json([
            'success' => true,
            'message' => 'Rooms Retrieved Successfully.',
            'data' => RoomResource::collection($rooms),
        ]);
    }

    #[OA\Post(
        path: "/api/v1/hotels/{hotel}/rooms",
        summary: "Create a new physical room",
        tags: ["Rooms"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "hotel", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["room_type_id", "room_number", "floor"],
                properties: [
                    new OA\Property(property: "room_type_id", type: "integer", example: 1),
                    new OA\Property(property: "room_number", type: "string", example: "101"),
                    new OA\Property(property: "floor", type: "integer", example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Room created successfully"),
            new OA\Response(response: 422, description: "Validation failed")
        ]
    )]
    public function store(StoreRoomRequest $request, Hotel $hotel): JsonResponse
    {
        $this->authorize('create', [Room::class, $hotel]);

        $room = $this->roomService->create($hotel, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Room Created Successfully.',
            'data' => new RoomResource($room),
        ], 201);
    }

    #[OA\Get(
        path: "/api/v1/hotels/{hotel}/rooms/{room}",
        summary: "Get room details",
        tags: ["Rooms"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "hotel", in: "path", required: true, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "room", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Room details retrieved"),
            new OA\Response(response: 404, description: "Room not found")
        ]
    )]
    public function show(Hotel $hotel, Room $room): JsonResponse
    {
        $this->authorize('view', [$room, $hotel]);

        return response()->json([
            'success' => true,
            'message' => 'Room Retrieved Successfully.',
            'data' => new RoomResource($room->load('roomType')),
        ]);
    }

    #[OA\Put(
        path: "/api/v1/hotels/{hotel}/rooms/{room}",
        summary: "Update room details or status",
        tags: ["Rooms"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "hotel", in: "path", required: true, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "room", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Room updated successfully"),
            new OA\Response(response: 422, description: "Validation failed")
        ]
    )]
    public function update(UpdateRoomRequest $request, Hotel $hotel, Room $room): JsonResponse
    {
        $this->authorize('update', [$room, $hotel]);

        $updatedRoom = $this->roomService->update($room, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Room Updated Successfully.',
            'data' => new RoomResource($updatedRoom),
        ]);
    }

    #[OA\Delete(
        path: "/api/v1/hotels/{hotel}/rooms/{room}",
        summary: "Soft delete a room",
        tags: ["Rooms"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "hotel", in: "path", required: true, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "room", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Room deleted successfully"),
            new OA\Response(response: 403, description: "Forbidden")
        ]
    )]
    public function destroy(Hotel $hotel, Room $room): JsonResponse
    {
        $this->authorize('delete', [$room, $hotel]);

        $this->roomService->delete($room);

        return response()->json([
            'success' => true,
            'message' => 'Room Deleted Successfully.',
        ]);
    }
}
