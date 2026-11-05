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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            $table->string('month'); // e.g. 2026-04

            $table->decimal('basic_salary', 10, 2);
            $table->decimal('deductions', 10, 2)->default(0);
            $table->decimal('bonus', 10, 2)->default(0);

            $table->decimal('net_salary', 10, 2);

            $table->date('paid_on')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
