@component('mail::message')
# Invoice Paid - Thank You!

Dear {{ $guest->first_name }} {{ $guest->last_name }},

This email confirms that your invoice has been fully paid. Thank you!

**Invoice Number:** {{ $invoice->invoice_number }}
**Status:** {{ strtoupper($invoice->status) }}

### Summary
* **Total Paid:** **{{ number_format($invoice->total_amount, 2) }} {{ $hotel->settings?->currency ?? 'KES' }}**

We hope you enjoyed your stay at {{ $hotel->name }}. We look forward to hosting you again!

@component('mail::button', ['url' => config('app.url') . '/bookings/' . $booking->id . '/invoice'])
View Invoice Details
@endcomponent

Regards,
{{ $hotel->name }} Management
@endcomponent
