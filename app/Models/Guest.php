<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsAuditTrail;

use Illuminate\Notifications\Notifiable;

class Guest extends Model
{
    use HasFactory;
    use SoftDeletes;
    use LogsAuditTrail;
    use Notifiable;

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
