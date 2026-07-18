<?php

namespace App\Http\Resources\Api\V1\Bookings;

use App\Http\Resources\Api\V1\Guests\GuestResource;
use App\Http\Resources\Api\V1\Rooms\RoomResource;
use App\Http\Resources\Api\V1\Services\ServiceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_reference' => $this->booking_reference,
            'hotel_id' => $this->hotel_id,
            'guest_id' => $this->guest_id,
            'check_in_date' => $this->check_in_date?->toDateString(),
            'check_out_date' => $this->check_out_date?->toDateString(),
            'adults' => (int) $this->adults,
            'children' => (int) $this->children,
            'total_amount' => (float) $this->total_amount,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'guest' => new GuestResource($this->whenLoaded('guest')),
            'rooms' => RoomResource::collection($this->whenLoaded('rooms')),
            'services' => ServiceResource::collection($this->whenLoaded('services')),
        ];
    }
}
