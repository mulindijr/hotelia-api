<?php

namespace App\Http\Controllers\Api\V1\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Maintenance\StoreMaintenanceRequest;
use App\Http\Requests\Api\V1\Maintenance\UpdateMaintenanceRequest;
use App\Http\Resources\Api\V1\Maintenance\MaintenanceRequestResource;
use App\Models\Hotel;
use App\Models\MaintenanceRequest;
use App\Services\Maintenance\MaintenanceService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\QueryBuilder;

#[OA\Tag(name: 'Maintenance', description: 'Room maintenance requests and issue tracking operations')]
class MaintenanceController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected MaintenanceService $maintenanceService
    ) {}

    #[OA\Get(
        path: '/api/v1/hotels/{hotel}/maintenance',
        summary: 'List all maintenance requests for a hotel',
        tags: ['Maintenance'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Maintenance requests list retrieved'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(Request $request, Hotel $hotel): JsonResponse
    {
        $this->authorize('viewAny', [MaintenanceRequest::class, $hotel]);

        // Retrieve rooms ids for this hotel
        $roomIds = $hotel->rooms()->pluck('id');

        $requests = QueryBuilder::for(MaintenanceRequest::class)
            ->whereIn('room_id', $roomIds)
            ->allowedFilters(...[
                'status',
                'priority',
                'room_id',
            ])
            ->with('room')
            ->paginate($request->query('per_page', 15));

        return MaintenanceRequestResource::collection($requests)->additional([
            'success' => true,
            'message' => 'Maintenance Requests Retrieved Successfully.',
        ])->response();
    }

    #[OA\Post(
        path: '/api/v1/hotels/{hotel}/maintenance',
        summary: 'Create a new maintenance request',
        tags: ['Maintenance'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['room_id', 'description'],
                properties: [
                    new OA\Property(property: 'room_id', type: 'integer', example: 1),
                    new OA\Property(property: 'description', type: 'string', example: 'Leaking showerhead'),
                    new OA\Property(property: 'priority', type: 'string', enum: ['low', 'medium', 'high', 'critical'], example: 'medium'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Maintenance request created successfully'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
    public function store(StoreMaintenanceRequest $request, Hotel $hotel): JsonResponse
    {
        $this->authorize('create', [MaintenanceRequest::class, $hotel]);

        $maintenanceRequest = $this->maintenanceService->create($hotel, $request->validated(), $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Maintenance Request Created Successfully.',
            'data' => new MaintenanceRequestResource($maintenanceRequest),
        ], 201);
    }

    #[OA\Get(
        path: '/api/v1/hotels/{hotel}/maintenance/{maintenanceRequest}',
        summary: 'Get maintenance request details',
        tags: ['Maintenance'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'maintenanceRequest', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Maintenance request details retrieved'),
            new OA\Response(response: 404, description: 'Maintenance request not found'),
        ]
    )]
    public function show(Hotel $hotel, MaintenanceRequest $maintenanceRequest): JsonResponse
    {
        $this->authorize('view', [$maintenanceRequest, $hotel]);

        return response()->json([
            'success' => true,
            'message' => 'Maintenance Request Retrieved Successfully.',
            'data' => new MaintenanceRequestResource($maintenanceRequest->load('room')),
        ]);
    }

    #[OA\Put(
        path: '/api/v1/hotels/{hotel}/maintenance/{maintenanceRequest}',
        summary: 'Update maintenance request status or details',
        tags: ['Maintenance'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'maintenanceRequest', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Maintenance request updated successfully'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
    public function update(UpdateMaintenanceRequest $request, Hotel $hotel, MaintenanceRequest $maintenanceRequest): JsonResponse
    {
        $this->authorize('update', [$maintenanceRequest, $hotel]);

        $updatedRequest = $this->maintenanceService->update($maintenanceRequest, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Maintenance Request Updated Successfully.',
            'data' => new MaintenanceRequestResource($updatedRequest),
        ]);
    }

    #[OA\Delete(
        path: '/api/v1/hotels/{hotel}/maintenance/{maintenanceRequest}',
        summary: 'Delete a maintenance request',
        tags: ['Maintenance'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'maintenanceRequest', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Maintenance request deleted successfully'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function destroy(Hotel $hotel, MaintenanceRequest $maintenanceRequest): JsonResponse
    {
        $this->authorize('delete', [$maintenanceRequest, $hotel]);

        $this->maintenanceService->delete($maintenanceRequest);

        return response()->json([
            'success' => true,
            'message' => 'Maintenance Request Deleted Successfully.',
        ]);
    }
}
