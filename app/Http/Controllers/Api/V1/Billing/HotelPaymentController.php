<?php

namespace App\Http\Controllers\Api\V1\Billing;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Billing\PaymentResource;
use App\Models\Hotel;
use App\Models\Payment;
use App\Services\Pdf\PdfService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class HotelPaymentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected PdfService $pdfService
    ) {}

    public function index(Request $request, Hotel $hotel): JsonResponse
    {
        // First authorize that the user can view this hotel
        $this->authorize('view', $hotel);

        $payments = QueryBuilder::for(Payment::class)
            ->whereHas('booking', function ($query) use ($hotel) {
                $query->where('hotel_id', $hotel->id);
            })
            ->allowedFilters([
                'status',
                'payment_method',
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->where('transaction_reference', 'like', "%{$value}%")
                            ->orWhereHas('booking', function ($bQuery) use ($value) {
                                $bQuery->where('booking_reference', 'like', "%{$value}%")
                                    ->orWhereHas('guest', function ($gQuery) use ($value) {
                                        $gQuery->where('first_name', 'like', "%{$value}%")
                                            ->orWhere('last_name', 'like', "%{$value}%")
                                            ->orWhere('email', 'like', "%{$value}%")
                                            ->orWhere('phone', 'like', "%{$value}%");
                                    });
                            });
                    });
                }),
            ])
            ->allowedIncludes(['booking', 'booking.guest', 'booking.rooms'])
            ->with(['booking.guest'])
            ->latest()
            ->paginate($request->query('per_page', 15));

        return PaymentResource::collection($payments)->additional([
            'success' => true,
            'message' => 'Hotel Payments Retrieved Successfully.',
        ])->response();
    }

    public function downloadReceiptPdf(Hotel $hotel, Payment $payment): Response
    {
        $this->authorize('view', $hotel);

        // Verify payment belongs to this hotel
        if ((int) $payment->booking?->hotel_id !== (int) $hotel->id) {
            abort(404, 'Payment does not belong to the specified hotel.');
        }

        return $this->pdfService->renderPaymentReceiptPdf($payment);
    }
}
