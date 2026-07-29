<?php

namespace App\Models;

use App\Traits\LogsAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceRequest extends Model
{
    use HasFactory;
    use LogsAuditTrail;
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
