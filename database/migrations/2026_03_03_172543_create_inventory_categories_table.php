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
        Schema::create('inventory_categories', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Category Fields
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();

            $table->boolean('status')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Unique per hotel
            $table->unique(['hotel_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_categories');
    }
};
