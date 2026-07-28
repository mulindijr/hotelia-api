<?php

namespace App\Http\Controllers\Api\V1\Billing;

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
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class PaymentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected BillingService $billingService
    ) {}

    /**
     * Display a listing of payments logged for a booking.
     */
    public function index(Request $request, Hotel $hotel, Booking $booking): JsonResponse
    {
        $this->authorize('view', [$booking, $hotel]);

        $payments = QueryBuilder::for(Payment::class)
            ->where('booking_id', $booking->id)
            ->allowedFilters([
                'status',
                'payment_method',
            ])
            ->latest()
            ->paginate($request->query('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Payments Retrieved Successfully.',
            'data' => PaymentResource::collection($payments->items()),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
        ]);
    }

    /**
     * Log a new payment for the booking.
     */
    public function store(StorePaymentRequest $request, Hotel $hotel, Booking $booking): JsonResponse
    {
        $this->authorize('update', [$booking, $hotel]);

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
        $this->authorize('update', [$booking, $hotel]);

        $updatedPayment = $this->billingService->updatePaymentStatus($payment, $request->status);

        return response()->json([
            'success' => true,
            'message' => 'Payment Status Updated Successfully.',
            'data' => new PaymentResource($updatedPayment),
        ]);
    }
}
