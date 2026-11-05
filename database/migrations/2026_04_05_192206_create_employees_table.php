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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // होटल / multi-tenant support
            $table->foreignId('hotel_id')
                ->constrained()
                ->cascadeOnDelete();

            // Basic Info
            $table->string('employee_code')->unique(); // EMP001
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->text('address')->nullable();
            $table->date('dob')->nullable();
            $table->string('profile_photo')->nullable();

            // Work Info
            $table->date('joining_date');

            $table->foreignId('department_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('user_role_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('shift_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Employment Type
            $table->enum('employment_type', ['full_time', 'part_time', 'contract'])
                ->default('full_time');

            // Salary
            $table->enum('salary_type', ['monthly', 'daily', 'hourly']);
            $table->decimal('salary_amount', 10, 2)->default(0);

            // Status
            $table->boolean('status')->default(1);     // active / inactive

            $table->timestamps();

            // 🔥 Important Constraints
            $table->unique(['hotel_id', 'employee_code']); // unique per hotel
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
