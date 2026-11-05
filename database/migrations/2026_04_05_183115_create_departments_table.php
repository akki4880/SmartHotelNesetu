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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('hotel_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name'); // Front Desk, Kitchen
            $table->string('code')->nullable(); // FD, KIT

            $table->text('description')->nullable();

            $table->boolean('status')->default(1);     // active / inactive

            $table->timestamps();

            // Unique per hotel (VERY IMPORTANT)
            $table->unique(['hotel_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
