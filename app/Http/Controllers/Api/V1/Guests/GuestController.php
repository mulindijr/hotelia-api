<?php

namespace App\Http\Controllers\Api\V1\Guests;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Guests\StoreGuestRequest;
use App\Http\Requests\Api\V1\Guests\UpdateGuestRequest;
use App\Http\Resources\Api\V1\Guests\GuestResource;
use App\Models\Guest;
use App\Services\Guest\GuestService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class GuestController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected GuestService $guestService
    ) {}

    /**
     * Display a listing of guests.
     */
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

        return response()->json([
            'success' => true,
            'message' => 'Guests Retrieved Successfully.',
            'data' => GuestResource::collection($guests->items()),
            'meta' => [
                'current_page' => $guests->currentPage(),
                'last_page' => $guests->lastPage(),
                'per_page' => $guests->perPage(),
                'total' => $guests->total(),
            ],
        ]);
    }

    /**
     * Store a newly created guest.
     */
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

    /**
     * Display the specified guest details.
     */
    public function show(Guest $guest): JsonResponse
    {
        $this->authorize('view', $guest);

        return response()->json([
            'success' => true,
            'message' => 'Guest Retrieved Successfully.',
            'data' => new GuestResource($guest),
        ]);
    }

    /**
     * Update the specified guest.
     */
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

    /**
     * Remove the specified guest.
     */
    public function destroy(Guest $guest): JsonResponse
    {
        $this->authorize('delete', $guest);

        $this->guestService->delete($guest);

        return response()->json([
            'success' => true,
            'message' => 'Guest Deleted Successfully.',
        ]);
    }
}
