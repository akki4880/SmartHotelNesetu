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
        Schema::create('room_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();

            $table->decimal('base_price', 10, 2);
            $table->decimal('weekend_price', 10, 2)->nullable();
            $table->decimal('extra_guest_price', 10, 2)->nullable();

            $table->unsignedTinyInteger('base_guest_count');
            $table->unsignedTinyInteger('max_extra_guests');

            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['hotel_id', 'room_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_rates');
    }
};
