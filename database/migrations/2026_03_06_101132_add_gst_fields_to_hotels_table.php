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
        Schema::table('hotels', function (Blueprint $table) {
            $table->decimal('cgst_percentage', 5, 2)->default(9)->after('pan_number');
            $table->decimal('sgst_percentage', 5, 2)->default(9)->after('cgst_percentage');
            $table->decimal('igst_percentage', 5, 2)->default(18)->after('sgst_percentage');
            $table->enum('invoice_tax_type', ['inclusive', 'exclusive'])->default('exclusive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            //
        });
    }
};
