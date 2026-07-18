<?php

namespace App\Http\Controllers\Api\V1\Hotels;

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

class BookingController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected BookingService $bookingService
    ) {}

    /**
     * Display a listing of the bookings for a hotel.
     */
    public function index(Request $request, Hotel $hotel): JsonResponse
    {
        if (!$request->user()->belongsToHotel($hotel->id) && !$request->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized hotel scope.');
        }

        $bookings = $hotel->bookings()->with(['guest', 'rooms.roomType', 'services'])->get();

        return response()->json([
            'success' => true,
            'message' => 'Bookings Retrieved Successfully.',
            'data' => BookingResource::collection($bookings),
        ]);
    }

    /**
     * Store a newly created booking.
     */
    public function store(StoreBookingRequest $request, Hotel $hotel): JsonResponse
    {
        if (!$request->user()->belongsToHotel($hotel->id) && !$request->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized hotel scope.');
        }

        $booking = $this->bookingService->create($hotel, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Booking Created Successfully.',
            'data' => new BookingResource($booking),
        ], 201);
    }

    /**
     * Display the specified booking.
     */
    public function show(Hotel $hotel, Booking $booking): JsonResponse
    {
        $this->authorize('view', $booking);

        return response()->json([
            'success' => true,
            'message' => 'Booking Retrieved Successfully.',
            'data' => new BookingResource($booking->load(['guest', 'rooms.roomType', 'services'])),
        ]);
    }

    /**
     * Update the specified booking.
     */
    public function update(UpdateBookingRequest $request, Hotel $hotel, Booking $booking): JsonResponse
    {
        $this->authorize('update', $booking);

        $updatedBooking = $this->bookingService->update($booking, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Booking Updated Successfully.',
            'data' => new BookingResource($updatedBooking),
        ]);
    }

    /**
     * Cancel the booking.
     */
    public function cancel(Hotel $hotel, Booking $booking): JsonResponse
    {
        $this->authorize('cancel', $booking);

        $cancelledBooking = $this->bookingService->cancel($booking);

        return response()->json([
            'success' => true,
            'message' => 'Booking Cancelled Successfully.',
            'data' => new BookingResource($cancelledBooking->load(['guest', 'rooms.roomType', 'services'])),
        ]);
    }

    /**
     * Check in guest.
     */
    public function checkIn(Hotel $hotel, Booking $booking): JsonResponse
    {
        $this->authorize('checkIn', $booking);

        $checkedInBooking = $this->bookingService->checkIn($booking);

        return response()->json([
            'success' => true,
            'message' => 'Booking Checked In Successfully.',
            'data' => new BookingResource($checkedInBooking->load(['guest', 'rooms.roomType', 'services'])),
        ]);
    }

    /**
     * Check out guest.
     */
    public function checkOut(Hotel $hotel, Booking $booking): JsonResponse
    {
        $this->authorize('checkOut', $booking);

        $checkedOutBooking = $this->bookingService->checkOut($booking);

        return response()->json([
            'success' => true,
            'message' => 'Booking Checked Out Successfully.',
            'data' => new BookingResource($checkedOutBooking->load(['guest', 'rooms.roomType', 'services'])),
        ]);
    }
}
