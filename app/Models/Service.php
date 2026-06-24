<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsAuditTrail;  

class Service extends Model
{
    use SoftDeletes;
    use LogsAuditTrail;

    protected $fillable = [
        'name',
        'description',
        'price',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_services');
    }
}
