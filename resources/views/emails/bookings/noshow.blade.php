@component('mail::message')
# Booking No-Show Notice

Dear {{ $guest->first_name }} {{ $guest->last_name }},

We noticed you did not check in for your scheduled reservation today at {{ $hotel->name }}.

**Booking Reference:** {{ $booking->booking_reference }}

### Stay Details
* **Check-in Date:** {{ $booking->check_in_date->toDateString() }}
* **Check-out Date:** {{ $booking->check_out_date->toDateString() }}

As a result, your booking has been marked as a **No-Show** and the rooms have been released. Please refer to our reservation terms or contact us if you wish to adjust this booking.

Regards,
{{ $hotel->name }} Management
@endcomponent
