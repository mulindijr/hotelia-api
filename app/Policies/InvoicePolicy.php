<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
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

    /**
     * Determine whether the user can view the invoice.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        return $user->belongsToHotel($invoice->booking?->hotel_id);
    }

    /**
     * Determine whether the user can manage the invoice.
     */
    public function manage(User $user, Invoice $invoice): bool
    {
        return $user->belongsToHotel($invoice->booking?->hotel_id);
    }
}
