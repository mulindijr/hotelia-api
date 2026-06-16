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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->string('invoice_number')->unique();

            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();

            $table->decimal('subtotal', 12, 2);

            $table->decimal('tax_amount', 12, 2);

            $table->decimal('total_amount', 12, 2);

            $table->enum('status', [
                'unpaid',
                'partial',
                'paid'
            ])->default('unpaid');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
