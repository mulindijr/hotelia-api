<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;
use Spatie\Activitylog\Models\Activity;
use Spatie\QueryBuilder\QueryBuilder;

class AuditController extends Controller
{
    #[OA\Get(
        path: '/api/v1/admin/audit-logs',
        summary: 'Display a listing of the system audit logs (requires view activity logs permission)',
        tags: ['Security'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'filter[log_name]', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[event]', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[causer_id]', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[description]', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Audit logs retrieved successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index()
    {
        $logs = QueryBuilder::for(Activity::class)
            ->whereHas('causer', function ($query) {
                // Ensure we only show activity logs performed by users in the current tenant
                $query->where('tenant_id', tenant('id'));
            })
            ->with('causer', 'subject')
            ->allowedFilters(
                'log_name',
                'event',
                'causer_id',
                'description'
            )
            ->allowedSorts(
                'created_at',
                'id'
            )
            ->latest()
            ->paginate(request()->query('per_page', 15));

        return response()->json($logs);
    }
}
