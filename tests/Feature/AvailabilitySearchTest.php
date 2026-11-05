<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\HourlyRate;
use App\Models\RoomDistribution;
use App\Models\RoomNumber;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilitySearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_hourly_availability_excludes_booked_rooms(): void
    {
        $hotel = Hotel::create([
            'hotel_code' => 'TEST001',
            'hotel_name' => 'Test Hotel',
            'phone' => '9999999999',
            'currency' => 'INR',
            'invoice_prefix' => 'INV',
            'is_gst_applicable' => true,
            'cgst_percentage' => 9,
            'sgst_percentage' => 9,
            'igst_percentage' => 0,
            'invoice_tax_type' => 'exclusive',
            'booking_sequence' => 1000,
            'booking_prefix' => 'BK',
            'status' => true,
        ]);

        $user = User::factory()->create([
            'hotel_id' => $hotel->id,
        ]);
        $this->actingAs($user);

        $guest = Guest::create([
            'hotel_id' => $hotel->id,
            'full_name' => 'Alice Guest',
            'mobile' => '9999988888',
            'email' => 'alice@example.com',
            'address' => 'Test address',
            'dob' => '1990-01-01',
        ]);

        $roomType = RoomType::create([
            'hotel_id' => $hotel->id,
            'name' => 'Deluxe',
            'default_base_guests' => 2,
            'default_max_extra_guests' => 1,
            'status' => true,
        ]);

        HourlyRate::create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'base_price' => 500,
            'extra_hour_price' => 100,
            'base_hours' => 2,
            'base_guest_count' => 2,
            'max_extra_guests' => 1,
            'status' => true,
        ]);

        foreach (['101', '102', '103'] as $roomNumber) {
            RoomNumber::create([
                'hotel_id' => $hotel->id,
                'room_type_id' => $roomType->id,
                'room_number' => $roomNumber,
                'status' => 0,
            ]);
        }

        $booking = Booking::create([
            'hotel_id' => $hotel->id,
            'guest_id' => $guest->id,
            'booking_type' => 'hourly',
            'check_in_date' => '2026-10-29',
            'check_out_date' => '2026-10-29',
            'status' => 'confirmed',
            'no_of_rooms' => 1,
            'base_amount' => 0,
            'discount' => 0,
            'total_amount' => 0,
        ]);

        RoomDistribution::create([
            'hotel_id' => $hotel->id,
            'booking_id' => $booking->id,
            'room_number_id' => RoomNumber::first()->id,
            'room_type_id' => $roomType->id,
            'allocation_date' => '2026-10-29',
            'is_room_assigned' => true,
        ]);

        $response = $this->get('/availability/search?check_in_date=2026-10-29&check_out_date=2026-10-29&no_of_guests=2&no_of_rooms=1&rate_type=hourly&check_in_time=10:00&check_out_time=12:00');

        $response->assertOk();
        $response->assertJsonPath('status', true);
        $response->assertJsonPath('data.0.available', 2);
    }

    public function test_dirty_or_unavailable_rooms_still_appear_in_results(): void
    {
        $hotel = Hotel::create([
            'hotel_code' => 'TEST002',
            'hotel_name' => 'Test Hotel 2',
            'phone' => '9999999998',
            'currency' => 'INR',
            'invoice_prefix' => 'INV',
            'is_gst_applicable' => true,
            'cgst_percentage' => 9,
            'sgst_percentage' => 9,
            'igst_percentage' => 0,
            'invoice_tax_type' => 'exclusive',
            'booking_sequence' => 1001,
            'booking_prefix' => 'BK',
            'status' => true,
        ]);

        $user = User::factory()->create([
            'hotel_id' => $hotel->id,
        ]);
        $this->actingAs($user);

        $roomType = RoomType::create([
            'hotel_id' => $hotel->id,
            'name' => 'Standard',
            'default_base_guests' => 2,
            'default_max_extra_guests' => 1,
            'status' => true,
        ]);

        $roomRate = $roomType->roomRate()->create([
            'hotel_id' => $hotel->id,
            'base_guest_count' => 2,
            'max_extra_guests' => 1,
            'extra_guest_price' => 200,
            'status' => true,
            'rate' => 1500,
        ]);

        $roomRate->rates()->create([
            'hotel_id' => $hotel->id,
            'date' => '2026-10-29',
            'price' => 1500,
            'type' => 'normal',
        ]);

        $roomRate->rates()->create([
            'hotel_id' => $hotel->id,
            'date' => '2026-10-30',
            'price' => 1500,
            'type' => 'normal',
        ]);

        RoomNumber::create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'room_number' => '201',
            'status' => 3,
        ]);

        $response = $this->get('/availability/search?check_in_date=2026-10-29&check_out_date=2026-10-30&no_of_guests=2&no_of_rooms=1&rate_type=room');

        $response->assertOk();
        $response->assertJsonPath('status', true);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.available', 0);
        $response->assertJsonPath('data.0.dirty_rooms', 1);
    }
}
