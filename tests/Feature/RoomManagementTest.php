<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingGuest;
use App\Models\BookingRoomTypeChange;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\RoomDistribution;
use App\Models\RoomNumber;
use App\Models\RoomRate;
use App\Models\RoomType;
use App\Models\State;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RoomManagementTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $hotel;
    private $roomType;
    private $roomRate;
    private $roomType2;
    private $roomRate2;
    private $countryId;
    private $stateId;

    protected function setUp(): void
    {
        parent::setUp();

        // Create country and state for guest records
        try {
            $countryData = DB::table('countries')->first();
            if (!$countryData) {
                DB::table('countries')->insert([
                    'country_name' => 'India',
                    'country_code' => 'IN',
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->countryId = DB::table('countries')->first()->id;
            
            $stateData = DB::table('states')->first();
            if (!$stateData) {
                DB::table('states')->insert([
                    'state_name' => 'Delhi',
                    'country_code' => 'IN',
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->stateId = DB::table('states')->first()->id;
        } catch (\Exception $e) {
            $this->countryId = 1;
            $this->stateId = 1;
        }

        // Create user_role if it doesn't exist
        $roleData = DB::table('user_roles')->first();
        if (!$roleData) {
            DB::table('user_roles')->insert([
                'role_name' => 'Admin',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $roleId = DB::table('user_roles')->first()->id;

        // Create hotel
        $this->hotel = Hotel::create([
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

        // Create a test user with the proper role_id
        $this->user = User::factory()->create([
            'hotel_id' => $this->hotel->id,
            'user_role_id' => $roleId,
        ]);

        // Create room type 1
        $this->roomType = RoomType::create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Standard',
            'default_base_guests' => 2,
            'default_max_extra_guests' => 1,
            'status' => true,
        ]);

        $this->roomRate = RoomRate::create([
            'room_type_id' => $this->roomType->id,
            'hotel_id' => $this->hotel->id,
            'base_price' => 2000,
            'weekend_price' => 2500,
            'extra_guest_price' => 300,
            'base_guest_count' => 2,
            'max_extra_guests' => 1,
            'status' => true,
        ]);

        // Create room type 2 (for type change tests)
        $this->roomType2 = RoomType::create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Deluxe',
            'default_base_guests' => 2,
            'default_max_extra_guests' => 2,
            'status' => true,
        ]);

        $this->roomRate2 = RoomRate::create([
            'room_type_id' => $this->roomType2->id,
            'hotel_id' => $this->hotel->id,
            'base_price' => 3000,
            'weekend_price' => 3500,
            'extra_guest_price' => 400,
            'base_guest_count' => 2,
            'max_extra_guests' => 2,
            'status' => true,
        ]);

        // Create 5 Standard rooms
        for ($i = 1; $i <= 5; $i++) {
            RoomNumber::create([
                'hotel_id' => $this->hotel->id,
                'room_type_id' => $this->roomType->id,
                'room_number' => '10' . $i,
                'status' => 0,
            ]);
        }

        // Create 5 Deluxe rooms
        for ($i = 1; $i <= 5; $i++) {
            RoomNumber::create([
                'hotel_id' => $this->hotel->id,
                'room_type_id' => $this->roomType2->id,
                'room_number' => '20' . $i,
                'status' => 0,
            ]);
        }

        $this->actingAs($this->user);
    }

    public function test_generate_invoice_page_shows_checkout_action_for_checked_in_booking()
    {
        $checkIn = Carbon::parse('2025-01-27');
        $checkOut = Carbon::parse('2025-01-30');

        $bookingData = [
            'hotel_id' => $this->hotel->id,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'room_type_id' => $this->roomType->id,
            'no_of_rooms' => 1,
            'no_of_guests' => 2,
            'guests' => [
                [
                    'name' => 'Jane Doe',
                    'mobile' => '9888888888',
                    'email' => 'jane@example.com',
                    'country_id' => $this->countryId,
                    'state_id' => $this->stateId,
                    'is_primary' => true,
                ]
            ],
            'additional_services' => [],
            'discount_amount' => 0,
            'discount_type' => null,
            'payment' => [],
            'agent_id' => null,
        ];

        $booking = \App\Services\BookingService::createSingleBooking($bookingData);
        $bookingModel = $booking['booking'];

        $bookingModel->update([
            'status' => 'checked_in',
            'actual_check_in_at' => now()->subHour(),
            'actual_check_out_at' => null,
        ]);

        $response = $this->get('/invoice-management/generate-invoice');

        $response->assertOk();
        $response->assertSee('checkout-btn');
    }

    public function test_checked_out_booking_hides_room_action_buttons()
    {
        $checkIn = Carbon::parse('2025-01-27');
        $checkOut = Carbon::parse('2025-01-30');

        $bookingData = [
            'hotel_id' => $this->hotel->id,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'room_type_id' => $this->roomType->id,
            'no_of_rooms' => 1,
            'no_of_guests' => 2,
            'guests' => [[
                'name' => 'Jack Doe',
                'mobile' => '9777777777',
                'email' => 'jack@example.com',
                'country_id' => $this->countryId,
                'state_id' => $this->stateId,
                'is_primary' => true,
            ]],
            'additional_services' => [],
            'discount_amount' => 0,
            'discount_type' => null,
            'payment' => [],
            'agent_id' => null,
        ];

        $booking = \App\Services\BookingService::createSingleBooking($bookingData);
        $booking['booking']->update([
            'status' => 'checked_out',
            'actual_check_in_at' => now()->subHour(),
            'actual_check_out_at' => now(),
        ]);

        $response = $this->get('/bookings');

        $response->assertOk();
        $response->assertDontSee('changeRoomBtn');
        $response->assertDontSee('upgradeRoomBtn');
    }

    /**
     * Test 1: Check-in with valid room selection
     */
    public function test_checkin_with_valid_room()
    {
        $checkIn = Carbon::parse('2025-01-27'); // Monday
        $checkOut = Carbon::parse('2025-01-30'); // Thursday

        $bookingData = [
            'hotel_id' => $this->hotel->id,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'room_type_id' => $this->roomType->id,
            'no_of_rooms' => 1,
            'no_of_guests' => 2,
            'guests' => [
                [
                    'name' => 'John Doe',
                    'mobile' => '9999999999',
                    'email' => 'john@example.com',
                    'country_id' => $this->countryId,
                    'state_id' => $this->stateId,
                    'is_primary' => true,
                ]
            ],
            'additional_services' => [],
            'discount_amount' => 0,
            'discount_type' => null,
            'payment' => [],
            'agent_id' => null,
        ];

        $booking = \App\Services\BookingService::createSingleBooking($bookingData);
        $bookingModel = $booking['booking'];

        // Select first available room for check-in
        $room = RoomNumber::where('hotel_id', $this->hotel->id)
            ->where('room_type_id', $this->roomType->id)
            ->first();

        $response = $this->post(route('booking.checkin'), [
            'booking_id' => $bookingModel->id,
            'rooms' => [$room->id]
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', true);

        // Verify booking was checked in
        $bookingModel->refresh();
        $this->assertNotNull($bookingModel->actual_check_in_at);
        $this->assertEquals('checked_in', $bookingModel->status);

        // Verify room is marked as occupied
        $room->refresh();
        $this->assertEquals(1, $room->status);
    }

    /**
     * Test 2: Check-in validates room type matches
     */
    public function test_checkin_rejects_wrong_room_type()
    {
        $checkIn = Carbon::parse('2025-01-27');
        $checkOut = Carbon::parse('2025-01-30');

        $bookingData = [
            'hotel_id' => $this->hotel->id,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'room_type_id' => $this->roomType->id, // Standard
            'no_of_rooms' => 1,
            'no_of_guests' => 2,
            'guests' => [],
            'additional_services' => [],
            'discount_amount' => 0,
            'discount_type' => null,
            'payment' => [],
            'agent_id' => null,
        ];

        $booking = \App\Services\BookingService::createSingleBooking($bookingData);
        $bookingModel = $booking['booking'];

        // Select Deluxe room (wrong type)
        $wrongRoom = RoomNumber::where('hotel_id', $this->hotel->id)
            ->where('room_type_id', $this->roomType2->id)
            ->first();

        $response = $this->post(route('booking.checkin'), [
            'booking_id' => $bookingModel->id,
            'rooms' => [$wrongRoom->id]
        ]);

        $response->assertStatus(500);
        $response->assertJsonPath('status', false);
        $response->assertJsonPath('error', 'Check-in failed');
        // Verify the specific error message contains the room type issue
        $this->assertStringContainsString('not of type', $response->json('message'));
    }

    /**
     * Test 3: Check-out sets rooms to 'dirty' status (3)
     */
    public function test_checkout_sets_room_to_dirty()
    {
        $checkIn = Carbon::parse('2025-01-27');
        $checkOut = Carbon::parse('2025-01-30');

        $bookingData = [
            'hotel_id' => $this->hotel->id,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'room_type_id' => $this->roomType->id,
            'no_of_rooms' => 1,
            'no_of_guests' => 2,
            'guests' => [],
            'additional_services' => [],
            'discount_amount' => 0,
            'discount_type' => null,
            'payment' => [],
            'agent_id' => null,
        ];

        $booking = \App\Services\BookingService::createSingleBooking($bookingData);
        $bookingModel = $booking['booking'];

        // Check-in
        $room = RoomNumber::where('hotel_id', $this->hotel->id)
            ->where('room_type_id', $this->roomType->id)
            ->first();

        $this->post(route('booking.checkin'), [
            'booking_id' => $bookingModel->id,
            'rooms' => [$room->id]
        ]);

        // Check-out
        $response = $this->post(route('booking.checkout', $bookingModel->id));

        $response->assertStatus(200);
        $response->assertJsonPath('status', true);

        // Verify room is marked as dirty (3) not available (0)
        $room->refresh();
        $this->assertEquals(3, $room->status);
    }

    /**
     * Test 4: Room change before check-in
     */
    public function test_room_change_before_checkin()
    {
        $checkIn = Carbon::parse('2025-01-27');
        $checkOut = Carbon::parse('2025-01-30');

        $bookingData = [
            'hotel_id' => $this->hotel->id,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'room_type_id' => $this->roomType->id,
            'no_of_rooms' => 1,
            'no_of_guests' => 2,
            'guests' => [],
            'additional_services' => [],
            'discount_amount' => 0,
            'discount_type' => null,
            'payment' => [],
            'agent_id' => null,
        ];

        $booking = \App\Services\BookingService::createSingleBooking($bookingData);
        $bookingModel = $booking['booking'];

        // Get two different rooms
        $rooms = RoomNumber::where('hotel_id', $this->hotel->id)
            ->where('room_type_id', $this->roomType->id)
            ->limit(2)
            ->get();

        $oldRoom = $rooms[0];
        $newRoom = $rooms[1];

        // Change room
        $response = $this->post(route('booking.changeRoom', $bookingModel->id), [
            'new_room_id' => $newRoom->id,
            'reason' => 'Guest preference'
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // Verify allocation was updated
        $allocation = RoomDistribution::where('booking_id', $bookingModel->id)->first();
        $this->assertEquals($newRoom->id, $allocation->room_number_id);
    }

    /**
     * Test 5: Room type change mid-stay
     */
    public function test_room_type_change_mid_stay()
    {
        // 3-night stay: Jan 27-30
        $checkIn = Carbon::parse('2025-01-27'); // Monday
        $checkOut = Carbon::parse('2025-01-30'); // Thursday
        $changeDate = Carbon::parse('2025-01-29'); // Wednesday (3rd night)

        $bookingData = [
            'hotel_id' => $this->hotel->id,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'room_type_id' => $this->roomType->id, // Standard
            'no_of_rooms' => 1,
            'no_of_guests' => 2,
            'guests' => [],
            'additional_services' => [],
            'discount_amount' => 0,
            'discount_type' => null,
            'payment' => [],
            'agent_id' => null,
        ];

        $booking = \App\Services\BookingService::createSingleBooking($bookingData);
        $bookingModel = $booking['booking'];

        // Get initial allocation
        $initialCount = RoomDistribution::where('booking_id', $bookingModel->id)->count();
        $this->assertEquals(3, $initialCount); // 3 nights

        // Change room type from Jan 29 onwards
        $response = $this->post(route('booking.changeRoomType', $bookingModel->id), [
            'new_room_type_id' => $this->roomType2->id,
            'effective_date' => $changeDate->toDateString(),
            'reason' => 'Guest upgrade'
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // Verify change was recorded
        $changeRecord = BookingRoomTypeChange::where('booking_id', $bookingModel->id)->first();
        $this->assertNotNull($changeRecord);
        $this->assertEquals($this->roomType->id, $changeRecord->old_room_type_id);
        $this->assertEquals($this->roomType2->id, $changeRecord->new_room_type_id);
        $this->assertEquals($changeDate->toDateString(), $changeRecord->effective_from->toDateString());

        // Verify allocations: Jan 27-28 should be Standard, Jan 29 should be Deluxe
        $jan27 = RoomDistribution::where('booking_id', $bookingModel->id)
            ->where('allocation_date', '2025-01-27')
            ->first();
        $this->assertEquals($this->roomType->id, $jan27->room_type_id);

        $jan29 = RoomDistribution::where('booking_id', $bookingModel->id)
            ->where('allocation_date', '2025-01-29')
            ->first();
        $this->assertEquals($this->roomType2->id, $jan29->room_type_id);
    }

    /**
     * Test 6: Get available replacement rooms
     */
    public function test_get_available_replacement_rooms()
    {
        $checkIn = Carbon::parse('2025-01-27');
        $checkOut = Carbon::parse('2025-01-30');

        $bookingData = [
            'hotel_id' => $this->hotel->id,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'room_type_id' => $this->roomType->id,
            'no_of_rooms' => 1,
            'no_of_guests' => 2,
            'guests' => [],
            'additional_services' => [],
            'discount_amount' => 0,
            'discount_type' => null,
            'payment' => [],
            'agent_id' => null,
        ];

        $booking = \App\Services\BookingService::createSingleBooking($bookingData);
        $bookingModel = $booking['booking'];

        // Get replacement rooms
        $response = $this->get(route('booking.replacementRooms', $bookingModel->id));

        $response->assertStatus(200);
        $response->assertJsonPath('status', true);
        $response->assertJsonPath('available_rooms', function ($rooms) {
            return count($rooms) > 0;
        });
    }

    /**
     * Test 7: Check-in rejects already checked-in booking
     */
    public function test_checkin_rejects_already_checkedin()
    {
        $checkIn = Carbon::parse('2025-01-27');
        $checkOut = Carbon::parse('2025-01-30');

        $bookingData = [
            'hotel_id' => $this->hotel->id,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'room_type_id' => $this->roomType->id,
            'no_of_rooms' => 1,
            'no_of_guests' => 2,
            'guests' => [],
            'additional_services' => [],
            'discount_amount' => 0,
            'discount_type' => null,
            'payment' => [],
            'agent_id' => null,
        ];

        $booking = \App\Services\BookingService::createSingleBooking($bookingData);
        $bookingModel = $booking['booking'];

        $room = RoomNumber::where('hotel_id', $this->hotel->id)
            ->where('room_type_id', $this->roomType->id)
            ->first();

        // First check-in
        $this->post(route('booking.checkin'), [
            'booking_id' => $bookingModel->id,
            'rooms' => [$room->id]
        ]);

        // Second check-in should fail
        $response = $this->post(route('booking.checkin'), [
            'booking_id' => $bookingModel->id,
            'rooms' => [$room->id]
        ]);

        $response->assertStatus(500);
        $response->assertJsonPath('status', false);
        $response->assertJsonPath('error', 'Check-in failed');
        $this->assertStringContainsString('already checked in', $response->json('message'));
    }

    /**
     * Test 8: Check-out validates guest is checked in
     */
    public function test_checkout_validates_checkin()
    {
        $checkIn = Carbon::parse('2025-01-27');
        $checkOut = Carbon::parse('2025-01-30');

        $bookingData = [
            'hotel_id' => $this->hotel->id,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'room_type_id' => $this->roomType->id,
            'no_of_rooms' => 1,
            'no_of_guests' => 2,
            'guests' => [],
            'additional_services' => [],
            'discount_amount' => 0,
            'discount_type' => null,
            'payment' => [],
            'agent_id' => null,
        ];

        $booking = \App\Services\BookingService::createSingleBooking($bookingData);
        $bookingModel = $booking['booking'];

        // Try to check-out without checking in
        $response = $this->post(route('booking.checkout', $bookingModel->id));

        $response->assertStatus(500);
        $response->assertJsonPath('status', false);
        $response->assertJsonPath('error', 'Check-out failed');
        $this->assertStringContainsString('not checked in', $response->json('message'));
    }
}
