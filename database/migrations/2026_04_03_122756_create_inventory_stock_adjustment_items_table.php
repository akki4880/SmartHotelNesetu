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
        Schema::create('inventory_stock_adjustment_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('adjustment_id')
                ->constrained('inventory_stock_adjustments')
                ->cascadeOnDelete();

            $table->foreignId('item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('inventory_units')->cascadeOnDelete();

            $table->decimal('system_stock', 12, 2);   // current_stock
            $table->decimal('physical_stock', 12, 2); // entered by user

            $table->decimal('difference', 12, 2);     // physical - system

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_stock_adjustment_items');
    }
};
