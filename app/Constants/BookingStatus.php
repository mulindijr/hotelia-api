<?php

namespace App\Constants;

final class BookingStatus
{
    public const PENDING = 'pending';
    public const CONFIRMED = 'confirmed';
    public const CHECKED_IN = 'checked_in';
    public const CHECKED_OUT = 'checked_out';
    public const CANCELLED = 'cancelled';
    public const NO_SHOW = 'no_show';
}
