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
        Schema::create('inventory_goods_receipts', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->foreignId('purchase_order_id')
                ->nullable()
                ->constrained('inventory_purchase_orders')
                ->nullOnDelete();

            $table->foreignId('vendor_id')
                ->constrained('inventory_vendors')
                ->cascadeOnDelete();

            // GRN Details
            $table->string('grn_number');
            $table->date('grn_date');

            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();

            // Financials
            $table->decimal('sub_total', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Unique per hotel
            $table->unique(['hotel_id', 'grn_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_goods_receipts');
    }
};
