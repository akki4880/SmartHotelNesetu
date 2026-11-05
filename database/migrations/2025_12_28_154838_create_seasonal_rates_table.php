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
        Schema::create('seasonal_rates', function (Blueprint $table) {
            $table->id();

            // Link to room type
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();

            $table->string('season_name')->nullable(); // Optional: Christmas, Diwali, etc.
            $table->date('start_date'); // Season start
            $table->date('end_date');   // Season end
            $table->decimal('seasonal_price', 10, 2); // Price override

            $table->boolean('status')->default(true);
            $table->timestamps();

            // Prevent duplicate seasonal entries for same room type and date range
            $table->unique(['room_type_id', 'start_date', 'end_date'], 'room_type_season_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seasonal_rates');
    }
};
