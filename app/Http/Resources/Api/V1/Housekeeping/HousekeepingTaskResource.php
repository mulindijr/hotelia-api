<?php

namespace App\Http\Resources\Api\V1\Housekeeping;

use App\Http\Resources\Api\V1\Rooms\RoomResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HousekeepingTaskResource extends JsonResource
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
            'room_id' => $this->room_id,
                        'assigned_to' => $this->assigned_to ? (int) $this->assigned_to : null,
            'assigned_user' => new \App\Http\Resources\Api\V1\Users\UserResource($this->whenLoaded('assignedTo')),
            'status' => $this->status,
            'scheduled_at' => $this->scheduled_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'room' => new RoomResource($this->whenLoaded('room')),
        ];
    }
}
