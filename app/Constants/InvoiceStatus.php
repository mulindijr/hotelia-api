<?php

namespace App\Constants;

final class InvoiceStatus
{
    public const DRAFT = 'draft';
    public const SENT = 'sent';
    public const PAID = 'paid';
    public const PARTIALLY_PAID = 'partially_paid';
    public const OVERDUE = 'overdue';
    public const VOID = 'void';
}
