<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsAuditTrail;

class Hotel extends Model
{
    use LogsAuditTrail;
    
    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'country',
        'city',
        'address',
        'description',
        'logo',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function settings()
    {
        return $this->hasOne(HotelSetting::class);
    }

    public function roomTypes()
    {
        return $this->hasMany(RoomType::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
