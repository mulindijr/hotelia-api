<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    protected $fillable = [
        'room_id',
        'reported_by',
        'description',
        'priority',
        'status',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
