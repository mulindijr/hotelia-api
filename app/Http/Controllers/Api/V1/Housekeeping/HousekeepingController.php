<?php

namespace App\Http\Controllers\Api\V1\Housekeeping;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Housekeeping\StoreHousekeepingTaskRequest;
use App\Http\Requests\Api\V1\Housekeeping\UpdateHousekeepingTaskRequest;
use App\Http\Resources\Api\V1\Housekeeping\HousekeepingTaskResource;
use App\Models\Hotel;
use App\Models\HousekeepingTask;
use App\Services\Housekeeping\HousekeepingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\QueryBuilder;

#[OA\Tag(name: 'Housekeeping', description: 'Room cleaning tasks and assignment operations')]
class HousekeepingController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected HousekeepingService $housekeepingService
    ) {}

    #[OA\Get(
        path: '/api/v1/hotels/{hotel}/housekeeping',
        summary: 'List all housekeeping tasks for a hotel',
        tags: ['Housekeeping'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Tasks list retrieved'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(Request $request, Hotel $hotel): JsonResponse
    {
        $this->authorize('viewAny', [HousekeepingTask::class, $hotel]);

        // Retrieve rooms ids for this hotel
        $roomIds = $hotel->rooms()->pluck('id');

        $tasks = QueryBuilder::for(HousekeepingTask::class)
            ->whereIn('room_id', $roomIds)
            ->allowedFilters(...[
                'status',
                'assigned_to',
                'room_id',
            ])
            
            ->allowedIncludes(['room', 'assignedTo'])->with(['room', 'assignedTo'])
            ->paginate($request->query('per_page', 15));

        return HousekeepingTaskResource::collection($tasks)->additional([
            'success' => true,
            'message' => 'Housekeeping Tasks Retrieved Successfully.',
        ])->response();
    }

    #[OA\Post(
        path: '/api/v1/hotels/{hotel}/housekeeping',
        summary: 'Create and assign a housekeeping task',
        tags: ['Housekeeping'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['room_id'],
                properties: [
                    new OA\Property(property: 'room_id', type: 'integer', example: 1),
                    new OA\Property(property: 'assigned_to', type: 'integer', example: 2),
                    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'in_progress', 'completed'], example: 'pending'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Task created successfully'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
    public function store(StoreHousekeepingTaskRequest $request, Hotel $hotel): JsonResponse
    {
        $this->authorize('create', [HousekeepingTask::class, $hotel]);

        $task = $this->housekeepingService->create($hotel, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Housekeeping Task Created Successfully.',
            'data' => new HousekeepingTaskResource($task),
        ], 201);
    }

    #[OA\Get(
        path: '/api/v1/hotels/{hotel}/housekeeping/{task}',
        summary: 'Get housekeeping task details',
        tags: ['Housekeeping'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'task', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Task details retrieved'),
            new OA\Response(response: 404, description: 'Task not found'),
        ]
    )]
    public function show(Hotel $hotel, HousekeepingTask $task): JsonResponse
    {
        $this->authorize('view', [$task, $hotel]);

        return response()->json([
            'success' => true,
            'message' => 'Housekeeping Task Retrieved Successfully.',
            'data' => new HousekeepingTaskResource($task->load('room')),
        ]);
    }

    #[OA\Put(
        path: '/api/v1/hotels/{hotel}/housekeeping/{task}',
        summary: 'Update housekeeping task status',
        tags: ['Housekeeping'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'task', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Task updated successfully'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
    public function update(UpdateHousekeepingTaskRequest $request, Hotel $hotel, HousekeepingTask $task): JsonResponse
    {
        $this->authorize('update', [$task, $hotel]);

        $updatedTask = $this->housekeepingService->update($task, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Housekeeping Task Updated Successfully.',
            'data' => new HousekeepingTaskResource($updatedTask),
        ]);
    }

    #[OA\Delete(
        path: '/api/v1/hotels/{hotel}/housekeeping/{task}',
        summary: 'Delete a housekeeping task',
        tags: ['Housekeeping'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'task', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Task deleted successfully'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function destroy(Hotel $hotel, HousekeepingTask $task): JsonResponse
    {
        $this->authorize('delete', [$task, $hotel]);

        $this->housekeepingService->delete($task);

        return response()->json([
            'success' => true,
            'message' => 'Housekeeping Task Deleted Successfully.',
        ]);
    }
}
