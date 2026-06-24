<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsAuditTrail;
use Illuminate\Support\Facades\Auth;

class Booking extends Model
{
    use SoftDeletes;
    use LogsAuditTrail;

    protected $fillable = [
        'booking_reference',
        'hotel_id',
        'guest_id',
        'check_in_date',
        'check_out_date',
        'adults',
        'children',
        'total_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'booking_rooms');
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
        return $this->belongsToMany(Service::class, 'booking_services');
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
