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
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\QueryBuilder;

#[OA\Tag(name: 'Billing')]
class PaymentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected BillingService $billingService
    ) {}

    #[OA\Get(
        path: '/api/v1/hotels/{hotel}/bookings/{booking}/payments',
        summary: 'Display a listing of payments logged for a booking',
        tags: ['Billing'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'booking', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
            new OA\Parameter(name: 'filter[status]', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[payment_method]', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Payments list retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Booking or Hotel not found'),
        ]
    )]
    public function index(Request $request, Hotel $hotel, Booking $booking): JsonResponse
    {
        $this->authorize('view', [$booking, $hotel]);

        $payments = QueryBuilder::for(Payment::class)
            ->where('booking_id', $booking->id)
            ->allowedFilters(...[
                'status',
                'payment_method',
            ])
            ->latest()
            ->paginate($request->query('per_page', 15));

        return PaymentResource::collection($payments)->additional([
            'success' => true,
            'message' => 'Payments Retrieved Successfully.',
        ])->response();
    }

    #[OA\Post(
        path: '/api/v1/hotels/{hotel}/bookings/{booking}/payments',
        summary: 'Log a new payment for the booking',
        tags: ['Billing'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'booking', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['amount', 'payment_method'],
                properties: [
                    new OA\Property(property: 'amount', type: 'number', format: 'float', example: 5000.00),
                    new OA\Property(property: 'payment_method', type: 'string', example: 'm-pesa'),
                    new OA\Property(property: 'transaction_reference', type: 'string', example: 'TXN12345678'),
                    new OA\Property(property: 'status', type: 'string', example: 'completed'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Payment logged successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
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

    #[OA\Put(
        path: '/api/v1/hotels/{hotel}/bookings/{booking}/payments/{payment}/status',
        summary: 'Update the status of a specific payment log',
        tags: ['Billing'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'booking', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'payment', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(property: 'status', type: 'string', example: 'completed'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Payment status updated successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
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
