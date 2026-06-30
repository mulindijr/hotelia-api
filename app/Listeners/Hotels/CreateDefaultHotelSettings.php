<?php

namespace App\Listeners\Hotels;

use App\Events\Hotels\HotelCreated;
use App\Models\HotelSetting;
use Illuminate\Support\Facades\DB;

class CreateDefaultHotelSettings
{
    /**
     * Handle the event.
     */
    public function handle(HotelCreated $event): void
    {
        $hotel = $event->hotel;

        DB::transaction(function () use ($hotel) {
            
            HotelSetting::firstOrCreate(
                ['hotel_id' => $hotel->id],
                [
                    'currency' => 'KES',
                    'timezone' => 'Africa/Nairobi',
                    'language' => 'en',

                    'check_in_time' => '14:00',
                    'check_out_time' => '11:00',

                    'default_checkout_grace_minutes' => 30,

                    'tax_rate' => 16.00,

                    'booking_prefix' => 'BK',
                    'invoice_prefix' => 'INV',

                    'late_checkout_fee' => 0,
                    'early_checkin_fee' => 0,

                    'booking_cancellation_hours' => 24,

                    'allow_overbooking' => false,
                ]                
            );
        });
    }
}
