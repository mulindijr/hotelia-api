<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsAuditTrail;

class RoomType extends Model
{
    use SoftDeletes;
    use LogsAuditTrail;

    protected $fillable = [
        'hotel_id',
        'name',
        'description',
        'capacity',
        'beds',
        'base_price',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'room_type_amenity');
    }
}
