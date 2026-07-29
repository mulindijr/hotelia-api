<?php

namespace App\Services\Hotel;

use App\Events\Services\ServiceCreated;
use App\Events\Services\ServiceDeleted;
use App\Events\Services\ServiceUpdated;
use App\Models\Hotel;
use App\Models\Service;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AncillaryService
{
    /**
     * Retrieve services for a hotel with caching.
     */
    public function getServices(Hotel $hotel)
    {
        return Cache::remember("hotel:{$hotel->id}:services", 3600, function () use ($hotel) {
            return $hotel->services()->get();
        });
    }

    /**
     * Create a new ancillary service for a hotel.
     */
    public function create(Hotel $hotel, array $data): Service
    {
        return DB::transaction(function () use ($hotel, $data) {
            $data['is_active'] = $data['is_active'] ?? true;

            /** @var Service $service */
            $service = $hotel->services()->create($data);

            Cache::forget("hotel:{$hotel->id}:services");

            event(new ServiceCreated($service));

            return $service;
        });
    }

    /**
     * Update an ancillary service.
     */
    public function update(Service $service, array $data): Service
    {
        return DB::transaction(function () use ($service, $data) {
            $service->update($data);

            Cache::forget("hotel:{$service->hotel_id}:services");

            event(new ServiceUpdated($service));

            return $service->fresh();
        });
    }

    /**
     * Delete an ancillary service.
     */
    public function delete(Service $service): bool
    {
        return DB::transaction(function () use ($service) {
            Cache::forget("hotel:{$service->hotel_id}:services");

            event(new ServiceDeleted($service));

            return $service->delete();
        });
    }
}
