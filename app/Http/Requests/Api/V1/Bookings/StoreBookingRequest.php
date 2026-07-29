<?php

namespace App\Http\Requests\Api\V1\Bookings;

use App\Constants\BookingStatus;
use App\Models\Booking;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
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
                'required',
                'integer',
                Rule::exists('guests', 'id'),
            ],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'adults' => ['sometimes', 'integer', 'min:1'],
            'children' => ['sometimes', 'integer', 'min:0'],
            'rooms' => ['required', 'array', 'min:1'],
            'rooms.*' => [
                'required',
                'integer',
                Rule::exists('rooms', 'id')->where('hotel_id', $hotelId),
            ],
            'services' => ['sometimes', 'array'],
            'services.*.id' => [
                'required',
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

            $checkIn = $this->input('check_in_date');
            $checkOut = $this->input('check_out_date');
            $roomIds = $this->input('rooms');

            $conflictingBookings = Booking::where('status', '!=', BookingStatus::CANCELLED)
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
                foreach ($conflictingBookings as $booking) {
                    foreach ($booking->rooms as $room) {
                        if (in_array($room->id, $roomIds)) {
                            $validator->errors()->add('rooms', "Room {$room->room_number} is already booked from {$booking->check_in_date->toDateString()} to {$booking->check_out_date->toDateString()}.");
                        }
                    }
                }
            }
        });
    }
}
