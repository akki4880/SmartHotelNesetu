<?php

use App\Models\Hotel;
use App\Models\InvoiceTemplate;
use App\Models\User;

test('hotel can save a free invoice template selection', function () {
    $hotel = Hotel::create([
        'hotel_code' => 'HMS-TEST-001',
        'hotel_name' => 'Test Hotel',
        'phone' => '9999999999',
        'status' => true,
    ]);

    $user = User::factory()->create([
        'hotel_id' => $hotel->id,
        'user_role_id' => 1,
    ]);

    $template = InvoiceTemplate::create([
        'name' => 'Free Classic',
        'slug' => 'free-classic',
        'view_file' => 'classic',
        'type' => 'free',
        'price' => 0,
        'is_default' => true,
        'status' => 1,
    ]);

    $response = $this->actingAs($user)
        ->from('/invoice-management/setup')
        ->post('/invoice-management/invoice-templates/select', [
            'template_id' => $template->id,
        ]);

    $response->assertRedirect('/invoice-management/setup');
    $response->assertSessionHas('success');

    $hotel->refresh();
    expect($hotel->invoice_template_id)->toBe($template->id);
});

test('hotel cannot select a paid invoice template', function () {
    $hotel = Hotel::create([
        'hotel_code' => 'HMS-TEST-002',
        'hotel_name' => 'Test Hotel 2',
        'phone' => '8888888888',
        'status' => true,
    ]);

    $user = User::factory()->create([
        'hotel_id' => $hotel->id,
        'user_role_id' => 1,
    ]);

    $template = InvoiceTemplate::create([
        'name' => 'Premium Template',
        'slug' => 'premium-template',
        'view_file' => 'premium',
        'type' => 'paid',
        'price' => 499,
        'is_default' => false,
        'status' => 1,
    ]);

    $response = $this->actingAs($user)
        ->from('/invoice-management/setup')
        ->post('/invoice-management/invoice-templates/select', [
            'template_id' => $template->id,
        ]);

    $response->assertRedirect('/invoice-management/setup');
    $response->assertSessionHas('error', 'This is a paid template. Please contact Smart Hotel to unlock it.');

    $hotel->refresh();
    expect($hotel->invoice_template_id)->toBeNull();
});
