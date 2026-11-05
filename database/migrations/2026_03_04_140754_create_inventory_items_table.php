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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->foreignId('category_id')
                ->constrained('inventory_categories')
                ->cascadeOnDelete();

            $table->foreignId('unit_id')
                ->constrained('inventory_units')
                ->cascadeOnDelete();

            $table->foreignId('vendor_id')
                ->nullable()
                ->constrained('inventory_vendors')
                ->nullOnDelete();

            // Basic Info
            $table->string('name');
            $table->string('item_code', 30)->nullable();
            $table->string('barcode')->nullable();
            $table->text('description')->nullable();

            // Stock Control
            $table->decimal('opening_stock', 12, 2)->default(0);
            $table->decimal('current_stock', 12, 2)->default(0);
            $table->decimal('minimum_stock', 12, 2)->default(0);
            $table->decimal('maximum_stock', 12, 2)->nullable();

            // Pricing
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->nullable();
            $table->decimal('tax_percentage', 5, 2)->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Unique per hotel
            $table->unique(['hotel_id', 'name']);
            $table->unique(['hotel_id', 'item_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
