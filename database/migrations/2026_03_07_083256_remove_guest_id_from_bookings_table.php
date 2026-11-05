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
        Schema::table('bookings', function (Blueprint $table) {
            // Don't try to drop index - just drop the column
            // SQLite doesn't handle named foreign key constraints well
            if (Schema::hasColumn('bookings', 'guest_id')) {
                try {
                    $table->dropForeign(['guest_id']);
                } catch (\Exception $e) {
                    // Foreign key constraint might not exist
                }
                $table->dropColumn('guest_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {});
    }
};
