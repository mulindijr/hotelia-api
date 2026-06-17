<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            [
                'email' => 'admin@hotelia.com',
            ],
            [
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'phone' => '0700000000',
                'password' => 'Password@123',
                'is_active' => true,
            ]
        );

        $user->assignRole('super_admin');
    }
}
