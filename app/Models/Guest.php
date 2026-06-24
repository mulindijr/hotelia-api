<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsAuditTrail;

class Guest extends Model
{
    use SoftDeletes;
    use LogsAuditTrail;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'nationality',
        'national_id',
        'passport_number',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
