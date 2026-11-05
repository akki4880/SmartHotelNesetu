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
        Schema::create('guest_id_proofs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('guest_id');
            $table->string('id_type')->nullable();        // passport, aadhaar, driving_license, etc
            $table->string('id_number')->nullable();
            $table->string('id_file_path'); // uploaded document
            $table->date('issued_on')->nullable();
            $table->date('expires_on')->nullable();

            $table->timestamps();

            $table->foreign('guest_id')->references('id')->on('guests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_id_proofs');
    }
};
