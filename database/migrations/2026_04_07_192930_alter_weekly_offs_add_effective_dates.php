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
        Schema::table('weekly_offs', function (Blueprint $table) {

            // Drop foreign key first
            $table->dropForeign(['employee_id']);

            // Drop unique index
            $table->dropUnique('weekly_offs_employee_id_day_of_week_unique');

            // Recreate foreign key
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weekly_offs', function (Blueprint $table) {

            // Drop new columns
            $table->dropColumn(['effective_from', 'effective_to']);

            // Recreate unique constraint
            $table->unique(['employee_id', 'day_of_week']);
        });
    }
};
