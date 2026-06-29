<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsAuditTrail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelSetting extends Model
{
    use LogsAuditTrail;

    protected $fillable = [

        'hotel_id',

        'currency',

        'timezone',

        'check_in_time',

        'check_out_time',

        'default_checkout_grace_minutes',

        'tax_rate',

        'booking_prefix',

        'invoice_prefix',

        'late_checkout_fee',

        'early_checkin_fee',

        'booking_cancellation_hours',

        'allow_overbooking',

        'language',

        'is_active',
    ];

    protected $casts = [

        'tax_rate' => 'decimal:2',

        'late_checkout_fee' => 'decimal:2',

        'early_checkin_fee' => 'decimal:2',

        'allow_overbooking' => 'boolean',

        'default_checkout_grace_minutes' => 'integer',

        'booking_cancellation_hours' => 'integer',

        'is_active' => 'boolean',
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
}
