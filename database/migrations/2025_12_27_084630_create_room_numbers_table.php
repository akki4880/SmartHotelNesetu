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
        Schema::create('room_numbers', function (Blueprint $table) {
            $table->id();

            // Hotel & room type
            $table->unsignedBigInteger('hotel_id');
            $table->unsignedBigInteger('room_type_id');

            // Room details
            $table->string('room_number'); // 101, A-201, G1

            // Room status
            $table->tinyInteger('status')
                ->default(0)
                ->comment('0=Available, 1=Occupied, 2=Maintenance, 3=Dirty, 4=Cleaning, 5=Blocked');

            $table->timestamps();

            // One room number per hotel
            $table->unique(['hotel_id', 'room_number']);

            // Optional FKs
            // $table->foreign('hotel_id')->references('id')->on('hotels')->onDelete('cascade');
            // $table->foreign('room_type_id')->references('id')->on('room_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_numbers');
    }
};
