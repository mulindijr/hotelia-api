<?php

namespace App\Http\Controllers\Api\V1\Billing;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Billing\PaymentResource;
use App\Models\Hotel;
use App\Models\Payment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class HotelPaymentController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, Hotel $hotel): JsonResponse
    {
        // First authorize that the user can view this hotel
        $this->authorize('view', $hotel);

        $payments = QueryBuilder::for(Payment::class)
            ->whereHas('booking', function ($query) use ($hotel) {
                $query->where('hotel_id', $hotel->id);
            })
            ->allowedFilters('status', 'payment_method')
            ->allowedIncludes('booking')
            ->latest()
            ->paginate($request->query('per_page', 15));

        return PaymentResource::collection($payments)->additional([
            'success' => true,
            'message' => 'Hotel Payments Retrieved Successfully.',
        ])->response();
    }
}
