<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use Spatie\QueryBuilder\QueryBuilder;

class AuditController extends Controller
{
    public function index()
    {
        $logs = QueryBuilder::for(Activity::class)
            ->with('causer')
            ->allowedFilters([
                'log_name',
                'event',
                'causer_id'
            ])
            ->allowedSorts([
                'created_at',
                'id'
            ])
            ->latest()
            ->paginate(10);

        return response()->json($logs);
    }
}
