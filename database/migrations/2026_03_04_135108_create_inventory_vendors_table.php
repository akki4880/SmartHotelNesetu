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
        Schema::create('inventory_vendors', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Vendor Basic Info
            $table->string('name');
            $table->string('vendor_code', 30)->nullable();

            // Contact Details
            $table->string('contact_person')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();

            // Address Details
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('pincode', 15)->nullable();

            // Tax & Business Info
            $table->string('gst_number', 30)->nullable();
            $table->string('pan_number', 20)->nullable();

            // Bank Details (Optional but useful later)
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('ifsc_code')->nullable();

            // Status
            $table->boolean('status')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Unique Constraints Per Hotel
            $table->unique(['hotel_id', 'name']);
            $table->unique(['hotel_id', 'vendor_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_vendors');
    }
};
