<?php

namespace App\Http\Controllers\Api\V1\Hotels;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\Api\V1\Hotels\StoreHotelRequest;
use App\Http\Requests\Api\V1\Hotels\UpdateHotelRequest;
use App\Http\Resources\Api\V1\Hotels\HotelResource;
use Spatie\QueryBuilder\QueryBuilder;
use App\Models\Hotel;
use App\Services\Hotel\HotelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HotelController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected HotelService $hotelService
    ) {}

    /**
     * Display a listing of hotels.
     */
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

    /**
     * Store a newly created hotel.
     */
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

    /**
     * Display the specified hotel.
     */
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

    /**
     * Update the specified hotel.
     */
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

    /**
     * Remove the specified hotel.
     */
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
