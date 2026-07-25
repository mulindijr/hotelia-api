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
use App\Models\HousekeepingTask;
use App\Models\Room;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
                ->where('status', '!=', BookingStatus::NO_SHOW)
                ->whereHas('rooms', function ($query) use ($roomIds) {
                    $query->whereIn('rooms.id', $roomIds);
                })
                ->where(function ($query) use ($checkInDate, $checkOutDate) {
                    $query->where('check_in_date', '<', $checkOutDate)
                          ->where('check_out_date', '>', $checkInDate);
                })
                ->exists();

            $isOverbooked = false;
            if ($conflictingBookings) {
                $allowOverbooking = $hotel->settings?->allow_overbooking ?? false;
                if (!$allowOverbooking) {
                    throw ValidationException::withMessages([
                        'rooms' => 'One or more selected rooms are no longer available for the selected date range.',
                    ]);
                }
                $isOverbooked = true;
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

            $status = $data['status'] ?? BookingStatus::PENDING;

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
                'status' => $status,
                'notes' => $data['notes'] ?? null,
                'is_overbooked' => $isOverbooked,
            ]);

            // 7. Attach rooms
            foreach ($rooms as $room) {
                $booking->rooms()->attach($room->id, ['price_per_night' => $room->roomType->base_price]);
                
                // Set room status to reserved if the booking status is active/pending/confirmed
                if (in_array($status, [BookingStatus::PENDING, BookingStatus::CONFIRMED])) {
                    $room->update(['status' => RoomStatus::RESERVED]);
                }
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

            // Validate status transition if status is being updated
            if (isset($data['status'])) {
                $this->guardStatusTransition($booking, $data['status']);
            }

            // Sync and compute rooms
            $roomIds = $data['rooms'] ?? $booking->rooms()->pluck('rooms.id')->toArray();
            $rooms = Room::whereIn('id', $roomIds)->with('roomType')->lockForUpdate()->get();
            
            // Check conflicts for rooms/dates if changed
            $conflictingBookings = Booking::where('hotel_id', $booking->hotel_id)
                ->where('id', '!=', $booking->id)
                ->where('status', '!=', BookingStatus::CANCELLED)
                ->where('status', '!=', BookingStatus::NO_SHOW)
                ->whereHas('rooms', function ($query) use ($roomIds) {
                    $query->whereIn('rooms.id', $roomIds);
                })
                ->where(function ($query) use ($checkIn, $checkOut) {
                    $query->where('check_in_date', '<', $checkOut)
                          ->where('check_out_date', '>', $checkIn);
                })
                ->exists();

            $isOverbooked = $booking->is_overbooked;
            if ($conflictingBookings) {
                $allowOverbooking = $booking->hotel->settings?->allow_overbooking ?? false;
                if (!$allowOverbooking) {
                    throw ValidationException::withMessages([
                        'rooms' => 'One or more selected rooms are no longer available for the selected date range.',
                    ]);
                }
                $isOverbooked = true;
            } else {
                $isOverbooked = false;
            }

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

            // Get original room IDs to manage room status changes
            $oldRoomIds = $booking->rooms()->pluck('rooms.id')->toArray();

            $booking->update(array_merge($data, [
                'total_amount' => $totalAmount,
                'is_overbooked' => $isOverbooked,
            ]));

            if (isset($data['rooms'])) {
                $syncData = [];
                foreach ($rooms as $room) {
                    $syncData[$room->id] = ['price_per_night' => $room->roomType->base_price];
                }
                $booking->rooms()->sync($syncData);
            }

            // Sync room statuses
            $newStatus = $booking->status;
            // 1. Release removed rooms
            $removedRoomIds = array_diff($oldRoomIds, $roomIds);
            if (!empty($removedRoomIds)) {
                Room::whereIn('id', $removedRoomIds)->update(['status' => RoomStatus::AVAILABLE]);
            }
            // 2. Set statuses for current rooms based on booking status
            foreach ($rooms as $room) {
                if (in_array($newStatus, [BookingStatus::PENDING, BookingStatus::CONFIRMED])) {
                    $room->update(['status' => RoomStatus::RESERVED]);
                } elseif ($newStatus === BookingStatus::CHECKED_IN) {
                    $room->update(['status' => RoomStatus::OCCUPIED]);
                } elseif ($newStatus === BookingStatus::CHECKED_OUT) {
                    $room->update(['status' => RoomStatus::CLEANING]);
                } elseif (in_array($newStatus, [BookingStatus::CANCELLED, BookingStatus::NO_SHOW])) {
                    $room->update(['status' => RoomStatus::AVAILABLE]);
                }
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
            $this->guardStatusTransition($booking, BookingStatus::CANCELLED);

            // Enforce cancellation window for confirmed bookings
            if ($booking->status === BookingStatus::CONFIRMED) {
                $cancellationHours = $booking->hotel->settings?->booking_cancellation_hours ?? 24;
                $checkInTimeSetting = $booking->hotel->settings?->check_in_time ?? '14:00';
                $scheduledCheckIn = Carbon::parse($booking->check_in_date->toDateString() . ' ' . $checkInTimeSetting);

                if (now()->diffInHours($scheduledCheckIn, false) < $cancellationHours) {
                    throw ValidationException::withMessages([
                        'status' => "The booking cannot be cancelled because the cancellation window of {$cancellationHours} hours has passed."
                    ]);
                }
            }

            $booking->update(['status' => BookingStatus::CANCELLED]);

            // Release rooms
            foreach ($booking->rooms as $room) {
                $room->update(['status' => RoomStatus::AVAILABLE]);
            }

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
            $this->guardStatusTransition($booking, BookingStatus::CHECKED_IN);

            $booking->update([
                'status' => BookingStatus::CHECKED_IN,
                'actual_check_in_at' => now(),
            ]);

            // Update room status
            foreach ($booking->rooms as $room) {
                $room->update(['status' => RoomStatus::OCCUPIED]);
            }

            // Regenerate invoice to apply early check-in fees if applicable
            app(\App\Services\Billing\BillingService::class)->regenerateInvoice($booking);

            event(new BookingCheckedIn($booking));

            return $booking->fresh();
        });
    }

    /**
     * Check out guest and auto-create housekeeping tasks for checked-out rooms.
     */
    public function checkOut(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            $this->guardStatusTransition($booking, BookingStatus::CHECKED_OUT);

            $booking->update([
                'status' => BookingStatus::CHECKED_OUT,
                'actual_check_out_at' => now(),
            ]);

            // Update room status to cleaning and create housekeeping task
            foreach ($booking->rooms as $room) {
                $room->update(['status' => RoomStatus::CLEANING]);

                HousekeepingTask::create([
                    'room_id' => $room->id,
                    'status' => 'pending',
                    'scheduled_at' => now(),
                ]);
            }

            // Regenerate invoice to apply late check-out fees if applicable
            app(\App\Services\Billing\BillingService::class)->regenerateInvoice($booking);

            event(new BookingCheckedOut($booking));

            return $booking->fresh();
        });
    }

    /**
     * Mark booking as no-show.
     */
    public function noShow(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            $this->guardStatusTransition($booking, BookingStatus::NO_SHOW);

            $booking->update(['status' => BookingStatus::NO_SHOW]);

            // Release rooms
            foreach ($booking->rooms as $room) {
                $room->update(['status' => RoomStatus::AVAILABLE]);
            }

            // Dispatch an event (we can use booking updated or register a dedicated one if needed)
            event(new BookingUpdated($booking));

            return $booking->fresh();
        });
    }

    /**
     * Guard that validates if transition from old status to new status is allowed.
     */
    protected function guardStatusTransition(Booking $booking, string $newStatus): void
    {
        $oldStatus = $booking->status;

        if ($oldStatus === $newStatus) {
            return;
        }

        $allowed = [
            BookingStatus::PENDING => [
                BookingStatus::CONFIRMED,
                BookingStatus::CANCELLED,
                BookingStatus::CHECKED_IN,
                BookingStatus::NO_SHOW,
            ],
            BookingStatus::CONFIRMED => [
                BookingStatus::CHECKED_IN,
                BookingStatus::CANCELLED,
                BookingStatus::NO_SHOW,
            ],
            BookingStatus::CHECKED_IN => [
                BookingStatus::CHECKED_OUT,
            ],
            // Terminal states:
            BookingStatus::CHECKED_OUT => [],
            BookingStatus::CANCELLED => [],
            BookingStatus::NO_SHOW => [],
        ];

        if (!in_array($newStatus, $allowed[$oldStatus] ?? [])) {
            throw ValidationException::withMessages([
                'status' => "Invalid status transition from '{$oldStatus}' to '{$newStatus}'."
            ]);
        }
    }
}
