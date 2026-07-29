<?php

namespace App\Constants;

enum Roles: string
{
    case SUPER_ADMIN = 'super_admin';
    case HOTEL_MANAGER = 'hotel_manager';
    case RECEPTIONIST = 'receptionist';
    case HOUSEKEEPER = 'housekeeper';
    case ACCOUNTANT = 'accountant';

    // Get all string values.
    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }
}
