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
        Schema::table('permissions', function (Blueprint $table) {
            $table->boolean('is_visible_to_hotel_owner')
                ->default(true)
                ->after('status');

            $table->integer('sequence_for_super_admin')
                ->default(0)
                ->after('is_visible_to_hotel_owner');

            $table->integer('sequence_for_hotel_owner')
                ->default(0)
                ->after('sequence_for_super_admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            //
        });
    }
};
