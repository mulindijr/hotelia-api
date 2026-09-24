<?php

namespace App\Http\Controllers\Api\V1\Bookings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Bookings\StoreBookingRequest;
use App\Http\Requests\Api\V1\Bookings\UpdateBookingRequest;
use App\Http\Resources\Api\V1\Bookings\BookingResource;
use App\Models\Booking;
use App\Models\Hotel;
use App\Services\Booking\BookingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

#[OA\Tag(name: 'Bookings', description: 'Reservation management, check-in, check-out, and cancellation endpoints')]
class BookingController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected BookingService $bookingService
    ) {}

    #[OA\Get(
        path: '/api/v1/hotels/{hotel}/bookings',
        summary: 'List all bookings for a hotel',
        tags: ['Bookings'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Bookings list retrieved'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(Request $request, Hotel $hotel): JsonResponse
    {
        $this->authorize('viewAny', [Booking::class, $hotel]);

        $bookings = QueryBuilder::for(Booking::class)
            ->where('hotel_id', $hotel->id)
            ->allowedFilters(...[
                'status',
                'guest_id',
                AllowedFilter::callback('check_in_date', function ($query, $value) 
            ->allowedIncludes(['guest', 'room', 'hotel', 'payments']){
                    $query->where('check_in_date', '>=', $value);
                }),
                AllowedFilter::callback('check_out_date', function ($query, $value) {
                    $query->where('check_out_date', '<=', $value);
                }),
            ])
            ->with(['guest', 'rooms.roomType', 'services'])
            ->paginate($request->query('per_page', 15));

        return BookingResource::collection($bookings)->additional([
            'success' => true,
            'message' => 'Bookings Retrieved Successfully.',
        ])->response();
    }

    #[OA\Post(
        path: '/api/v1/hotels/{hotel}/bookings',
        summary: 'Create a new booking reservation with overlap validation',
        tags: ['Bookings'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['guest_id', 'check_in_date', 'check_out_date', 'room_ids'],
                properties: [
                    new OA\Property(property: 'guest_id', type: 'integer', example: 1),
                    new OA\Property(property: 'check_in_date', type: 'string', format: 'date', example: '2026-08-01'),
                    new OA\Property(property: 'check_out_date', type: 'string', format: 'date', example: '2026-08-05'),
                    new OA\Property(property: 'room_ids', type: 'array', items: new OA\Items(type: 'integer'), example: [1, 2]),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Booking created successfully'),
            new OA\Response(response: 422, description: 'Validation failed or room overlap detected'),
        ]
    )]
    public function store(StoreBookingRequest $request, Hotel $hotel): JsonResponse
    {
        $this->authorize('create', [Booking::class, $hotel]);

        $booking = $this->bookingService->create($hotel, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Booking Created Successfully.',
            'data' => new BookingResource($booking),
        ], 201);
    }

    #[OA\Get(
        path: '/api/v1/hotels/{hotel}/bookings/{booking}',
        summary: 'Get booking details',
        tags: ['Bookings'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'booking', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Booking details retrieved'),
            new OA\Response(response: 404, description: 'Booking not found'),
        ]
    )]
    public function show(Hotel $hotel, Booking $booking): JsonResponse
    {
        $this->authorize('view', [$booking, $hotel]);

        return response()->json([
            'success' => true,
            'message' => 'Booking Retrieved Successfully.',
            'data' => new BookingResource($booking->load(['guest', 'rooms.roomType', 'services'])),
        ]);
    }

    #[OA\Put(
        path: '/api/v1/hotels/{hotel}/bookings/{booking}',
        summary: 'Update booking details',
        tags: ['Bookings'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'booking', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Booking updated successfully'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
    public function update(UpdateBookingRequest $request, Hotel $hotel, Booking $booking): JsonResponse
    {
        $this->authorize('update', [$booking, $hotel]);

        $updatedBooking = $this->bookingService->update($booking, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Booking Updated Successfully.',
            'data' => new BookingResource($updatedBooking),
        ]);
    }

    #[OA\Post(
        path: '/api/v1/hotels/{hotel}/bookings/{booking}/cancel',
        summary: 'Cancel a booking',
        tags: ['Bookings'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'booking', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Booking cancelled successfully'),
        ]
    )]
    public function cancel(Hotel $hotel, Booking $booking): JsonResponse
    {
        $this->authorize('cancel', [$booking, $hotel]);

        $cancelledBooking = $this->bookingService->cancel($booking);

        return response()->json([
            'success' => true,
            'message' => 'Booking Cancelled Successfully.',
            'data' => new BookingResource($cancelledBooking->load(['guest', 'rooms.roomType', 'services'])),
        ]);
    }

    #[OA\Post(
        path: '/api/v1/hotels/{hotel}/bookings/{booking}/check-in',
        summary: 'Check in guest and update rooms to occupied',
        tags: ['Bookings'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'booking', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Booking checked in successfully'),
        ]
    )]
    public function checkIn(Hotel $hotel, Booking $booking): JsonResponse
    {
        $this->authorize('checkIn', [$booking, $hotel]);

        $checkedInBooking = $this->bookingService->checkIn($booking);

        return response()->json([
            'success' => true,
            'message' => 'Booking Checked In Successfully.',
            'data' => new BookingResource($checkedInBooking->load(['guest', 'rooms.roomType', 'services'])),
        ]);
    }

    #[OA\Post(
        path: '/api/v1/hotels/{hotel}/bookings/{booking}/check-out',
        summary: 'Check out guest, update rooms to cleaning, and auto-create housekeeping tasks',
        tags: ['Bookings'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'booking', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Booking checked out successfully'),
        ]
    )]
    public function checkOut(Hotel $hotel, Booking $booking): JsonResponse
    {
        $this->authorize('checkOut', [$booking, $hotel]);

        $checkedOutBooking = $this->bookingService->checkOut($booking);

        return response()->json([
            'success' => true,
            'message' => 'Booking Checked Out Successfully.',
            'data' => new BookingResource($checkedOutBooking->load(['guest', 'rooms.roomType', 'services'])),
        ]);
    }

    #[OA\Post(
        path: '/api/v1/hotels/{hotel}/bookings/{booking}/no-show',
        summary: 'Mark booking as no-show and release rooms',
        tags: ['Bookings'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'booking', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Booking marked as no-show successfully'),
        ]
    )]
    public function noShow(Hotel $hotel, Booking $booking): JsonResponse
    {
        $this->authorize('noShow', [$booking, $hotel]);

        $noShowBooking = $this->bookingService->noShow($booking);

        return response()->json([
            'success' => true,
            'message' => 'Booking Marked as No-Show Successfully.',
            'data' => new BookingResource($noShowBooking->load(['guest', 'rooms.roomType', 'services'])),
        ]);
    }
}
