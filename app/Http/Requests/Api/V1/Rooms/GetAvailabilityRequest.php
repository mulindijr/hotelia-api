<?php

namespace App\Http\Requests\Api\V1\Rooms;

use App\Constants\Permissions;
use Illuminate\Foundation\Http\FormRequest;

class GetAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        $hotel = $this->route('hotel');
        $user = $this->user();

        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->belongsToHotel($hotel) && $user->hasPermissionTo(Permissions::VIEW_AVAILABILITY);
    }

    public function rules(): array
    {
        return [
            'start_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date', 'date_format:Y-m-d', 'after:start_date'],
            'room_type_id' => ['nullable', 'integer', 'exists:room_types,id'],
            'rate_plan_id' => ['nullable', 'integer', 'exists:rate_plans,id'],
        ];
    }
}
