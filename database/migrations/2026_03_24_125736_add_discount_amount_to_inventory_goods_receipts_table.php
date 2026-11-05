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
        Schema::table('inventory_goods_receipts', function (Blueprint $table) {
            $table->decimal('discount_amount', 12, 2)
                ->default(0)
                ->after('tax_amount')
                ->comment('Invoice level discount applied on GRN');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_goods_receipts', function (Blueprint $table) {
            //
        });
    }
};
