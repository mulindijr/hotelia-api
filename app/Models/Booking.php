<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsAuditTrail;
use Illuminate\Support\Facades\Auth;

class Booking extends Model
{
    use HasFactory;
    use SoftDeletes;
    use LogsAuditTrail;

    protected $fillable = [
        'booking_reference',
        'hotel_id',
        'guest_id',
        'rate_plan_id',
        'check_in_date',
        'check_out_date',
        'adults',
        'children',
        'total_amount',
        'status',
        'notes',
        'actual_check_in_at',
        'actual_check_out_at',
        'is_overbooked',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'total_amount' => 'decimal:2',
        'actual_check_in_at' => 'datetime',
        'actual_check_out_at' => 'datetime',
        'is_overbooked' => 'boolean',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function ratePlan()
    {
        return $this->belongsTo(RatePlan::class);
    }

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'booking_rooms')->withPivot('price_per_night')->withTimestamps();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(BookingStatusHistory::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'booking_services')->withPivot(['quantity', 'price'])->withTimestamps();
    }

    protected static function booted()
    {
        static::updating(function ($booking) {
            if ($booking->isDirty('status')) {
                BookingStatusHistory::create([
                    'booking_id' => $booking->id,
                    'old_status' => $booking->getOriginal('status'),
                    'new_status' => $booking->status,
                    'changed_by' => Auth::id(),
                ]);
            }
        });
    }
}
