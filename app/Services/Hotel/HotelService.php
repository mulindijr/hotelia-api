<?php

namespace App\Services\Hotel;

use App\Events\Hotels\HotelCreated;
use App\Events\Hotels\HotelDeleted;
use App\Events\Hotels\HotelUpdated;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class HotelService
{
  // Create hotel
  public function create(array $data, User $createdBy): Hotel
  {
    return DB::transaction(function () use ($data, $createdBy) {

      if (isset($data['logo']) && $data['logo'] instanceof UploadedFile) {
        $data['logo'] = $this->uploadLogo($data['logo']);
      }

      $hotel = Hotel::create($data);

      $this->createDefaultSettings($hotel);

      $hotel->users()->attach($createdBy->id);

      event(new HotelCreated($hotel));

      return $hotel->fresh(['settings']);
    });
  }

  // Update an existing hotel
  public function update(Hotel $hotel, array $data): Hotel
  {
    return DB::transaction(function () use ($hotel, $data) {

      if (isset($data['logo']) && $data['logo'] instanceof UploadedFile) {

        $this->deleteLogo($hotel);

        $data['logo'] = $this->uploadLogo($data['logo']);
      }

      $hotel->update($data);

      event(new HotelUpdated($hotel));

      return $hotel->fresh(['settings']);
    });
  }

  // Delete a hotel
  public function delete(Hotel $hotel): void
  {
    DB::transaction(function () use ($hotel) {

      $this->deleteLogo($hotel);

      $hotel->delete();

      event(new HotelDeleted($hotel));
    });
  }

  // Upload hotel logo
  private function uploadLogo(UploadedFile $logo): string
  {
    return $logo->store('hotels/logos', 'public');
  }

  // Delete existing hotel logo
  private function deleteLogo(Hotel $hotel): void
  {
    if ($hotel->logo && Storage::disk('public')->exists($hotel->logo)) {
      Storage::disk('public')->delete($hotel->logo);
    }
  }

  // Create default settings for a hotel
  private function createDefaultSettings(Hotel $hotel): void
  {
    $hotel->settings()->create([
      'currency' => 'KES',
      'timezone' => 'Africa/Nairobi',
      'check_in_time' => '14:00',
      'check_out_time' => '11:00',
      'tax_rate' => 16,
      'booking_prefix' => 'BK',
      'invoice_prefix' => 'INV',
      'booking_cancellation_hours' => 24,
      'allow_overbooking' => false,
      'default_checkout_grace_minutes' => 30,
      'late_checkout_fee' => 0,
      'early_checkin_fee' => 0,
    ]);
  }
}
