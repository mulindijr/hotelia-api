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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->string('booking_reference')->unique();

            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();

            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();

            $table->date('check_in_date');
            $table->date('check_out_date');

            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);

            $table->decimal('total_amount', 12, 2);

            $table->enum('status', [
                'pending',
                'confirmed',
                'checked_in',
                'checked_out',
                'cancelled',
                'no_show'
            ])->default('pending');

            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
