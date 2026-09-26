<?php

namespace App\Models;

use App\Traits\LogsAuditTrail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'first_name',
    'last_name',
    'email',
    'phone',
    'password',
    'is_active',
    'last_login_at',
    'password_changed_at',
    'locked_until',
    'failed_login_count',
])]
#[Hidden([
    'password',
    'remember_token',
])]
class User extends Authenticatable
{
    use \Stancl\Tenancy\Database\Concerns\BelongsToTenant;

    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use HasRoles;
    use LogsAuditTrail;
    use Notifiable;
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
            'password_changed_at' => 'datetime',
            'locked_until' => 'datetime',
            'failed_login_count' => 'integer',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function loginHistories()
    {
        return $this->hasMany(LoginHistory::class);
    }

    public function passwordHistories()
    {
        return $this->hasMany(PasswordHistory::class);
    }

    public function hotels()
    {
        return $this->belongsToMany(Hotel::class);
    }

    // Check if the user account is currently locked
    public function isLocked(): bool
    {
        return $this->locked_until && now()->lessThan($this->locked_until);
    }

    // Unlock the user account
    public function unlock(): bool
    {
        return $this->update([
            'locked_until' => null,
            'failed_login_count' => 0,
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('user')
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->dontLogIfAttributesChangedOnly([
                'last_login_at',
                'updated_at',
                'failed_login_count',
            ]);
    }

    /**
     * Determine whether the user belongs to a hotel.
     */
    public function belongsToHotel(Hotel|int $hotel): bool
    {
        $hotelId = $hotel instanceof Hotel ? $hotel->id : $hotel;

        return $this->hotels()
            ->whereKey($hotelId)
            ->exists();
    }

    /**
     * Check if user is a global super admin
     */
    public function isSuperAdmin(): bool
    {
        return \Illuminate\Support\Facades\DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', static::class)
            ->where('model_has_roles.model_id', $this->id)
            ->where('roles.name', 'super_admin')
            ->exists();
    }
}
