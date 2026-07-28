@component('mail::message')
# Booking Modification Notice

Dear {{ $guest->first_name }} {{ $guest->last_name }},

Your booking at {{ $hotel->name }} has been updated. Here are the revised details:

**Booking Reference:** {{ $booking->booking_reference }}

### Revised Stay Details
* **Check-in Date:** {{ $booking->check_in_date->toDateString() }} (after {{ $hotel->settings?->check_in_time ?? '14:00' }})
* **Check-out Date:** {{ $booking->check_out_date->toDateString() }} (before {{ $hotel->settings?->check_out_time ?? '11:00' }})
* **Guests:** {{ $booking->adults }} Adults, {{ $booking->children }} Children
* **Total Amount:** {{ number_format($booking->total_amount, 2) }} {{ $hotel->settings?->currency ?? 'KES' }}

If you did not request this change, please contact us immediately.

@component('mail::button', ['url' => config('app.url') . '/bookings/' . $booking->id])
View Booking Details
@endcomponent

Regards,
{{ $hotel->name }} Management
@endcomponent
