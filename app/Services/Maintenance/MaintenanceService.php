<?php

namespace App\Services\Maintenance;

use App\Events\Maintenance\MaintenanceRequestCreated;
use App\Events\Maintenance\MaintenanceRequestUpdated;
use App\Events\Maintenance\MaintenanceRequestDeleted;
use App\Models\Hotel;
use App\Models\MaintenanceRequest;

class MaintenanceService
{
    /**
     * Create a new maintenance request.
     */
    public function create(Hotel $hotel, array $data, int $userId): MaintenanceRequest
    {
        $data['reported_by'] = $data['reported_by'] ?? $userId;
        $data['status'] = $data['status'] ?? 'open';
        $data['priority'] = $data['priority'] ?? 'medium';

        $request = MaintenanceRequest::create($data);

        event(new MaintenanceRequestCreated($request));

        return $request->load('room');
    }

    /**
     * Update a maintenance request.
     */
    public function update(MaintenanceRequest $request, array $data): MaintenanceRequest
    {
        $request->update($data);

        event(new MaintenanceRequestUpdated($request));

        return $request->fresh()->load('room');
    }

    /**
     * Delete a maintenance request.
     */
    public function delete(MaintenanceRequest $request): bool
    {
        event(new MaintenanceRequestDeleted($request));

        return $request->delete();
    }
}
