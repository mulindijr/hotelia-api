<?php

namespace App\Http\Controllers\Api\V1\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Users\StoreUserRequest;
use App\Http\Requests\Api\V1\Users\UpdateUserRequest;
use App\Http\Resources\Api\V1\Users\UserResource;
use App\Models\Hotel;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\QueryBuilder;

#[OA\Tag(name: 'Users', description: 'Hotel staff and user management operations')]
class UserController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected UserService $userService
    ) {}

    #[OA\Get(
        path: '/api/v1/hotels/{hotel}/users',
        summary: "Display a listing of the hotel's staff",
        tags: ['Users'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
            new OA\Parameter(name: 'filter[email]', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Staff list retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(Request $request, Hotel $hotel): JsonResponse
    {
        $this->authorize('view', $hotel);

        $users = QueryBuilder::for(User::class)
            ->whereHas('hotels', function ($q) use ($hotel) {
                $q->where('hotels.id', $hotel->id);
            })
            ->allowedFilters(...[
                'email',
            ])
            ->with('roles')
            ->paginate($request->query('per_page', 15));

        return UserResource::collection($users)->additional([
            'success' => true,
            'message' => 'Staff users retrieved successfully.',
        ])->response();
    }

    #[OA\Post(
        path: '/api/v1/hotels/{hotel}/users',
        summary: 'Create and assign a new staff member',
        tags: ['Users'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['first_name', 'last_name', 'email', 'password', 'role'],
                properties: [
                    new OA\Property(property: 'first_name', type: 'string', example: 'Jane'),
                    new OA\Property(property: 'last_name', type: 'string', example: 'Smith'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'jane.smith@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'Secret123!'),
                    new OA\Property(property: 'phone', type: 'string', example: '+254700000000'),
                    new OA\Property(property: 'role', type: 'string', example: 'receptionist'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Staff user created and associated successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
    public function store(StoreUserRequest $request, Hotel $hotel): JsonResponse
    {
        $this->authorize('update', $hotel);

        $user = $this->userService->createStaff($hotel, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Staff user created and associated successfully.',
            'data' => new UserResource($user),
        ], 201);
    }

    #[OA\Get(
        path: '/api/v1/hotels/{hotel}/users/{user}',
        summary: 'Display the specified staff member details',
        tags: ['Users'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Staff user details retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Staff user not found'),
        ]
    )]
    public function show(Hotel $hotel, User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return response()->json([
            'success' => true,
            'message' => 'Staff user retrieved successfully.',
            'data' => new UserResource($user),
        ]);
    }

    #[OA\Put(
        path: '/api/v1/hotels/{hotel}/users/{user}',
        summary: 'Update the specified staff member details',
        tags: ['Users'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'first_name', type: 'string', example: 'Jane'),
                    new OA\Property(property: 'last_name', type: 'string', example: 'Smith'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'jane.smith@example.com'),
                    new OA\Property(property: 'phone', type: 'string', example: '+254700000000'),
                    new OA\Property(property: 'is_active', type: 'boolean', example: true),
                    new OA\Property(property: 'role', type: 'string', example: 'receptionist'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Staff user updated successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Staff user not found'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
    public function update(UpdateUserRequest $request, Hotel $hotel, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $updatedUser = $this->userService->updateStaff($user, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Staff user updated successfully.',
            'data' => new UserResource($updatedUser),
        ]);
    }

    #[OA\Delete(
        path: '/api/v1/hotels/{hotel}/users/{user}',
        summary: 'Remove the specified staff member from the hotel',
        tags: ['Users'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Staff user dissociated successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Staff user not found'),
        ]
    )]
    public function destroy(Hotel $hotel, User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $this->userService->deleteStaff($hotel, $user);

        return response()->json([
            'success' => true,
            'message' => 'Staff user dissociated/deleted successfully.',
        ]);
    }
}
