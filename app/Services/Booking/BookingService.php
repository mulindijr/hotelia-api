<?php

namespace App\Services\Booking;

use App\Constants\BookingStatus;
use App\Constants\RoomStatus;
use App\Events\Bookings\BookingCancelled;
use App\Events\Bookings\BookingCheckedIn;
use App\Events\Bookings\BookingCheckedOut;
use App\Events\Bookings\BookingCreated;
use App\Events\Bookings\BookingUpdated;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingService
{
    /**
     * Create a new booking with DB transaction and pessimistic locking.
     */
    public function create(Hotel $hotel, array $data): Booking
    {
        return DB::transaction(function () use ($hotel, $data) {
            $roomIds = $data['rooms'];
            $checkInDate = $data['check_in_date'];
            $checkOutDate = $data['check_out_date'];

            // 1. Lock target rooms for update to prevent concurrent double-bookings
            $rooms = Room::whereIn('id', $roomIds)
                ->where('hotel_id', $hotel->id)
                ->with('roomType')
                ->lockForUpdate()
                ->get();

            // 2. Perform in-transaction double-booking verification
            $conflictingBookings = Booking::where('hotel_id', $hotel->id)
                ->where('status', '!=', BookingStatus::CANCELLED)
                ->whereHas('rooms', function ($query) use ($roomIds) {
                    $query->whereIn('rooms.id', $roomIds);
                })
                ->where(function ($query) use ($checkInDate, $checkOutDate) {
                    $query->where('check_in_date', '<', $checkOutDate)
                          ->where('check_out_date', '>', $checkInDate);
                })
                ->exists();

            if ($conflictingBookings) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'rooms' => 'One or more selected rooms are no longer available for the selected date range.',
                ]);
            }

            // 3. Generate unique booking reference
            $prefix = $hotel->settings?->booking_prefix ?? 'BK-';
            do {
                $ref = $prefix . date('Ymd') . strtoupper(Str::random(4));
            } while (Booking::where('booking_reference', $ref)->exists());

            // 4. Compute room cost & nights
            $checkIn = Carbon::parse($checkInDate);
            $checkOut = Carbon::parse($checkOutDate);
            $nights = max(1, $checkIn->diffInDays($checkOut));

            $totalRoomCost = $rooms->sum(fn($r) => $r->roomType->base_price * $nights);

            // 5. Attach services and compute cost
            $servicesCost = 0;
            $servicesData = [];
            if (!empty($data['services'])) {
                $services = Service::whereIn('id', collect($data['services'])->pluck('id'))
                    ->where('hotel_id', $hotel->id)
                    ->get()
                    ->keyBy('id');

                foreach ($data['services'] as $s) {
                    $serviceModel = $services->get($s['id']);
                    if ($serviceModel) {
                        $qty = $s['quantity'] ?? 1;
                        $price = $serviceModel->price;
                        $servicesCost += $price * $qty;
                        $servicesData[$s['id']] = ['quantity' => $qty, 'price' => $price];
                    }
                }
            }

            $totalAmount = $totalRoomCost + $servicesCost;

            // 6. Create Booking record
            /** @var Booking $booking */
            $booking = Booking::create([
                'booking_reference' => $ref,
                'hotel_id' => $hotel->id,
                'guest_id' => $data['guest_id'],
                'check_in_date' => $checkInDate,
                'check_out_date' => $checkOutDate,
                'adults' => $data['adults'] ?? 1,
                'children' => $data['children'] ?? 0,
                'total_amount' => $totalAmount,
                'status' => $data['status'] ?? BookingStatus::PENDING,
                'notes' => $data['notes'] ?? null,
            ]);

            // 7. Attach rooms
            foreach ($rooms as $room) {
                $booking->rooms()->attach($room->id, ['price_per_night' => $room->roomType->base_price]);
            }

            // 8. Attach services
            if (!empty($servicesData)) {
                $booking->services()->attach($servicesData);
            }

            event(new BookingCreated($booking));

            return $booking->load(['guest', 'rooms', 'services']);
        });
    }

    /**
     * Update an existing booking.
     */
    public function update(Booking $booking, array $data): Booking
    {
        return DB::transaction(function () use ($booking, $data) {
            $checkIn = $data['check_in_date'] ?? $booking->check_in_date->toDateString();
            $checkOut = $data['check_out_date'] ?? $booking->check_out_date->toDateString();
            $nights = max(1, Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut)));

            // Sync and compute rooms
            $roomIds = $data['rooms'] ?? $booking->rooms()->pluck('rooms.id')->toArray();
            $rooms = Room::whereIn('id', $roomIds)->with('roomType')->lockForUpdate()->get();
            $totalRoomCost = $rooms->sum(fn($r) => $r->roomType->base_price * $nights);

            // Sync and compute services
            $servicesCost = 0;
            if (isset($data['services'])) {
                $servicesData = [];
                $services = Service::whereIn('id', collect($data['services'])->pluck('id'))->get()->keyBy('id');
                foreach ($data['services'] as $s) {
                    $serviceModel = $services->get($s['id']);
                    if ($serviceModel) {
                        $qty = $s['quantity'] ?? 1;
                        $price = $serviceModel->price;
                        $servicesCost += $price * $qty;
                        $servicesData[$s['id']] = ['quantity' => $qty, 'price' => $price];
                    }
                }
                $booking->services()->sync($servicesData);
            } else {
                $servicesCost = $booking->services()->get()->sum(fn($s) => $s->pivot->price * $s->pivot->quantity);
            }

            $totalAmount = $totalRoomCost + $servicesCost;

            $booking->update(array_merge($data, ['total_amount' => $totalAmount]));

            if (isset($data['rooms'])) {
                $syncData = [];
                foreach ($rooms as $room) {
                    $syncData[$room->id] = ['price_per_night' => $room->roomType->base_price];
                }
                $booking->rooms()->sync($syncData);
            }

            event(new BookingUpdated($booking));

            return $booking->fresh()->load(['guest', 'rooms', 'services']);
        });
    }

    /**
     * Cancel a booking.
     */
    public function cancel(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            $booking->update(['status' => BookingStatus::CANCELLED]);

            event(new BookingCancelled($booking));

            return $booking->fresh();
        });
    }

    /**
     * Check in guest.
     */
    public function checkIn(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            $booking->update(['status' => BookingStatus::CHECKED_IN]);

            // Update room status
            foreach ($booking->rooms as $room) {
                $room->update(['status' => RoomStatus::OCCUPIED]);
            }

            event(new BookingCheckedIn($booking));

            return $booking->fresh();
        });
    }

    /**
     * Check out guest.
     */
    public function checkOut(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            $booking->update(['status' => BookingStatus::CHECKED_OUT]);

            // Update room status to cleaning
            foreach ($booking->rooms as $room) {
                $room->update(['status' => RoomStatus::CLEANING]);
            }

            event(new BookingCheckedOut($booking));

            return $booking->fresh();
        });
    }
}
