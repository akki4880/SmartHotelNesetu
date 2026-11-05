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
        // For SQLite, we need to recreate the table without the problematic foreign key
        // But since migrations may have already run, we'll only run this if guest_id exists
        
        if (Schema::hasColumn('bookings', 'guest_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                // Drop only if it exists - wrap in try-catch for SQLite compatibility
                try {
                    $table->dropForeign(['guest_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist in SQLite
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
