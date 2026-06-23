<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\PasswordHistory;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            [
                'email' => 'mulindijrn@gmail.com',
            ],
            [
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'phone' => '0700000000',
                'password' => Hash::make('Password@123'),
                'is_active' => true,
            ]
        );

        // Ensure password history is recorded AFTER user exists
        PasswordHistory::firstOrCreate([
            'user_id' => $user->id,
            'password_hash' => $user->password,
        ]);

        $user->assignRole('super_admin');
    }
}
