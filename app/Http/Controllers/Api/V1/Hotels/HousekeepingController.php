<?php

namespace App\Http\Controllers\Api\V1\Hotels;

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

class HousekeepingController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected HousekeepingService $housekeepingService
    ) {}

    /**
     * Display a listing of housekeeping tasks for a hotel.
     */
    public function index(Request $request, Hotel $hotel): JsonResponse
    {
        $this->authorize('viewAny', [HousekeepingTask::class, $hotel]);

        // Retrieve rooms ids for this hotel
        $roomIds = $hotel->rooms()->pluck('id');

        $tasks = HousekeepingTask::whereIn('room_id', $roomIds)->with('room')->get();

        return response()->json([
            'success' => true,
            'message' => 'Housekeeping Tasks Retrieved Successfully.',
            'data' => HousekeepingTaskResource::collection($tasks),
        ]);
    }

    /**
     * Store a newly created housekeeping task.
     */
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

    /**
     * Display the specified housekeeping task.
     */
    public function show(Hotel $hotel, HousekeepingTask $task): JsonResponse
    {
        $this->authorize('view', [$task, $hotel]);

        return response()->json([
            'success' => true,
            'message' => 'Housekeeping Task Retrieved Successfully.',
            'data' => new HousekeepingTaskResource($task->load('room')),
        ]);
    }

    /**
     * Update the specified housekeeping task.
     */
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

    /**
     * Remove the specified housekeeping task.
     */
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
