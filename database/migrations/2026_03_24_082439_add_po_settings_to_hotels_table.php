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
            $table->string('po_prefix')->default('PO-')->after('is_gst_applicable');

            $table->unsignedBigInteger('po_start_number')
                  ->default(1)
                  ->after('po_prefix');

            $table->unsignedBigInteger('po_last_number')
                  ->default(0)
                  ->after('po_start_number');
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
