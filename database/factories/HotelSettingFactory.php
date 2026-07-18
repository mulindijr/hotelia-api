<?php

namespace Database\Factories;

use App\Models\Hotel;
use App\Models\HotelSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HotelSetting>
 */
class HotelSettingFactory extends Factory
{
    protected $model = HotelSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hotel_id' => Hotel::factory(),
            'currency' => 'KES',
            'timezone' => 'Africa/Nairobi',
            'language' => 'en',
            'check_in_time' => '14:00:00',
            'check_out_time' => '11:00:00',
            'default_checkout_grace_minutes' => 30,
            'tax_rate' => 16.00,
            'booking_prefix' => 'BK',
            'invoice_prefix' => 'INV',
            'late_checkout_fee' => 1000.00,
            'early_checkin_fee' => 1500.00,
            'booking_cancellation_hours' => 24,
            'allow_overbooking' => false,
            'is_active' => true,
        ];
    }

    /**
     * State for custom prefix configurations.
     */
    public function customPrefixes(string $booking, string $invoice): static
    {
        return $this->state(fn (array $attributes) => [
            'booking_prefix' => $booking,
            'invoice_prefix' => $invoice,
        ]);
    }

    /**
     * State for enabling overbooking.
     */
    public function overbookingAllowed(): static
    {
        return $this->state(fn (array $attributes) => [
            'allow_overbooking' => true,
        ]);
    }

    /**
     * State for USD/US localized settings.
     */
    public function usd(): static
    {
        return $this->state(fn (array $attributes) => [
            'currency' => 'USD',
            'timezone' => 'America/New_York',
            'tax_rate' => 8.25,
            'late_checkout_fee' => 50.00,
            'early_checkin_fee' => 75.00,
        ]);
    }
}
