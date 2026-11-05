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
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();

            // Basic Identity
            $table->string('hotel_code')->unique();        // HMS-001
            $table->string('hotel_name');
            $table->string('legal_name')->nullable();
            $table->string('logo')->nullable();

            // Contact Information
            // $table->string('email')->unique();
            $table->string('phone', 20);
            $table->string('alternate_phone', 20)->nullable();
            $table->string('website')->nullable();

            // Address
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('pincode', 10)->nullable();

            // Tax & Finance
            $table->string('gst_number')->nullable();
            $table->string('pan_number')->nullable();
            $table->string('currency')->default('INR');
            $table->string('invoice_prefix')->default('INV');

            $table->time('check_in_time')->default('14:00');
            $table->time('check_out_time')->default('11:00');
            $table->text('cancellation_policy')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->boolean('status')->default(true);
            $table->text('blocked_reason')->nullable();
            // $table->string('timezone')->default('Asia/Kolkata');
            // Audit
            $table->unsignedBigInteger('created_by')->nullable(); // super admin id
            $table->json('room_rate_type')->nullable();
            $table->boolean('is_gst_applicable')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
