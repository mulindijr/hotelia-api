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
        Schema::table('rooms', function (Blueprint $table) {
            $table->unique(['hotel_id', 'room_number'], 'rooms_hotel_id_room_number_unique');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['hotel_id', 'status'], 'bookings_hotel_status_index');
            $table->index(['check_in_date', 'check_out_date'], 'bookings_dates_index');
            $table->index(['hotel_id', 'status', 'check_in_date', 'check_out_date'], 'bookings_tenant_availability_index');
        });

        Schema::table('housekeeping_tasks', function (Blueprint $table) {
            $table->index(['room_id', 'status'], 'housekeeping_room_status_index');
        });

        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->index(['room_id', 'status'], 'maintenance_room_status_index');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['booking_id', 'status'], 'payments_booking_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_booking_status_index');
        });

        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropIndex('maintenance_room_status_index');
        });

        Schema::table('housekeeping_tasks', function (Blueprint $table) {
            $table->dropIndex('housekeeping_room_status_index');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_tenant_availability_index');
            $table->dropIndex('bookings_dates_index');
            $table->dropIndex('bookings_hotel_status_index');
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropUnique('rooms_hotel_id_room_number_unique');
        });
    }
};
