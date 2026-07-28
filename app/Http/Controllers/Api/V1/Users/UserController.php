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
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Display a listing of the hotel's staff.
     */
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

    /**
     * Create and assign a new staff member.
     */
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

    /**
     * Display the specified staff member.
     */
    public function show(Hotel $hotel, User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return response()->json([
            'success' => true,
            'message' => 'Staff user retrieved successfully.',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Update the specified staff member.
     */
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

    /**
     * Remove the specified staff member from the hotel.
     */
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
