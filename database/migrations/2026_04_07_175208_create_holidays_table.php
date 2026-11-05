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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name'); // Ram Navami, Diwali
            $table->date('date');

            $table->boolean('is_optional')->default(false); // optional holiday

            $table->text('description')->nullable();

            $table->timestamps();

            $table->unique(['hotel_id', 'date']); // no duplicate holiday
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
