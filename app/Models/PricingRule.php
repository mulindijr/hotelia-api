<?php

namespace App\Models;

use App\Traits\LogsAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PricingRule extends Model
{
    use HasFactory, LogsAuditTrail, SoftDeletes;

    protected $fillable = [
        'hotel_id',
        'rate_plan_id',
        'room_type_id',
        'name',
        'start_date',
        'end_date',
        'days_of_week',
        'min_nights',
        'max_nights',
        'price_modifier_type',
        'price_modifier_value',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'price_modifier_value' => 'decimal:2',
        'priority' => 'integer',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function ratePlan(): BelongsTo
    {
        return $this->belongsTo(RatePlan::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }
}
