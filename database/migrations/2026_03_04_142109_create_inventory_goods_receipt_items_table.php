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
        Schema::create('inventory_goods_receipt_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('goods_receipt_id')
                ->constrained('inventory_goods_receipts')
                ->cascadeOnDelete();

            $table->foreignId('purchase_order_item_id')
                ->nullable()
                ->constrained('inventory_purchase_order_items')
                ->nullOnDelete();

            $table->foreignId('item_id')
                ->constrained('inventory_items')
                ->cascadeOnDelete();

            $table->foreignId('unit_id')
                ->constrained('inventory_units')
                ->cascadeOnDelete();

            // Quantity & Pricing
            $table->decimal('received_quantity', 12, 2);
            $table->decimal('rate', 12, 2);

            $table->decimal('tax_percentage', 5, 2)->nullable();
            $table->decimal('tax_amount', 12, 2)->default(0);

            $table->decimal('total', 12, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_goods_receipt_items');
    }
};
