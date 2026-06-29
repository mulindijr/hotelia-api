<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hotel_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('hotel_id')->unique()->constrained()->cascadeOnDelete();

            $table->string('currency')->default('KES');
            $table->string('timezone')->default('Africa/Nairobi');
            $table->string('language')->default('en');

            $table->time('check_in_time')->default('14:00');
            $table->time('check_out_time')->default('11:00');
            $table->integer('default_checkout_grace_minutes')->default(30);

            $table->decimal('tax_rate', 5, 2)->default(16.00);
            $table->string('booking_prefix', 10)->default('BK');
            $table->string('invoice_prefix', 10)->default('INV');
            $table->decimal('late_checkout_fee', 12, 2)->default(0.00);
            $table->decimal('early_checkin_fee', 12, 2)->default(0.00);

            $table->integer('booking_cancellation_hours')->default(24);
            $table->boolean('allow_overbooking')->default(false);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_settings');
    }
};
