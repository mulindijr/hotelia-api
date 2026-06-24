<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsAuditTrail;
use Illuminate\Support\Facades\Auth;

class Room extends Model
{
    use SoftDeletes;
    use LogsAuditTrail;

    protected $fillable = [
        'hotel_id',
        'room_type_id',
        'room_number',
        'floor',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_rooms');
    }

    public function statusHistory()
    {
        return $this->hasMany(RoomStatusHistory::class);
    }

    protected static function booted()
    {
        static::updating(function ($room) {
            if ($room->isDirty('status')) {
                RoomStatusHistory::create([
                    'room_id' => $room->id,
                    'old_status' => $room->getOriginal('status'),
                    'new_status' => $room->status,
                    'changed_by' => Auth::id(),
                ]);
            }
        });
    }
}
