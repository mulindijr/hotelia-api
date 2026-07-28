@component('mail::message')
# Invoice Issued

Dear {{ $guest->first_name }} {{ $guest->last_name }},

An invoice has been generated or updated for your stay at {{ $hotel->name }}.

**Invoice Number:** {{ $invoice->invoice_number }}
**Status:** {{ strtoupper($invoice->status) }}

### Breakdown
@foreach ($invoice->items as $item)
* **{{ $item->description }}**: {{ number_format($item->total_price, 2) }} {{ $hotel->settings?->currency ?? 'KES' }}
@endforeach

---
* **Subtotal:** {{ number_format($invoice->subtotal, 2) }} {{ $hotel->settings?->currency ?? 'KES' }}
* **Tax Amount ({{ $hotel->settings?->tax_rate ?? 0 }}%):** {{ number_format($invoice->tax_amount, 2) }} {{ $hotel->settings?->currency ?? 'KES' }}
* **Total Amount:** **{{ number_format($invoice->total_amount, 2) }} {{ $hotel->settings?->currency ?? 'KES' }}**

Please arrange for payment at your earliest convenience.

@component('mail::button', ['url' => config('app.url') . '/bookings/' . $booking->id . '/invoice'])
View Invoice Details
@endcomponent

Regards,
{{ $hotel->name }} Management
@endcomponent
