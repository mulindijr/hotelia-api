<?php

namespace App\Services\Hotel;

use App\Models\Hotel;
use Illuminate\Support\Facades\Storage;

class HotelService
{
  // Create a new hotel
  public function create(array $data): Hotel
  {
    // Store logo if it exists
    if (isset($data['logo'])) {
      $data['logo'] = $data['logo']
        ->store('hotels/logos', 'public');
    }

    return Hotel::create($data);
  }

  // Update an existing hotel
  public function update(Hotel $hotel, array $data): Hotel
  {
    if (isset($data['logo'])) {

      // Delete the old logo if it exists
      if ($hotel->logo) {
        Storage::disk('public')->delete($hotel->logo);
      }

      // Store the new logo
      $data['logo'] = $data['logo']
        ->store('hotels/logos', 'public');
    }

    $hotel->update($data);

    return $hotel->refresh();
  }

  // Delete a hotel
  public function delete(Hotel $hotel): void
  {
    $hotel->delete();
  }
}
