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
        Schema::create('weekly_offs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('hotel_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('employee_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('day_of_week', [
                'monday',
                'tuesday',
                'wednesday',
                'thursday',
                'friday',
                'saturday',
                'sunday'
            ]);

            $table->timestamps();

            $table->unique(['employee_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_offs');
    }
};
