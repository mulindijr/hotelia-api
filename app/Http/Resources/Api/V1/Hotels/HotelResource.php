<?php

namespace App\Http\Resources\Api\V1\Hotels;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelResource extends JsonResource
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

            'name' => $this->name,
            'slug' => $this->slug,

            'email' => $this->email,
            'phone' => $this->phone,

            'country' => $this->country,
            'city' => $this->city,
            'address' => $this->address,

            'description' => $this->description,

            'logo' => $this->logo,
            'is_active' => $this->is_active,

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
