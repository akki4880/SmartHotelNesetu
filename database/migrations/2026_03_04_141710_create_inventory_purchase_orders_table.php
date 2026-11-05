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
        Schema::create('inventory_purchase_orders', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')
                ->constrained('inventory_vendors')
                ->cascadeOnDelete();

            // PO Details
            $table->string('po_number');
            $table->date('po_date');
            $table->date('expected_delivery_date')->nullable();

            // Totals
            $table->decimal('sub_total', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);

            // Status
            $table->enum('status', [
                'draft',
                'approved',
                'sent',
                'partially_received',
                'completed',
                'cancelled'
            ])->default('draft');

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Unique per hotel
            $table->unique(['hotel_id', 'po_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_purchase_orders');
    }
};
