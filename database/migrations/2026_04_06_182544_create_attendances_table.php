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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('hotel_id')
                ->constrained()
                ->cascadeOnDelete();
            // Employee relation
            $table->foreignId('employee_id')
                ->constrained()
                ->onDelete('cascade');

            // Shift relation
            $table->foreignId('shift_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Attendance date
            $table->date('attendance_date');

            // Punch timings
            $table->dateTime('check_in')->nullable();
            $table->dateTime('check_out')->nullable();

            // Working hours
            $table->decimal('working_hours', 5, 2)->default(0);

            // Extra work
            $table->decimal('overtime_hours', 5, 2)->default(0);

            // Status
            $table->enum('status', [
                'present',
                'absent',
                'late',
                'half_day',
                'leave',
                'week_off'
            ])->default('present');

            // Late minutes
            $table->integer('late_minutes')->default(0);

            // Notes
            $table->text('remarks')->nullable();

            $table->timestamps();

            // Prevent duplicate attendance per day
            $table->unique(['employee_id', 'attendance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
