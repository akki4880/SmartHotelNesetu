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
        Schema::create('booking_room_type_changes', function (Blueprint $table) {
            $table->id();

            // Link to booking
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();

            // Old and new room details
            $table->foreignId('old_room_type_id')->constrained('room_types')->cascadeOnDelete();
            $table->foreignId('new_room_type_id')->constrained('room_types')->cascadeOnDelete();

            $table->foreignId('old_room_number_id')->nullable()
                ->constrained('room_numbers')
                ->nullOnDelete();

            $table->foreignId('new_room_number_id')->nullable()
                ->constrained('room_numbers')
                ->nullOnDelete();

            // Effective date of change
            $table->date('effective_from');

            // Who made the change
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();

            // Reason for change
            $table->text('reason')->nullable();

            // Pricing impact (if any)
            $table->decimal('price_adjustment', 10, 2)->nullable()->default(0);

            $table->timestamps();

            // Audit trail
            $table->index(['booking_id', 'effective_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_room_type_changes');
    }
};
