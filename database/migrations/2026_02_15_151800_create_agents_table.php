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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('hotel_id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();

            $table->string('company_name')->nullable();

            // optional commission rule
            $table->enum('commission_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('commission_value', 10, 2)->default(0);

            $table->boolean('status')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
