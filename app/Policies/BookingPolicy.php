<?php

namespace App\Policies;

use App\Models\Booking;
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
     * Determine whether the user can view the booking.
     */
    public function view(User $user, Booking $booking): bool
    {
        return $user->belongsToHotel($booking->hotel_id);
    }

    /**
     * Determine whether the user can update the booking.
     */
    public function update(User $user, Booking $booking): bool
    {
        return $user->belongsToHotel($booking->hotel_id);
    }

    /**
     * Determine whether the user can cancel the booking.
     */
    public function cancel(User $user, Booking $booking): bool
    {
        return $user->belongsToHotel($booking->hotel_id);
    }

    /**
     * Determine whether the user can check in guests.
     */
    public function checkIn(User $user, Booking $booking): bool
    {
        return $user->belongsToHotel($booking->hotel_id);
    }

    /**
     * Determine whether the user can check out guests.
     */
    public function checkOut(User $user, Booking $booking): bool
    {
        return $user->belongsToHotel($booking->hotel_id);
    }
}
