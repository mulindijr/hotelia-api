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

            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();

            $table->string('currency')->default('KES');

            $table->time('check_in_time')->default('14:00');
            $table->time('check_out_time')->default('11:00');

            $table->decimal('tax_rate', 5, 2)->default(16.00);

            $table->integer('booking_cancellation_hours')->default(24);

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
