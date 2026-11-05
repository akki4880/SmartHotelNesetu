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
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();

            // Multi-hotel support
            $table->foreignId('hotel_id')
                ->constrained()
                ->cascadeOnDelete();

            // Employee relation
            $table->foreignId('employee_id')
                ->constrained()
                ->cascadeOnDelete();

            // Document Info
            $table->string('document_name'); // Aadhar, PAN, Contract
            $table->string('file_path');     // stored file path

            // Optional fields
            $table->date('expiry_date')->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};
