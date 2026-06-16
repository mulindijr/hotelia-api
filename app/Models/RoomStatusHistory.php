<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomStatusHistory extends Model
{
    protected $fillable = [
        'room_id',
        'old_status',
        'new_status',
        'changed_by',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
