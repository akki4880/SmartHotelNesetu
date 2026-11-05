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
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hotel_id');
            $table->string('name'); // Deluxe, Suite, Standard
            $table->text('description')->nullable();
            $table->boolean('status')->default(1);  // 1 = active, 0 = inactive
            $table->unsignedTinyInteger('default_base_guests')->default(2);
            $table->unsignedTinyInteger('default_max_extra_guests')->default(0);
            $table->timestamps();
            $table->unique(['hotel_id', 'name']);
            $table->foreign('hotel_id')->references('id')->on('hotels')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
