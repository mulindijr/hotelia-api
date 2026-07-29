<?php

namespace App\Http\Controllers\Api\V1\Hotels;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Hotels\StoreHotelRequest;
use App\Http\Requests\Api\V1\Hotels\UpdateHotelRequest;
use App\Http\Resources\Api\V1\Hotels\HotelResource;
use App\Models\Hotel;
use App\Services\Hotel\HotelService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag(name: 'Hotels', description: 'Multi-tenant hotel management endpoints')]
class HotelController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected HotelService $hotelService
    ) {}

    #[OA\Get(
        path: '/api/v1/hotels',
        summary: 'List all accessible hotels for the user',
        tags: ['Hotels'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Hotels retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Hotel::class);

        $query = Hotel::query()->with('settings');

        if (! $request->user()->hasRole('super_admin')) {
            $query->whereHas('users', function ($q) use ($request) {
                $q->where('users.id', $request->user()->id);
            });
        }

        $perPage = min(request()->integer('per_page', 15), 100);

        $hotels = QueryBuilder::for($query)
            ->allowedFilters(
                'name',
                'city',
                'country',
                'is_active'
            )
            ->allowedSorts(
                'name',
                'created_at'
            )
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'message' => 'Hotels Retrieved Successfully.',
            'data' => HotelResource::collection($hotels)->resolve(),
            'meta' => [
                'current_page' => $hotels->currentPage(),
                'last_page' => $hotels->lastPage(),
                'per_page' => $hotels->perPage(),
                'total' => $hotels->total(),
            ],
        ]);
    }

    #[OA\Post(
        path: '/api/v1/hotels',
        summary: 'Create a new hotel',
        tags: ['Hotels'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'phone', 'country', 'city'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Grand Hotelia'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'info@grandhotelia.com'),
                    new OA\Property(property: 'phone', type: 'string', example: '+254700000000'),
                    new OA\Property(property: 'country', type: 'string', example: 'Kenya'),
                    new OA\Property(property: 'city', type: 'string', example: 'Nairobi'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Hotel created successfully'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
    public function store(StoreHotelRequest $request): JsonResponse
    {
        $this->authorize('create', Hotel::class);

        $hotel = $this->hotelService->create(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'message' => 'Hotel Created Successfully.',
            'data' => new HotelResource($hotel),
        ], Response::HTTP_CREATED);
    }

    #[OA\Get(
        path: '/api/v1/hotels/{hotel}',
        summary: 'Get hotel details by ID',
        tags: ['Hotels'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Hotel details retrieved'),
            new OA\Response(response: 404, description: 'Hotel not found'),
        ]
    )]
    public function show(Hotel $hotel): JsonResponse
    {
        $this->authorize('view', $hotel);

        $hotel->load([
            'settings',
            'roomTypes',
            'rooms',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Hotel Retrieved Successfully.',
            'data' => new HotelResource($hotel),
        ]);
    }

    #[OA\Put(
        path: '/api/v1/hotels/{hotel}',
        summary: 'Update hotel profile',
        tags: ['Hotels'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Hotel updated successfully'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Hotel not found'),
        ]
    )]
    public function update(UpdateHotelRequest $request, Hotel $hotel): JsonResponse
    {
        $this->authorize('update', $hotel);

        $hotel = $this->hotelService->update(
            $hotel,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Hotel Updated Successfully.',
            'data' => new HotelResource($hotel),
        ]);
    }

    #[OA\Delete(
        path: '/api/v1/hotels/{hotel}',
        summary: 'Soft delete a hotel',
        tags: ['Hotels'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Hotel deleted successfully'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function destroy(Hotel $hotel): JsonResponse
    {
        $this->authorize('delete', $hotel);

        $this->hotelService->delete($hotel);

        return response()->json([
            'success' => true,
            'message' => 'Hotel Deleted Successfully.',
            'data' => null,
        ]);
    }
}
