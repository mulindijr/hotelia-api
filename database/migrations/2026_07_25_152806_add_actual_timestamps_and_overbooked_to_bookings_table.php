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
        Schema::table('bookings', function (Blueprint $table) {
            $table->dateTime('actual_check_in_at')->nullable()->after('notes');
            $table->dateTime('actual_check_out_at')->nullable()->after('actual_check_in_at');
            $table->boolean('is_overbooked')->default(false)->after('actual_check_out_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['actual_check_in_at', 'actual_check_out_at', 'is_overbooked']);
        });
    }
};
