<?php

namespace App\Policies;

use App\Constants\Permissions;
use App\Models\Hotel;
use App\Models\PricingRule;
use App\Models\User;

class PricingRulePolicy
{
    /**
     * Before hook for Super Admins.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user, Hotel $hotel): bool
    {
        return $user->belongsToHotel($hotel) && $user->hasPermissionTo(Permissions::VIEW_PRICING_RULES);
    }

    public function create(User $user, Hotel $hotel): bool
    {
        return $user->belongsToHotel($hotel) && $user->hasPermissionTo(Permissions::MANAGE_PRICING_RULES);
    }

    public function view(User $user, PricingRule $pricingRule, ?Hotel $hotel = null): bool
    {
        if ($hotel && (int) $pricingRule->hotel_id !== (int) $hotel->id) {
            return false;
        }

        return $user->belongsToHotel($pricingRule->hotel_id) && $user->hasPermissionTo(Permissions::VIEW_PRICING_RULES);
    }

    public function update(User $user, PricingRule $pricingRule, ?Hotel $hotel = null): bool
    {
        if ($hotel && (int) $pricingRule->hotel_id !== (int) $hotel->id) {
            return false;
        }

        return $user->belongsToHotel($pricingRule->hotel_id) && $user->hasPermissionTo(Permissions::MANAGE_PRICING_RULES);
    }

    public function delete(User $user, PricingRule $pricingRule, ?Hotel $hotel = null): bool
    {
        if ($hotel && (int) $pricingRule->hotel_id !== (int) $hotel->id) {
            return false;
        }

        return $user->belongsToHotel($pricingRule->hotel_id) && $user->hasPermissionTo(Permissions::MANAGE_PRICING_RULES);
    }
}
