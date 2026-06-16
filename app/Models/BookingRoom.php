<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingRoom extends Model
{
    protected $fillable = [
        'booking_id',
        'room_id',
        'price_per_night',
    ];

    protected $casts = [
        'price_per_night' => 'decimal:2',
    ];
}
