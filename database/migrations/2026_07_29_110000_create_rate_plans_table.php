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
        Schema::create('rate_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('code')->index();
            $table->text('description')->nullable();

            // 'percentage' or 'fixed'
            $table->string('modifier_type')->default('percentage');
            // e.g. -10.00 for 10% discount, +15.00 for $15 add-on
            $table->decimal('modifier_value', 12, 2)->default(0.00);

            $table->string('cancellation_policy')->nullable(); // e.g., 'flexible', 'non_refundable'
            $table->string('meal_plan')->nullable(); // e.g., 'room_only', 'breakfast', 'all_inclusive'

            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rate_plans');
    }
};
