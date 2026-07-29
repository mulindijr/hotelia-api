<?php

namespace App\Services\User;

use App\Models\Hotel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserService
{
    /**
     * Create/register a new staff member and assign their hotel inside a DB transaction.
     */
    public function createStaff(Hotel $hotel, array $data): User
    {
        return DB::transaction(function () use ($hotel, $data) {
            $password = $data['password'] ?? Str::random(12);

            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => $password,
                'is_active' => true,
            ]);

            $user->assignRole($data['role']);

            $user->hotels()->attach($hotel->id);

            return $user->fresh();
        });
    }

    /**
     * Update details of a staff member inside a DB transaction.
     */
    public function updateStaff(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $updateFields = array_filter([
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : null,
            ], fn ($val) => ! is_null($val));

            if (! empty($data['password'])) {
                $updateFields['password'] = $data['password'];
            }

            $user->update($updateFields);

            if (! empty($data['role'])) {
                $user->syncRoles([$data['role']]);
            }

            return $user->fresh();
        });
    }

    /**
     * Dissociate a staff member from the hotel inside a DB transaction.
     */
    public function deleteStaff(Hotel $hotel, User $user): void
    {
        DB::transaction(function () use ($hotel, $user) {
            $user->hotels()->detach($hotel->id);

            // If the user has no remaining associated hotels and is not a super admin, soft-delete them
            if ($user->hotels()->count() === 0 && ! $user->hasRole('super_admin')) {
                $user->delete();
            }
        });
    }
}
