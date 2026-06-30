<?php

namespace App\Http\Resources\Api\V1\Hotels;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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

            'logo' => $this->logo ? Storage::disk('public')->url($this->logo) : null,
            'is_active' => (bool) $this->is_active,

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            'settings' => new HotelSettingResource($this->whenLoaded('settings')),
        ];
    }
}
