<?php

use App\Models\Hotel;

it('stores no gst setting without null tax percentages', function () {
    $hotel = Hotel::create([
        'hotel_code' => 'HMS-001',
        'hotel_name' => 'Test Hotel',
        'phone' => '9999999999',
        'currency' => 'INR',
        'invoice_prefix' => 'INV',
        'booking_prefix' => 'BK',
        'booking_sequence' => 1000,
        'status' => true,
        'is_gst_applicable' => true,
        'gst_number' => '27ABCDE1234F1Z5',
        'pan_number' => 'ABCDE1234F',
        'cgst_percentage' => 9,
        'sgst_percentage' => 9,
        'igst_percentage' => 18,
        'invoice_tax_type' => 'exclusive',
    ]);

    $response = $this->post(route('hotel.setup.tax-billing', $hotel->id), [
        'is_gst_applicable' => '0',
        'invoice_tax_type' => 'exclusive',
    ]);

    $response->assertOk();
    $response->assertJsonPath('status', true);

    $hotel->refresh();

    expect($hotel->is_gst_applicable)->toBeFalse()
        ->and($hotel->cgst_percentage)->not->toBeNull()
        ->and($hotel->sgst_percentage)->not->toBeNull()
        ->and($hotel->igst_percentage)->not->toBeNull();
});
