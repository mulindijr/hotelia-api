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
use Spatie\QueryBuilder\QueryBuilder;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Services", description: "Ancillary hotel service endpoints")]
class ServiceController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected AncillaryService $ancillaryService
    ) {}

    #[OA\Get(
        path: "/api/v1/hotels/{hotel}/services",
        summary: "Display a listing of ancillary services for a hotel",
        tags: ["Services"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "hotel", in: "path", required: true, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 15)),
            new OA\Parameter(name: "filter[name]", in: "query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "filter[is_active]", in: "query", required: false, schema: new OA\Schema(type: "boolean"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Services retrieved successfully"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden")
        ]
    )]
    public function index(Request $request, Hotel $hotel): JsonResponse
    {
        $this->authorize('viewAny', [Service::class, $hotel]);

        $services = QueryBuilder::for(Service::class)
            ->where('hotel_id', $hotel->id)
            ->allowedFilters(...[
                'name',
                'is_active',
            ])
            ->paginate($request->query('per_page', 15));

        return ServiceResource::collection($services)->additional([
            'success' => true,
            'message' => 'Services Retrieved Successfully.',
        ])->response();
    }

    #[OA\Post(
        path: "/api/v1/hotels/{hotel}/services",
        summary: "Store a newly created ancillary service",
        tags: ["Services"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "hotel", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "price"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Laundry Service"),
                    new OA\Property(property: "description", type: "string", example: "Wash and iron laundry services"),
                    new OA\Property(property: "price", type: "number", format: "float", example: 500.00),
                    new OA\Property(property: "is_active", type: "boolean", example: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Service created successfully"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 422, description: "Validation failed")
        ]
    )]
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

    #[OA\Get(
        path: "/api/v1/hotels/{hotel}/services/{service}",
        summary: "Display the specified service details",
        tags: ["Services"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "hotel", in: "path", required: true, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "service", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Service details retrieved successfully"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 404, description: "Service not found")
        ]
    )]
    public function show(Hotel $hotel, Service $service): JsonResponse
    {
        $this->authorize('view', [$service, $hotel]);

        return response()->json([
            'success' => true,
            'message' => 'Service Retrieved Successfully.',
            'data' => new ServiceResource($service),
        ]);
    }

    #[OA\Put(
        path: "/api/v1/hotels/{hotel}/services/{service}",
        summary: "Update the specified service",
        tags: ["Services"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "hotel", in: "path", required: true, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "service", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Laundry Service"),
                    new OA\Property(property: "description", type: "string", example: "Wash and iron laundry services"),
                    new OA\Property(property: "price", type: "number", format: "float", example: 500.00),
                    new OA\Property(property: "is_active", type: "boolean", example: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Service updated successfully"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 404, description: "Service not found"),
            new OA\Response(response: 422, description: "Validation failed")
        ]
    )]
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

    #[OA\Delete(
        path: "/api/v1/hotels/{hotel}/services/{service}",
        summary: "Remove the specified service",
        tags: ["Services"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "hotel", in: "path", required: true, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "service", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Service deleted successfully"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 404, description: "Service not found")
        ]
    )]
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
