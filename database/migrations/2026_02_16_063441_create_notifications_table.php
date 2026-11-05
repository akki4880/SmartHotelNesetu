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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('hotel_id');
            // null = global notification

            $table->string('title');
            $table->text('message');

            $table->enum('type', ['manual', 'system'])->default('system');

            $table->unsignedBigInteger('sender_id')->nullable();

            // receiver (nullable if broadcast)
            $table->unsignedBigInteger('receiver_id')->nullable();

            // NEW ⭐ send by role
            $table->string('receiver_role')->nullable();
            // admin, hotel_admin, accountant etc

            // NEW ⭐ priority
            $table->enum('priority', ['low', 'medium', 'high'])->default('low');

            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->boolean('is_read')->default(0);
            $table->timestamp('read_at')->nullable();

            // NEW ⭐ archive/delete without removing from DB
            $table->boolean('is_deleted')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
