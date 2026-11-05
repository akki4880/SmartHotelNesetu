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

            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();

            $table->string('invoice_number')->unique();

            // tax configuration snapshot
            $table->boolean('is_gst_applicable')->default(false);
            $table->enum('invoice_tax_type', ['inclusive', 'exclusive'])->default('exclusive');

            $table->decimal('cgst_percentage', 5, 2)->nullable();
            $table->decimal('sgst_percentage', 5, 2)->nullable();
            $table->decimal('igst_percentage', 5, 2)->nullable();

            // amounts
            $table->decimal('room_amount', 10, 2)->default(0);
            $table->decimal('service_amount', 10, 2)->default(0);

            $table->decimal('subtotal', 10, 2)->default(0);

            $table->decimal('cgst_amount', 10, 2)->default(0);
            $table->decimal('sgst_amount', 10, 2)->default(0);
            $table->decimal('igst_amount', 10, 2)->default(0);

            $table->decimal('total_tax', 10, 2)->default(0);

            $table->decimal('grand_total', 10, 2)->default(0);

            $table->timestamp('invoice_date');

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
