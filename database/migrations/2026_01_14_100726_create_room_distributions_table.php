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
        Schema::create('room_distributions', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('hotel_id');
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_number_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();

            // One row = one NIGHT blocked
            $table->date('allocation_date');

            $table->boolean('is_room_assigned')->default(true);

            $table->timestamps();

            $table->unique(
                ['hotel_id', 'room_type_id', 'room_number_id', 'allocation_date'],
                'room_dist_hotel_room_type_num_date_unique'
            );

            // Optional foreign keys (add if tables exist)
            $table->foreign('hotel_id')->references('id')->on('hotels')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_distributions');
    }
};
