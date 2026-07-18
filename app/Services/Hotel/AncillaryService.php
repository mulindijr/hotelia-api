<?php

namespace App\Services\Hotel;

use App\Events\Services\ServiceCreated;
use App\Events\Services\ServiceUpdated;
use App\Events\Services\ServiceDeleted;
use App\Models\Hotel;
use App\Models\Service;

class AncillaryService
{
    /**
     * Create a new ancillary service for a hotel.
     */
    public function create(Hotel $hotel, array $data): Service
    {
        $data['is_active'] = $data['is_active'] ?? true;

        /** @var Service $service */
        $service = $hotel->services()->create($data);

        event(new ServiceCreated($service));

        return $service;
    }

    /**
     * Update an ancillary service.
     */
    public function update(Service $service, array $data): Service
    {
        $service->update($data);

        event(new ServiceUpdated($service));

        return $service->fresh();
    }

    /**
     * Delete an ancillary service.
     */
    public function delete(Service $service): bool
    {
        event(new ServiceDeleted($service));

        return $service->delete();
    }
}
