<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [

            // Hotels
            'view hotels',
            'create hotels',
            'update hotels',
            'delete hotels',

            // Room Types
            'view room types',
            'create room types',
            'update room types',
            'delete room types',

            // Rooms
            'view rooms',
            'create rooms',
            'update rooms',
            'delete rooms',

            // Amenities
            'view amenities',
            'create amenities',
            'update amenities',
            'delete amenities',

            // Guests
            'view guests',
            'create guests',
            'update guests',
            'delete guests',

            // Bookings
            'view bookings',
            'create bookings',
            'update bookings',
            'cancel bookings',
            'check in guests',
            'check out guests',

            // Payments
            'view payments',
            'create payments',
            'update payments',
            'refund payments',

            // Invoices
            'view invoices',
            'create invoices',
            'update invoices',

            // Services
            'view services',
            'create services',
            'update services',
            'delete services',

            // Housekeeping
            'view housekeeping',
            'manage housekeeping',

            // Maintenance
            'view maintenance',
            'manage maintenance',

            // Users
            'view users',
            'create users',
            'update users',
            'delete users',

            // Reports
            'view reports',

            // Activity Logs
            'view activity logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $superAdmin = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        $hotelManager = Role::firstOrCreate([
            'name' => 'hotel_manager',
            'guard_name' => 'web',
        ]);

        $receptionist = Role::firstOrCreate([
            'name' => 'receptionist',
            'guard_name' => 'web',
        ]);

        $housekeeper = Role::firstOrCreate([
            'name' => 'housekeeper',
            'guard_name' => 'web',
        ]);

        $accountant = Role::firstOrCreate([
            'name' => 'accountant',
            'guard_name' => 'web',
        ]);

        // Super Admin gets everything
        $superAdmin->givePermissionTo(Permission::all());

        // Hotel Manager
        $hotelManager->givePermissionTo([
            'view hotels',
            'update hotels',

            'view room types',
            'create room types',
            'update room types',

            'view rooms',
            'create rooms',
            'update rooms',

            'view bookings',
            'create bookings',
            'update bookings',
            'cancel bookings',

            'view guests',

            'view housekeeping',
            'manage housekeeping',

            'view maintenance',
            'manage maintenance',

            'view reports',
        ]);

        // Receptionist
        $receptionist->givePermissionTo([
            'view rooms',

            'view guests',
            'create guests',
            'update guests',

            'view bookings',
            'create bookings',
            'update bookings',

            'check in guests',
            'check out guests',
        ]);

        // Housekeeper
        $housekeeper->givePermissionTo([
            'view housekeeping',
            'manage housekeeping',
        ]);

        // Accountant
        $accountant->givePermissionTo([
            'view payments',
            'create payments',
            'update payments',
            'refund payments',

            'view invoices',
            'create invoices',
            'update invoices',

            'view reports',
        ]);
    }
}
