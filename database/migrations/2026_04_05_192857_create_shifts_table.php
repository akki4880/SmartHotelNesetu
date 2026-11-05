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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();

            // Multi-hotel support
            $table->foreignId('hotel_id')
                ->constrained()
                ->cascadeOnDelete();

            // Shift Details
            $table->string('name'); // Morning, Evening, Night
            $table->time('start_time');
            $table->time('end_time');

            // Optional settings
            $table->integer('grace_minutes')->default(0); // allowed late time
            $table->boolean('is_night_shift')->default(false);

            $table->boolean('status')->default(1);     // active / inactive

            $table->timestamps();

            // Unique shift per hotel
            $table->unique(['hotel_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
