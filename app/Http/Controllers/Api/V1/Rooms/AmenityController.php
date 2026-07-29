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
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\QueryBuilder;

#[OA\Tag(name: 'Amenities', description: 'Global and room amenity management operations')]
class AmenityController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected AmenityService $amenityService
    ) {}

    #[OA\Get(
        path: '/api/v1/amenities',
        summary: 'Display a listing of amenities',
        tags: ['Amenities'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
            new OA\Parameter(name: 'filter[name]', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Amenities list retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Amenity::class);

        $amenities = QueryBuilder::for(Amenity::class)
            ->allowedFilters(...[
                'name',
            ])
            ->paginate($request->query('per_page', 15));

        return AmenityResource::collection($amenities)->additional([
            'success' => true,
            'message' => 'Amenities Retrieved Successfully.',
        ])->response();
    }

    #[OA\Post(
        path: '/api/v1/amenities',
        summary: 'Store a newly created amenity',
        tags: ['Amenities'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'WiFi'),
                    new OA\Property(property: 'description', type: 'string', example: 'High-speed wireless internet access'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Amenity created successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
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

    #[OA\Put(
        path: '/api/v1/amenities/{amenity}',
        summary: 'Update the specified amenity',
        tags: ['Amenities'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'amenity', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'WiFi'),
                    new OA\Property(property: 'description', type: 'string', example: 'High-speed wireless internet access'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Amenity updated successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Amenity not found'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
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

    #[OA\Delete(
        path: '/api/v1/amenities/{amenity}',
        summary: 'Remove the specified amenity',
        tags: ['Amenities'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'amenity', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Amenity deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Amenity not found'),
        ]
    )]
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
