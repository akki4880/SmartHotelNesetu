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
        Schema::table('inventory_units', function (Blueprint $table) {

            // ✅ STEP 1: Drop UNIQUE index (IMPORTANT)
            $table->dropUnique('inventory_units_hotel_id_name_unique');

            // ✅ STEP 2: Drop FOREIGN KEY
            $table->dropForeign(['hotel_id']);

            // ✅ STEP 3: Drop COLUMN
            $table->dropColumn('hotel_id');

            // ✅ STEP 4: Add new unique constraints
            $table->unique('name');
            $table->unique('short_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_units', function (Blueprint $table) {

            
        });
    }
};
