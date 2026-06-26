<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsAuditTrail;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hotel extends Model
{
    use LogsAuditTrail;
    use SoftDeletes;
    
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

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
