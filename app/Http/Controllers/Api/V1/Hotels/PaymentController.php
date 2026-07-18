<?php

namespace App\Http\Controllers\Api\V1\Hotels;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Billing\StorePaymentRequest;
use App\Http\Requests\Api\V1\Billing\UpdatePaymentStatusRequest;
use App\Http\Resources\Api\V1\Billing\PaymentResource;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Payment;
use App\Services\Billing\BillingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected BillingService $billingService
    ) {}

    /**
     * Display a listing of payments logged for a booking.
     */
    public function index(Hotel $hotel, Booking $booking): JsonResponse
    {
        if ($booking->hotel_id !== $hotel->id) {
            abort(404, 'Booking not found in hotel scope.');
        }

        $this->authorize('view', $booking);

        $payments = $booking->payments()->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Payments Retrieved Successfully.',
            'data' => PaymentResource::collection($payments),
        ]);
    }

    /**
     * Log a new payment for the booking.
     */
    public function store(StorePaymentRequest $request, Hotel $hotel, Booking $booking): JsonResponse
    {
        if ($booking->hotel_id !== $hotel->id) {
            abort(404, 'Booking not found in hotel scope.');
        }

        $this->authorize('update', $booking);

        $payment = $this->billingService->logPayment($booking, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Payment Logged Successfully.',
            'data' => new PaymentResource($payment),
        ], 201);
    }

    /**
     * Update the status of a specific payment log.
     */
    public function updateStatus(UpdatePaymentStatusRequest $request, Hotel $hotel, Booking $booking, Payment $payment): JsonResponse
    {
        if ($booking->hotel_id !== $hotel->id || $payment->booking_id !== $booking->id) {
            abort(404, 'Payment log not found in booking/hotel scope.');
        }

        $this->authorize('update', $booking);

        $updatedPayment = $this->billingService->updatePaymentStatus($payment, $request->status);

        return response()->json([
            'success' => true,
            'message' => 'Payment Status Updated Successfully.',
            'data' => new PaymentResource($updatedPayment),
        ]);
    }
}
