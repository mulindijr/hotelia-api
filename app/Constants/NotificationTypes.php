<?php

namespace App\Constants;

final class NotificationTypes
{
  public const HOTEL_CREATED = 'hotel_created';

  public const HOTEL_UPDATED = 'hotel_updated';

  public const HOTEL_DELETED = 'hotel_deleted';

  public const BOOKING_CREATED = 'booking_created';

  public const BOOKING_CANCELLED = 'booking_cancelled';

  public const CHECK_IN = 'check_in';

  public const CHECK_OUT = 'check_out';

  public const PAYMENT_RECEIVED = 'payment_received';

  public const INVOICE_CREATED = 'invoice_created';

  public const MAINTENANCE_CREATED = 'maintenance_created';

  public const HOUSEKEEPING_ASSIGNED = 'housekeeping_assigned';
}
