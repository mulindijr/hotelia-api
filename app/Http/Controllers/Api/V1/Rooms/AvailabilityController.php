<?php

namespace App\Http\Controllers\Api\V1\Rooms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Rooms\GetAvailabilityRequest;
use App\Models\Hotel;
use App\Services\Room\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class AvailabilityController extends Controller
{
    public function __construct(
        protected AvailabilityService $availabilityService
    ) {}

    #[OA\Get(
        path: '/api/v1/hotels/{hotel_id}/availability',
        summary: 'Get room availability matrix and calculated prices for a date range',
        tags: ['Availability'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'hotel_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'start_date', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'end_date', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'room_type_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'rate_plan_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Availability matrix retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(GetAvailabilityRequest $request, Hotel $hotel): JsonResponse
    {
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))
            : Carbon::today();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))
            : $startDate->copy()->addDays(30);

        $roomTypeId = $request->input('room_type_id') ? (int) $request->input('room_type_id') : null;
        $ratePlanId = $request->input('rate_plan_id') ? (int) $request->input('rate_plan_id') : null;

        $matrix = $this->availabilityService->getAvailabilityMatrix(
            $hotel,
            $startDate,
            $endDate,
            $roomTypeId,
            $ratePlanId
        );

        return response()->json([
            'success' => true,
            'message' => 'Room availability matrix retrieved successfully.',
            'data' => $matrix,
        ]);
    }
}
