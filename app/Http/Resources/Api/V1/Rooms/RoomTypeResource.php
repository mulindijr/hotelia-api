<?php

namespace App\Http\Resources\Api\V1\Rooms;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomTypeResource extends JsonResource
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
            'hotel_id' => $this->hotel_id,
            'name' => $this->name,
            'description' => $this->description,
            'capacity' => (int) $this->capacity,
            'beds' => (int) $this->beds,
            'base_price' => (float) $this->base_price,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'amenities' => AmenityResource::collection($this->whenLoaded('amenities')),
        ];
    }
}
