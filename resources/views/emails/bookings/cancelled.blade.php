@component('mail::message')
# Booking Cancellation Confirmation

Dear {{ $guest->first_name }} {{ $guest->last_name }},

This email confirms that your booking at {{ $hotel->name }} has been cancelled.

**Booking Reference:** {{ $booking->booking_reference }}

### Cancelled Stay Details
* **Check-in Date Was:** {{ $booking->check_in_date->toDateString() }}
* **Check-out Date Was:** {{ $booking->check_out_date->toDateString() }}

If you have any questions regarding refunds or rebooking, please feel free to reach out to our front desk.

Regards,
{{ $hotel->name }} Management
@endcomponent
