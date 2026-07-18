<?php

namespace App\Http\Controllers\Api\V1\Hotels;

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

class MaintenanceController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected MaintenanceService $maintenanceService
    ) {}

    /**
     * Display a listing of maintenance requests for a hotel.
     */
    public function index(Request $request, Hotel $hotel): JsonResponse
    {
        if (!$request->user()->belongsToHotel($hotel->id) && !$request->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized hotel scope.');
        }

        // Retrieve rooms ids for this hotel
        $roomIds = $hotel->rooms()->pluck('id');

        $tasks = MaintenanceRequest::whereIn('room_id', $roomIds)->with('room')->get();

        return response()->json([
            'success' => true,
            'message' => 'Maintenance Requests Retrieved Successfully.',
            'data' => MaintenanceRequestResource::collection($tasks),
        ]);
    }

    /**
     * Store a newly created maintenance request.
     */
    public function store(StoreMaintenanceRequest $request, Hotel $hotel): JsonResponse
    {
        if (!$request->user()->belongsToHotel($hotel->id) && !$request->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized hotel scope.');
        }

        $maintenanceRequest = $this->maintenanceService->create($hotel, $request->validated(), $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Maintenance Request Created Successfully.',
            'data' => new MaintenanceRequestResource($maintenanceRequest),
        ], 201);
    }

    /**
     * Display the specified maintenance request.
     */
    public function show(Hotel $hotel, MaintenanceRequest $maintenanceRequest): JsonResponse
    {
        $this->authorize('view', $maintenanceRequest);

        return response()->json([
            'success' => true,
            'message' => 'Maintenance Request Retrieved Successfully.',
            'data' => new MaintenanceRequestResource($maintenanceRequest->load('room')),
        ]);
    }

    /**
     * Update the specified maintenance request.
     */
    public function update(UpdateMaintenanceRequest $request, Hotel $hotel, MaintenanceRequest $maintenanceRequest): JsonResponse
    {
        $this->authorize('update', $maintenanceRequest);

        $updatedRequest = $this->maintenanceService->update($maintenanceRequest, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Maintenance Request Updated Successfully.',
            'data' => new MaintenanceRequestResource($updatedRequest),
        ]);
    }

    /**
     * Remove the specified maintenance request.
     */
    public function destroy(Hotel $hotel, MaintenanceRequest $maintenanceRequest): JsonResponse
    {
        $this->authorize('delete', $maintenanceRequest);

        $this->maintenanceService->delete($maintenanceRequest);

        return response()->json([
            'success' => true,
            'message' => 'Maintenance Request Deleted Successfully.',
        ]);
    }
}
