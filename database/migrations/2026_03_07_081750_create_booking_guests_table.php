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
        Schema::create('booking_guests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained()->nullOnDelete();

            $table->string('full_name');
            $table->string('mobile');
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->date('dob')->nullable();

            $table->unsignedInteger('country_id')->nullable();
            $table->unsignedInteger('state_id')->nullable();

            $table->boolean('is_primary_guest')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_guests');
    }
};
