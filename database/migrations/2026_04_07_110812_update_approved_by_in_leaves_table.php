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
        Schema::table('leaves', function (Blueprint $table) {

            // 1️⃣ Drop old foreign key
            $table->dropForeign(['approved_by']);

            // 2️⃣ Modify column (make sure it's unsignedBigInteger + nullable)
            $table->unsignedBigInteger('approved_by')->nullable()->change();

            // 3️⃣ Add new foreign key to users table
            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {

            // rollback: remove users FK
            $table->dropForeign(['approved_by']);

            // restore employees FK
            $table->foreign('approved_by')
                ->references('id')
                ->on('employees')
                ->nullOnDelete();
        });
    }
};
