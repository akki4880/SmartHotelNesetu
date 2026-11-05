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
        Schema::create('inventory_purchase_order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_order_id')
                ->constrained('inventory_purchase_orders')
                ->cascadeOnDelete();

            $table->foreignId('item_id')
                ->constrained('inventory_items')
                ->cascadeOnDelete();

            $table->foreignId('unit_id')
                ->constrained('inventory_units')
                ->cascadeOnDelete();

            $table->decimal('quantity', 12, 2);
            $table->decimal('rate', 12, 2);

            $table->decimal('tax_percentage', 5, 2)->nullable();
            $table->decimal('tax_amount', 12, 2)->default(0);

            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);

            $table->decimal('received_quantity', 12, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_purchase_order_items');
    }
};
