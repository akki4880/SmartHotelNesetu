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
        Schema::create('booking_guest_id_proofs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_guest_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('id_type');
            $table->string('id_number')->nullable();
            $table->string('id_file_path')->nullable();
            $table->date('issued_on')->nullable();
            $table->date('expires_on')->nullable();
            
            $table->boolean('is_primary')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_guest_id_proofs');
    }
};
