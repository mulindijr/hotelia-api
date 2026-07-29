<?php

namespace App\Http\Controllers\Api\V1\Guests;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Guests\StoreGuestRequest;
use App\Http\Requests\Api\V1\Guests\UpdateGuestRequest;
use App\Http\Resources\Api\V1\Bookings\BookingResource;
use App\Http\Resources\Api\V1\Guests\GuestResource;
use App\Models\Guest;
use App\Services\Guest\GuestService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

#[OA\Tag(name: 'Guests', description: 'Guest management operations')]
class GuestController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected GuestService $guestService
    ) {}

    #[OA\Get(
        path: '/api/v1/guests',
        summary: 'Display a listing of guests',
        tags: ['Guests'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
            new OA\Parameter(name: 'filter[first_name]', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[last_name]', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[email]', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[phone]', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[search]', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Guests retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Guest::class);

        $guests = QueryBuilder::for(Guest::class)
            ->allowedFilters(
                'first_name',
                'last_name',
                'email',
                'phone',
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->where('first_name', 'like', "%{$value}%")
                            ->orWhere('last_name', 'like', "%{$value}%")
                            ->orWhere('email', 'like', "%{$value}%")
                            ->orWhere('phone', 'like', "%{$value}%");
                    });
                })
            )
            ->paginate($request->query('per_page', 15));

        return GuestResource::collection($guests)->additional([
            'success' => true,
            'message' => 'Guests Retrieved Successfully.',
        ])->response();
    }

    #[OA\Post(
        path: '/api/v1/guests',
        summary: 'Store a newly created guest',
        tags: ['Guests'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['first_name', 'last_name', 'email', 'phone'],
                properties: [
                    new OA\Property(property: 'first_name', type: 'string', example: 'John'),
                    new OA\Property(property: 'last_name', type: 'string', example: 'Doe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john.doe@example.com'),
                    new OA\Property(property: 'phone', type: 'string', example: '+254712345678'),
                    new OA\Property(property: 'nationality', type: 'string', example: 'Kenyan'),
                    new OA\Property(property: 'national_id', type: 'string', example: '12345678'),
                    new OA\Property(property: 'passport_number', type: 'string', example: 'A1234567B'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Guest created successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
    public function store(StoreGuestRequest $request): JsonResponse
    {
        $this->authorize('create', Guest::class);

        $guest = $this->guestService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Guest Created Successfully.',
            'data' => new GuestResource($guest),
        ], 201);
    }

    #[OA\Get(
        path: '/api/v1/guests/{guest}',
        summary: 'Display the specified guest details',
        tags: ['Guests'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'guest', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Guest details retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Guest not found'),
        ]
    )]
    public function show(Guest $guest): JsonResponse
    {
        $this->authorize('view', $guest);

        return response()->json([
            'success' => true,
            'message' => 'Guest Retrieved Successfully.',
            'data' => new GuestResource($guest),
        ]);
    }

    #[OA\Put(
        path: '/api/v1/guests/{guest}',
        summary: 'Update the specified guest',
        tags: ['Guests'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'guest', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'first_name', type: 'string', example: 'John'),
                    new OA\Property(property: 'last_name', type: 'string', example: 'Doe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john.doe@example.com'),
                    new OA\Property(property: 'phone', type: 'string', example: '+254712345678'),
                    new OA\Property(property: 'nationality', type: 'string', example: 'Kenyan'),
                    new OA\Property(property: 'national_id', type: 'string', example: '12345678'),
                    new OA\Property(property: 'passport_number', type: 'string', example: 'A1234567B'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Guest updated successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Guest not found'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
    public function update(UpdateGuestRequest $request, Guest $guest): JsonResponse
    {
        $this->authorize('update', $guest);

        $updatedGuest = $this->guestService->update($guest, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Guest Updated Successfully.',
            'data' => new GuestResource($updatedGuest),
        ]);
    }

    #[OA\Delete(
        path: '/api/v1/guests/{guest}',
        summary: 'Remove the specified guest',
        tags: ['Guests'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'guest', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Guest deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Guest not found'),
        ]
    )]
    public function destroy(Guest $guest): JsonResponse
    {
        $this->authorize('delete', $guest);

        $this->guestService->delete($guest);

        return response()->json([
            'success' => true,
            'message' => 'Guest Deleted Successfully.',
        ]);
    }

    #[OA\Get(
        path: '/api/v1/guests/{guest}/bookings',
        summary: "Display a listing of the guest's bookings",
        tags: ['Guests'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'guest', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Guest bookings retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Guest not found'),
        ]
    )]
    public function bookings(Request $request, Guest $guest): JsonResponse
    {
        $this->authorize('view', $guest);

        $user = $request->user();

        $query = $guest->bookings()->with(['guest', 'rooms.roomType', 'services']);

        if (! $user->hasRole('super_admin')) {
            $hotelIds = $user->hotels()->pluck('hotels.id');
            $query->whereIn('hotel_id', $hotelIds);
        }

        $bookings = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Guest Bookings Retrieved Successfully.',
            'data' => BookingResource::collection($bookings),
        ]);
    }
}
