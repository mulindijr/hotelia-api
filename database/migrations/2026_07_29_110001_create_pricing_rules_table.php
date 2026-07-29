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
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rate_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('room_type_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Store active days as JSON array e.g., ["friday", "saturday"]
            $table->json('days_of_week')->nullable();

            // Length of stay constraints
            $table->integer('min_nights')->nullable();
            $table->integer('max_nights')->nullable();

            // 'percentage', 'fixed', or 'override'
            $table->string('price_modifier_type')->default('percentage');
            $table->decimal('price_modifier_value', 12, 2);

            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
