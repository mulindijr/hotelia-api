<?php

namespace App\Http\Controllers\Api\V1\Rooms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Rooms\StoreAmenityRequest;
use App\Http\Requests\Api\V1\Rooms\UpdateAmenityRequest;
use App\Http\Resources\Api\V1\Rooms\AmenityResource;
use App\Models\Amenity;
use App\Services\Room\AmenityService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class AmenityController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected AmenityService $amenityService
    ) {}

    /**
     * Display a listing of amenities.
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Amenity::class);

        $amenities = Amenity::all();

        return response()->json([
            'success' => true,
            'message' => 'Amenities Retrieved Successfully.',
            'data' => AmenityResource::collection($amenities),
        ]);
    }

    /**
     * Store a newly created amenity.
     */
    public function store(StoreAmenityRequest $request): JsonResponse
    {
        $this->authorize('create', Amenity::class);

        $amenity = $this->amenityService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Amenity Created Successfully.',
            'data' => new AmenityResource($amenity),
        ], 201);
    }

    /**
     * Update the specified amenity.
     */
    public function update(UpdateAmenityRequest $request, Amenity $amenity): JsonResponse
    {
        $this->authorize('update', $amenity);

        $updatedAmenity = $this->amenityService->update($amenity, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Amenity Updated Successfully.',
            'data' => new AmenityResource($updatedAmenity),
        ]);
    }

    /**
     * Remove the specified amenity.
     */
    public function destroy(Amenity $amenity): JsonResponse
    {
        $this->authorize('delete', $amenity);

        $this->amenityService->delete($amenity);

        return response()->json([
            'success' => true,
            'message' => 'Amenity Deleted Successfully.',
        ]);
    }
}
