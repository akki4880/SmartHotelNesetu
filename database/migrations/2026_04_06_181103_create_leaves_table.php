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
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')
                ->constrained()
                ->cascadeOnDelete();
            // Employee relation
            $table->foreignId('employee_id')
                ->constrained()
                ->onDelete('cascade');

            // Leave type
            $table->string('leave_type'); 
            // sick, casual, paid, emergency

            // Dates
            $table->date('from_date');
            $table->date('to_date');
            $table->integer('total_days')->default(1);

            // Reason
            $table->text('reason')->nullable();

            // Status workflow
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'cancelled'
            ])->default('pending');

            // Approval info
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            // Optional HR note
            $table->text('admin_remark')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
