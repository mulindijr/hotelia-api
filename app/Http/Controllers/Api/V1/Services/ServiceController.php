<?php

namespace App\Http\Controllers\Api\V1\Services;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Services\StoreServiceRequest;
use App\Http\Requests\Api\V1\Services\UpdateServiceRequest;
use App\Http\Resources\Api\V1\Services\ServiceResource;
use App\Models\Hotel;
use App\Models\Service;
use App\Services\Hotel\AncillaryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected AncillaryService $ancillaryService
    ) {}

    /**
     * Display a listing of ancillary services for a hotel.
     */
    public function index(Request $request, Hotel $hotel): JsonResponse
    {
        $this->authorize('viewAny', [Service::class, $hotel]);

        $services = $this->ancillaryService->getServices($hotel);

        return response()->json([
            'success' => true,
            'message' => 'Services Retrieved Successfully.',
            'data' => ServiceResource::collection($services),
        ]);
    }

    /**
     * Store a newly created ancillary service.
     */
    public function store(StoreServiceRequest $request, Hotel $hotel): JsonResponse
    {
        $this->authorize('create', [Service::class, $hotel]);

        $service = $this->ancillaryService->create($hotel, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Service Created Successfully.',
            'data' => new ServiceResource($service),
        ], 201);
    }

    /**
     * Display the specified service details.
     */
    public function show(Hotel $hotel, Service $service): JsonResponse
    {
        $this->authorize('view', [$service, $hotel]);

        return response()->json([
            'success' => true,
            'message' => 'Service Retrieved Successfully.',
            'data' => new ServiceResource($service),
        ]);
    }

    /**
     * Update the specified service.
     */
    public function update(UpdateServiceRequest $request, Hotel $hotel, Service $service): JsonResponse
    {
        $this->authorize('update', [$service, $hotel]);

        $updatedService = $this->ancillaryService->update($service, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Service Updated Successfully.',
            'data' => new ServiceResource($updatedService),
        ]);
    }

    /**
     * Remove the specified service.
     */
    public function destroy(Hotel $hotel, Service $service): JsonResponse
    {
        $this->authorize('delete', [$service, $hotel]);

        $this->ancillaryService->delete($service);

        return response()->json([
            'success' => true,
            'message' => 'Service Deleted Successfully.',
        ]);
    }
}
