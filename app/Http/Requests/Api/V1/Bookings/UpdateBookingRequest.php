<?php

namespace App\Http\Requests\Api\V1\Bookings;

use App\Constants\BookingStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $hotelId = $this->route('hotel')?->id;

        return [
            'guest_id' => [
                'sometimes',
                'integer',
                Rule::exists('guests', 'id'),
            ],
            'check_in_date' => ['sometimes', 'date', 'after_or_equal:today'],
            'check_out_date' => ['sometimes', 'date', 'after:check_in_date'],
            'adults' => ['sometimes', 'integer', 'min:1'],
            'children' => ['sometimes', 'integer', 'min:0'],
            'rooms' => ['sometimes', 'array', 'min:1'],
            'rooms.*' => [
                'required_with:rooms',
                'integer',
                Rule::exists('rooms', 'id')->where('hotel_id', $hotelId),
            ],
            'services' => ['sometimes', 'array'],
            'services.*.id' => [
                'required_with:services',
                'integer',
                Rule::exists('services', 'id')->where('hotel_id', $hotelId),
            ],
            'services.*.quantity' => ['sometimes', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) {
                return;
            }

            $hotel = $this->route('hotel');
            if ($hotel?->settings?->allow_overbooking) {
                return;
            }

            // Only run overlap check if rooms and dates are provided
            $booking = $this->route('booking');
            $bookingId = $booking?->id;

            $checkIn = $this->input('check_in_date') ?? $booking?->check_in_date?->toDateString();
            $checkOut = $this->input('check_out_date') ?? $booking?->check_out_date?->toDateString();
            
            // If rooms array is not provided, load the current booking's room IDs
            $roomIds = $this->input('rooms');
            if (is_null($roomIds) && $booking) {
                $roomIds = $booking->rooms()->pluck('rooms.id')->toArray();
            }

            if (empty($roomIds) || !$checkIn || !$checkOut) {
                return;
            }

            $conflictingBookings = \App\Models\Booking::where('id', '!=', $bookingId)
                ->where('status', '!=', BookingStatus::CANCELLED)
                ->whereHas('rooms', function ($query) use ($roomIds) {
                    $query->whereIn('rooms.id', $roomIds);
                })
                ->where(function ($query) use ($checkIn, $checkOut) {
                    $query->where('check_in_date', '<', $checkOut)
                          ->where('check_out_date', '>', $checkIn);
                })
                ->with('rooms')
                ->get();

            if ($conflictingBookings->isNotEmpty()) {
                foreach ($conflictingBookings as $b) {
                    foreach ($b->rooms as $room) {
                        if (in_array($room->id, $roomIds)) {
                            $validator->errors()->add('rooms', "Room {$room->room_number} is already booked from {$b->check_in_date->toDateString()} to {$b->check_out_date->toDateString()}.");
                        }
                    }
                }
            }
        });
    }
}
