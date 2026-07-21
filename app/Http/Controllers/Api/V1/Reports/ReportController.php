<?php

namespace App\Http\Controllers\Api\V1\Reports;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Services\Report\ReportService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Fetch daily operational statistics dashboard.
     */
    public function dashboard(Hotel $hotel): JsonResponse
    {
        $this->authorize('view', $hotel);

        $stats = $this->reportService->getDashboardStats($hotel);

        return response()->json([
            'success' => true,
            'message' => 'Dashboard Statistics Retrieved Successfully.',
            'data' => $stats,
        ]);
    }

    /**
     * Fetch range-based financial revenue statistics.
     */
    public function revenue(Request $request, Hotel $hotel): JsonResponse
    {
        $this->authorize('view', $hotel);

        $request->validate([
            'start_date' => ['sometimes', 'date', 'date_format:Y-m-d'],
            'end_date' => ['sometimes', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ]);

        $stats = $this->reportService->getRevenueStats(
            $hotel,
            $request->input('start_date'),
            $request->input('end_date')
        );

        return response()->json([
            'success' => true,
            'message' => 'Revenue Statistics Retrieved Successfully.',
            'data' => $stats,
        ]);
    }
}
