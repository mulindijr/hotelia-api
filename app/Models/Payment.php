<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsAuditTrail;

class Payment extends Model
{
    use SoftDeletes;
    use LogsAuditTrail;

    protected $fillable = [
        'booking_id',
        'amount',
        'payment_method',
        'transaction_reference',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
