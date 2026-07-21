<?php

namespace App\Http\Controllers\Api\V1\Reports;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Services\Report\ReportService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use OpenApi\Attributes as OA;

#[OA\Tag(name: "Reports", description: "Operational and financial reporting dashboard endpoints")]
class ReportController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ReportService $reportService
    ) {}

    #[OA\Get(
        path: "/api/v1/hotels/{hotel}/reports/dashboard",
        summary: "Retrieve real-time operational dashboard stats for a hotel",
        tags: ["Reports"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "hotel", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Dashboard statistics retrieved successfully"),
            new OA\Response(response: 403, description: "Forbidden")
        ]
    )]
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

    #[OA\Get(
        path: "/api/v1/hotels/{hotel}/reports/revenue",
        summary: "Retrieve range-based financial revenue KPIs (ADR, RevPAR, payments)",
        tags: ["Reports"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "hotel", in: "path", required: true, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "start_date", in: "query", required: false, schema: new OA\Schema(type: "string", format: "date", example: "2026-07-01")),
            new OA\Parameter(name: "end_date", in: "query", required: false, schema: new OA\Schema(type: "string", format: "date", example: "2026-07-31"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Revenue statistics retrieved successfully"),
            new OA\Response(response: 422, description: "Validation failed")
        ]
    )]
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
