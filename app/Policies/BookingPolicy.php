<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\User;

class BookingPolicy
{
    /**
     * Before hook for Super Admins.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view the list of bookings for a hotel.
     */
    public function viewAny(User $user, Hotel $hotel): bool
    {
        return $user->belongsToHotel($hotel);
    }

    /**
     * Determine whether the user can create a booking for a hotel.
     */
    public function create(User $user, Hotel $hotel): bool
    {
        return $user->belongsToHotel($hotel);
    }

    /**
     * Determine whether the user can view the booking.
     */
    public function view(User $user, Booking $booking, ?Hotel $hotel = null): bool
    {
        if ($hotel && (int) $booking->hotel_id !== (int) $hotel->id) {
            return false;
        }

        return $user->belongsToHotel($booking->hotel_id);
    }

    /**
     * Determine whether the user can update the booking.
     */
    public function update(User $user, Booking $booking, ?Hotel $hotel = null): bool
    {
        if ($hotel && (int) $booking->hotel_id !== (int) $hotel->id) {
            return false;
        }

        return $user->belongsToHotel($booking->hotel_id);
    }

    /**
     * Determine whether the user can cancel the booking.
     */
    public function cancel(User $user, Booking $booking, ?Hotel $hotel = null): bool
    {
        if ($hotel && (int) $booking->hotel_id !== (int) $hotel->id) {
            return false;
        }

        return $user->belongsToHotel($booking->hotel_id);
    }

    /**
     * Determine whether the user can check in guests.
     */
    public function checkIn(User $user, Booking $booking, ?Hotel $hotel = null): bool
    {
        if ($hotel && (int) $booking->hotel_id !== (int) $hotel->id) {
            return false;
        }

        return $user->belongsToHotel($booking->hotel_id);
    }

    /**
     * Determine whether the user can check out guests.
     */
    public function checkOut(User $user, Booking $booking, ?Hotel $hotel = null): bool
    {
        if ($hotel && (int) $booking->hotel_id !== (int) $hotel->id) {
            return false;
        }

        return $user->belongsToHotel($booking->hotel_id);
    }

    /**
     * Determine whether the user can mark booking as no-show.
     */
    public function noShow(User $user, Booking $booking, ?Hotel $hotel = null): bool
    {
        if ($hotel && (int) $booking->hotel_id !== (int) $hotel->id) {
            return false;
        }

        return $user->belongsToHotel($booking->hotel_id);
    }
}
