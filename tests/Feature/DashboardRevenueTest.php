<?php

use App\Http\Controllers\DashboardController;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\User;
use App\Models\UserRole;

it('provides live revenue analytics for the hotel dashboard', function () {
    $role = UserRole::create([
        'id' => 3,
        'role_name' => 'hotel_admin',
    ]);

    $hotel = Hotel::create([
        'hotel_code' => 'HMS-TEST-01',
        'hotel_name' => 'Test Hotel',
        'phone' => '9999999999',
        'city' => 'Bengaluru',
        'state' => 'Karnataka',
        'country' => 'India',
        'currency' => 'INR',
        'status' => true,
    ]);

    $user = User::create([
        'name' => 'Hotel Admin',
        'email' => 'admin@testhotel.com',
        'password' => bcrypt('password'),
        'user_role_id' => $role->id,
        'hotel_id' => $hotel->id,
    ]);

    $guest = Guest::create([
        'hotel_id' => $hotel->id,
        'full_name' => 'Test Guest',
        'mobile' => '9876543210',
        'email' => 'guest@testhotel.com',
        'address' => 'Bengaluru',
        'dob' => '1990-01-01',
        'country_id' => 1,
        'state_id' => 1,
    ]);

    Booking::create([
        'hotel_id' => $hotel->id,
        'guest_id' => $guest->id,
        'booking_type' => 'regular',
        'check_in_date' => now()->startOfMonth()->toDateString(),
        'check_out_date' => now()->startOfMonth()->addDays(2)->toDateString(),
        'no_of_rooms' => 1,
        'base_amount' => 2000,
        'discount' => 0,
        'cgst' => 0,
        'sgst' => 0,
        'igst' => 0,
        'total_amount' => 2500,
        'status' => 'confirmed',
        'created_at' => now()->startOfMonth()->toDateTimeString(),
        'updated_at' => now()->toDateTimeString(),
    ]);

    Booking::create([
        'hotel_id' => $hotel->id,
        'guest_id' => $guest->id,
        'booking_type' => 'regular',
        'check_in_date' => now()->subMonth()->startOfMonth()->toDateString(),
        'check_out_date' => now()->subMonth()->startOfMonth()->addDays(2)->toDateString(),
        'no_of_rooms' => 1,
        'base_amount' => 3000,
        'discount' => 0,
        'cgst' => 0,
        'sgst' => 0,
        'igst' => 0,
        'total_amount' => 3500,
        'status' => 'confirmed',
        'created_at' => now()->subMonth()->startOfMonth()->toDateTimeString(),
        'updated_at' => now()->toDateTimeString(),
    ]);

    $this->actingAs($user);

    $view = app(DashboardController::class)->index();

    expect($view->getData())->toHaveKey('revenueData')
        ->and((float) $view->getData()['revenueData']['current_month_total'])->toBe(2500.0)
        ->and((float) $view->getData()['revenueData']['previous_month_total'])->toBe(3500.0)
        ->and(count($view->getData()['revenueData']['monthly']))->toBe(12);
});
