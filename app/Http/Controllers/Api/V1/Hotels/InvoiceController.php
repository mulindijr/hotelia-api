<?php

namespace App\Http\Controllers\Api\V1\Hotels;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Billing\InvoiceResource;
use App\Models\Booking;
use App\Models\Hotel;
use App\Services\Billing\BillingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected BillingService $billingService
    ) {}

    /**
     * Display the booking's invoice (generate if not existing).
     */
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

    /**
     * Manually trigger invoice regeneration (e.g. after booking modifications).
     */
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
