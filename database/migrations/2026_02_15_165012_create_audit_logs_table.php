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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');

            $table->unsignedBigInteger('hotel_id');

            // which part of system
            $table->string('module'); // booking, ticket, user etc

            // what happened
            $table->string('action'); // create, update, delete, login
            $table->string('event')->nullable();
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable();
            $table->unsignedBigInteger('affected_user_id')->nullable();

            // which record
            $table->unsignedBigInteger('record_id')->nullable();

            // if specific field changed
            $table->string('field_name')->nullable();

            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();

            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
