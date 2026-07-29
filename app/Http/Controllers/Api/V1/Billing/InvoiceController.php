<?php

namespace App\Http\Controllers\Api\V1\Billing;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Billing\InvoiceResource;
use App\Models\Booking;
use App\Models\Hotel;
use App\Services\Billing\BillingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Billing', description: 'Hotel billing and invoice management operations')]
class InvoiceController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected BillingService $billingService
    ) {}

    #[OA\Get(
        path: '/api/v1/hotels/{hotel}/bookings/{booking}/invoice',
        summary: "Display the booking's invoice (generate if not existing)",
        tags: ['Billing'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'booking', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Invoice details retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Booking or Hotel not found'),
        ]
    )]
    public function show(Hotel $hotel, Booking $booking): JsonResponse
    {
        $this->authorize('view', [$booking, $hotel]);

        $invoice = $this->billingService->getOrGenerateInvoice($booking);

        return response()->json([
            'success' => true,
            'message' => 'Invoice Retrieved Successfully.',
            'data' => new InvoiceResource($invoice),
        ]);
    }

    #[OA\Post(
        path: '/api/v1/hotels/{hotel}/bookings/{booking}/invoice/regenerate',
        summary: 'Manually trigger invoice regeneration (e.g. after booking modifications)',
        tags: ['Billing'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'booking', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Invoice regenerated successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Booking or Hotel not found'),
        ]
    )]
    public function regenerate(Hotel $hotel, Booking $booking): JsonResponse
    {
        $this->authorize('update', [$booking, $hotel]);

        $invoice = $this->billingService->regenerateInvoice($booking);

        return response()->json([
            'success' => true,
            'message' => 'Invoice Regenerated Successfully.',
            'data' => new InvoiceResource($invoice),
        ]);
    }
}
