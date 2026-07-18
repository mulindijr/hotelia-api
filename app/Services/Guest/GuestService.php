<?php

namespace App\Services\Guest;

use App\Events\Guests\GuestCreated;
use App\Events\Guests\GuestUpdated;
use App\Events\Guests\GuestDeleted;
use App\Models\Guest;

class GuestService
{
    /**
     * Create a new guest.
     */
    public function create(array $data): Guest
    {
        $guest = Guest::create($data);

        event(new GuestCreated($guest));

        return $guest;
    }

    /**
     * Update a guest.
     */
    public function update(Guest $guest, array $data): Guest
    {
        $guest->update($data);

        event(new GuestUpdated($guest));

        return $guest->fresh();
    }

    /**
     * Delete a guest.
     */
    public function delete(Guest $guest): bool
    {
        event(new GuestDeleted($guest));

        return $guest->delete();
    }
}
