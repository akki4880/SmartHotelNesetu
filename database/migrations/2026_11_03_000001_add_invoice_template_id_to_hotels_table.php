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
            if (!Schema::hasColumn('hotels', 'invoice_template_id')) {
                $table->foreignId('invoice_template_id')
                    ->nullable()
                    ->after('invoice_prefix')
                    ->constrained('invoice_templates')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            if (Schema::hasColumn('hotels', 'invoice_template_id')) {
                $table->dropConstrainedForeignId('invoice_template_id');
            }
        });
    }
};
