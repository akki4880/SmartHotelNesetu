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
        Schema::create('hourly_rates', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();

            // Hourly pricing rules
            $table->unsignedTinyInteger('base_hours'); // e.g. 2 hours
            $table->decimal('base_price', 10, 2);      // e.g. 300
            $table->decimal('extra_hour_price', 10, 2); // e.g. 100 per hour

            // Guest rules (hotel specific)
            $table->unsignedTinyInteger('base_guest_count');
            $table->unsignedTinyInteger('max_extra_guests')->default(0);

            // Optional constraints
            $table->unsignedTinyInteger('max_hours')->nullable(); // e.g. max 12 hours

            // Status
            $table->boolean('status')->default(true);

            $table->timestamps();

            // Ensure one hourly rate per hotel + room type
            $table->unique(
                ['hotel_id', 'room_type_id'],
                'hotel_roomtype_hourly_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hourly_rates');
    }
};
