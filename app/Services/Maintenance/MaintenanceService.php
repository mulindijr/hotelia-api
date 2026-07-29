<?php

namespace App\Services\Maintenance;

use App\Events\Maintenance\MaintenanceRequestCreated;
use App\Events\Maintenance\MaintenanceRequestDeleted;
use App\Events\Maintenance\MaintenanceRequestUpdated;
use App\Models\Hotel;
use App\Models\MaintenanceRequest;
use Illuminate\Support\Facades\DB;

class MaintenanceService
{
    /**
     * Create a new maintenance request inside a DB transaction.
     */
    public function create(Hotel $hotel, array $data, int $userId): MaintenanceRequest
    {
        return DB::transaction(function () use ($data, $userId) {
            $data['reported_by'] = $data['reported_by'] ?? $userId;
            $data['status'] = $data['status'] ?? 'open';
            $data['priority'] = $data['priority'] ?? 'medium';

            $request = MaintenanceRequest::create($data);

            event(new MaintenanceRequestCreated($request));

            return $request->load('room');
        });
    }

    /**
     * Update a maintenance request inside a DB transaction.
     */
    public function update(MaintenanceRequest $request, array $data): MaintenanceRequest
    {
        return DB::transaction(function () use ($request, $data) {
            $request->update($data);

            event(new MaintenanceRequestUpdated($request));

            return $request->fresh()->load('room');
        });
    }

    /**
     * Delete a maintenance request inside a DB transaction.
     */
    public function delete(MaintenanceRequest $request): bool
    {
        return DB::transaction(function () use ($request) {
            event(new MaintenanceRequestDeleted($request));

            return $request->delete();
        });
    }
}
