<?php

namespace App\Http\Controllers\Api\V1\Pricing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Pricing\StoreRatePlanRequest;
use App\Http\Requests\Api\V1\Pricing\UpdateRatePlanRequest;
use App\Http\Resources\Api\V1\Pricing\RatePlanResource;
use App\Models\Hotel;
use App\Models\RatePlan;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class RatePlanController extends Controller
{
    use AuthorizesRequests;

    #[OA\Get(
        path: '/api/v1/hotels/{hotel_id}/rate-plans',
        summary: 'List all rate plans for a hotel',
        tags: ['Rate Plans'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Rate plans retrieved successfully'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(Hotel $hotel): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [RatePlan::class, $hotel]);

        $ratePlans = RatePlan::where('hotel_id', $hotel->id)->latest()->get();

        return RatePlanResource::collection($ratePlans);
    }

    #[OA\Post(
        path: '/api/v1/hotels/{hotel_id}/rate-plans',
        summary: 'Create a new rate plan for a hotel',
        tags: ['Rate Plans'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 201, description: 'Rate plan created successfully'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreRatePlanRequest $request, Hotel $hotel): JsonResponse
    {
        $data = $request->validated();
        $data['hotel_id'] = $hotel->id;

        if (! empty($data['is_default'])) {
            RatePlan::where('hotel_id', $hotel->id)->update(['is_default' => false]);
        }

        $ratePlan = RatePlan::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Rate plan created successfully.',
            'data' => new RatePlanResource($ratePlan),
        ], 201);
    }

    #[OA\Get(
        path: '/api/v1/hotels/{hotel_id}/rate-plans/{rate_plan_id}',
        summary: 'Get rate plan details',
        tags: ['Rate Plans'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Rate plan details'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Hotel $hotel, RatePlan $ratePlan): JsonResponse
    {
        $this->authorize('view', [$ratePlan, $hotel]);

        return response()->json([
            'success' => true,
            'message' => 'Rate plan retrieved successfully.',
            'data' => new RatePlanResource($ratePlan),
        ]);
    }

    #[OA\Put(
        path: '/api/v1/hotels/{hotel_id}/rate-plans/{rate_plan_id}',
        summary: 'Update rate plan',
        tags: ['Rate Plans'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Rate plan updated successfully'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateRatePlanRequest $request, Hotel $hotel, RatePlan $ratePlan): JsonResponse
    {
        $data = $request->validated();

        if (! empty($data['is_default'])) {
            RatePlan::where('hotel_id', $hotel->id)
                ->where('id', '!=', $ratePlan->id)
                ->update(['is_default' => false]);
        }

        $ratePlan->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Rate plan updated successfully.',
            'data' => new RatePlanResource($ratePlan),
        ]);
    }

    #[OA\Delete(
        path: '/api/v1/hotels/{hotel_id}/rate-plans/{rate_plan_id}',
        summary: 'Delete rate plan',
        tags: ['Rate Plans'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Rate plan deleted successfully'),
        ]
    )]
    public function destroy(Hotel $hotel, RatePlan $ratePlan): JsonResponse
    {
        $this->authorize('delete', [$ratePlan, $hotel]);

        $ratePlan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rate plan deleted successfully.',
        ]);
    }
}
