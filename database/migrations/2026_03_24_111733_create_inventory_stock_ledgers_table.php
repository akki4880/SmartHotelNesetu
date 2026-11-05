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
        Schema::create('inventory_stock_ledgers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('inventory_items')->cascadeOnDelete();

            $table->string('type'); // GRN / ISSUE / ADJUSTMENT
            $table->string('reference_number')->nullable(); // GRN number / PO etc

            $table->decimal('quantity', 12, 2);
            $table->enum('flow', ['in', 'out']); // in = add, out = reduce

            $table->decimal('balance_after', 12, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_stock_ledgers');
    }
};
