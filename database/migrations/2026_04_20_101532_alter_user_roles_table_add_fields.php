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
        Schema::table('user_roles', function (Blueprint $table) {

            // 🏨 Multi-tenant support
            $table->foreignId('hotel_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();

            // 🔐 Permissions (JSON)
            $table->json('permissions')
                ->nullable()
                ->after('description');

            // 👤 Created By
            $table->foreignId('created_by')
                ->nullable()
                ->after('permissions')
                ->constrained('users')
                ->nullOnDelete();

            // 🔄 Status
            $table->boolean('status')
                ->default(1)
                ->after('created_by');
        });

        // ⚠️ Fix unique constraint
        Schema::table('user_roles', function (Blueprint $table) {

            // Drop old unique on role_name
            $table->dropUnique(['role_name']);

            // Add new composite unique
            $table->unique(['hotel_id', 'role_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_roles', function (Blueprint $table) {

            // Remove composite unique
            $table->dropUnique(['hotel_id', 'role_name']);

            // Restore old unique
            $table->unique('role_name');

            // Drop added columns
            $table->dropForeign(['hotel_id']);
            $table->dropColumn('hotel_id');

            $table->dropColumn('permissions');

            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');

            $table->dropColumn('status');
        });
    }
};
