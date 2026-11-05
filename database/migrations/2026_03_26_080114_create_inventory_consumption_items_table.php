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
        Schema::create('inventory_consumption_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('consumption_id')
                ->constrained('inventory_consumptions')
                ->cascadeOnDelete();

            $table->foreignId('item_id')
                ->constrained('inventory_items')
                ->cascadeOnDelete();

            $table->foreignId('unit_id')
                ->constrained('inventory_units')
                ->cascadeOnDelete();

            $table->decimal('quantity', 12, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_consumption_items');
    }
};
