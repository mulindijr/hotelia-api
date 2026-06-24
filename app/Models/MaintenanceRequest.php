<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceRequest extends Model
{
    use SoftDeletes;

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
