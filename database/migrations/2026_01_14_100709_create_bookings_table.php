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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();

            $table->enum('booking_type', ['regular', 'bulk', 'hourly'])->default('regular');

            // Availability (DATE based)
            $table->date('check_in_date');
            $table->date('check_out_date');

            // Actual usage (TIME based – for billing only)
            $table->dateTime('actual_check_in_at')->nullable();
            $table->dateTime('actual_check_out_at')->nullable();

            $table->integer('no_of_rooms')->default(1);

            $table->decimal('base_amount', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->enum('discount_type', ['flat', 'percent'])->nullable();

            $table->decimal('cgst', 10, 2)->default(0);
            $table->decimal('sgst', 10, 2)->default(0);
            $table->decimal('igst', 10, 2)->default(0);

            $table->decimal('total_amount', 10, 2)->default(0);

            $table->decimal('refunded_amount', 10, 2)->default(0);
            $table->enum('refund_status', ['none', 'partial', 'full'])->default('none');
            $table->enum('status', [
                'pending',
                'confirmed',
                'checked_in',
                'checked_out',
                'cancelled',
                'no_show'
            ])->default('pending');

            $table->text('remark')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
