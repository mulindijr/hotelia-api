<?php

namespace App\Http\Controllers\Api\V1\Pricing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Pricing\StorePricingRuleRequest;
use App\Http\Requests\Api\V1\Pricing\UpdatePricingRuleRequest;
use App\Http\Resources\Api\V1\Pricing\PricingRuleResource;
use App\Models\Hotel;
use App\Models\PricingRule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class PricingRuleController extends Controller
{
    use AuthorizesRequests;

    #[OA\Get(
        path: '/api/v1/hotels/{hotel_id}/pricing-rules',
        summary: 'List all dynamic pricing rules for a hotel',
        tags: ['Pricing Rules'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Pricing rules retrieved successfully'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(Hotel $hotel): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [PricingRule::class, $hotel]);

        $rules = PricingRule::where('hotel_id', $hotel->id)
            ->orderBy('priority', 'desc')
            ->latest()
            ->get();

        return PricingRuleResource::collection($rules);
    }

    #[OA\Post(
        path: '/api/v1/hotels/{hotel_id}/pricing-rules',
        summary: 'Create a new dynamic pricing rule',
        tags: ['Pricing Rules'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 201, description: 'Pricing rule created successfully'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StorePricingRuleRequest $request, Hotel $hotel): JsonResponse
    {
        $data = $request->validated();
        $data['hotel_id'] = $hotel->id;

        $rule = PricingRule::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Pricing rule created successfully.',
            'data' => new PricingRuleResource($rule),
        ], 201);
    }

    #[OA\Get(
        path: '/api/v1/hotels/{hotel_id}/pricing-rules/{pricing_rule_id}',
        summary: 'Get pricing rule details',
        tags: ['Pricing Rules'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Pricing rule details'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Hotel $hotel, PricingRule $pricingRule): JsonResponse
    {
        $this->authorize('view', [$pricingRule, $hotel]);

        return response()->json([
            'success' => true,
            'message' => 'Pricing rule retrieved successfully.',
            'data' => new PricingRuleResource($pricingRule),
        ]);
    }

    #[OA\Put(
        path: '/api/v1/hotels/{hotel_id}/pricing-rules/{pricing_rule_id}',
        summary: 'Update pricing rule',
        tags: ['Pricing Rules'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Pricing rule updated successfully'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdatePricingRuleRequest $request, Hotel $hotel, PricingRule $pricingRule): JsonResponse
    {
        $data = $request->validated();

        $pricingRule->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Pricing rule updated successfully.',
            'data' => new PricingRuleResource($pricingRule),
        ]);
    }

    #[OA\Delete(
        path: '/api/v1/hotels/{hotel_id}/pricing-rules/{pricing_rule_id}',
        summary: 'Delete pricing rule',
        tags: ['Pricing Rules'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Pricing rule deleted successfully'),
        ]
    )]
    public function destroy(Hotel $hotel, PricingRule $pricingRule): JsonResponse
    {
        $this->authorize('delete', [$pricingRule, $hotel]);

        $pricingRule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pricing rule deleted successfully.',
        ]);
    }
}
