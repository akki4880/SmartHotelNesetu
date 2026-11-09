<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Hotel;
use App\Models\RoomType;
use App\Models\RoomNumber;
use App\Models\RoomRate;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\GuestIdProof;
use App\Models\User;
use App\Services\AvailabilityService;
use App\Services\PricingService;
use App\Services\BookingService;
use App\Services\GuestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected $hotel;
    protected $roomType;
    protected $roomRate;

    protected function setUp(): void
    {
        parent::setUp();

        // Create country manually (required for guests) - insert into countries table if it exists
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
            
            // Create state
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
            // If countries table doesn't exist, just use fake IDs - the migration should handle it
            $this->countryId = 1;
            $this->stateId = 1;
        }

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

        // Create room type
        $this->roomType = RoomType::create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Standard',
            'default_base_guests' => 2,
            'default_max_extra_guests' => 2,
            'status' => true,
        ]);

        // Create room rate
        $this->roomRate = RoomRate::create([
            'room_type_id' => $this->roomType->id,
            'hotel_id' => $this->hotel->id,
            'base_price' => 2000,
            'weekend_price' => 2500,
            'extra_guest_price' => 500,
            'base_guest_count' => 2,
            'max_extra_guests' => 2,
            'status' => true,
        ]);

        // Create 5 rooms
        for ($i = 1; $i <= 5; $i++) {
            RoomNumber::create([
                'hotel_id' => $this->hotel->id,
                'room_type_id' => $this->roomType->id,
                'room_number' => '10' . $i,
                'status' => 0, // Available
            ]);
        }
    }

    /**
     * Test 1: Successful single booking creation
     */
    public function test_successful_single_booking_creation()
    {
        $checkIn = Carbon::today()->addDays(2);
        $checkOut = $checkIn->copy()->addDays(3);

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
            'remark' => 'Test booking',
        ];

        $result = BookingService::createSingleBooking($bookingData);

        $this->assertTrue($result['success']);
        $this->assertNotNull($result['booking']->id);
        $this->assertEquals('BK-01000', $result['booking']->booking_code);
        $this->assertEquals('confirmed', $result['booking']->status);
        $this->assertEquals(1, $result['booking']->no_of_rooms);
        $this->assertGreaterThan(0, $result['booking']->total_amount);
    }

    public function test_guest_already_checked_in_cannot_create_new_booking()
    {
        $guest = Guest::create([
            'hotel_id' => $this->hotel->id,
            'full_name' => 'Checked In Guest',
            'mobile' => '7777777777',
            'email' => 'checkedin@example.com',
            'country_id' => $this->countryId,
            'state_id' => $this->stateId,
        ]);

        $booking = Booking::create([
            'hotel_id' => $this->hotel->id,
            'booking_code' => 'BK-10001',
            'booking_number' => 1001,
            'booking_type' => 'regular',
            'check_in_date' => Carbon::today()->toDateString(),
            'check_out_date' => Carbon::today()->addDay()->toDateString(),
            'no_of_rooms' => 1,
            'no_of_guests' => 2,
            'base_amount' => 2000,
            'discount' => 0,
            'cgst' => 0,
            'sgst' => 0,
            'igst' => 0,
            'total_amount' => 2000,
            'status' => 'checked_in',
            'actual_check_in_at' => now(),
            'actual_check_out_at' => null,
            'remark' => 'active stay',
        ]);

        $booking->bookingGuests()->create([
            'guest_id' => $guest->id,
            'full_name' => $guest->full_name,
            'mobile' => $guest->mobile,
            'email' => $guest->email,
            'is_primary_guest' => true,
        ]);

        $bookingData = [
            'hotel_id' => $this->hotel->id,
            'check_in_date' => Carbon::today()->addDays(2)->toDateString(),
            'check_out_date' => Carbon::today()->addDays(3)->toDateString(),
            'room_type_id' => $this->roomType->id,
            'no_of_rooms' => 1,
            'no_of_guests' => 2,
            'guests' => [
                [
                    'name' => 'Checked In Guest',
                    'mobile' => '7777777777',
                    'email' => 'checkedin@example.com',
                    'country_id' => $this->countryId,
                    'state_id' => $this->stateId,
                    'is_primary' => true,
                ]
            ],
            'additional_services' => [],
            'discount_amount' => 0,
            'discount_type' => null,
            'payment' => [],
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        BookingService::createSingleBooking($bookingData);
    }

    public function test_store_persists_payment_details_sent_as_flat_fields()
    {
        $user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'hotel_id' => $this->hotel->id,
        ]);

        $checkIn = Carbon::today()->addDays(2);
        $checkOut = $checkIn->copy()->addDays(2);

        $request = new \Illuminate\Http\Request();
        $request->setMethod('POST');
        $request->replace([
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'room_type_id' => $this->roomType->id,
            'no_of_rooms' => 1,
            'no_of_guests' => 2,
            'rate_type' => 'room',
            'guests' => [[
                'name' => 'Payment Guest',
                'mobile' => '9876543211',
                'email' => 'payment@example.com',
                'address' => 'Test Address',
                'country_id' => $this->countryId,
                'state_id' => $this->stateId,
                'is_primary' => true,
            ]],
            'agent_id' => null,
            'discount_amount' => 0,
            'discount_type' => null,
            'additional_services' => [],
            'payment_method' => 'cash',
            'amount_paid' => '500',
            'reference' => 'REF-1001',
            'paid_at' => now()->toDateTimeString(),
            'remark' => 'Payment test',
            'primary_guest' => 0,
        ]);

        $this->actingAs($user);

        $response = (new \App\Http\Controllers\BookingController())->store($request);

        $this->assertSame(200, $response->getStatusCode());
        $payload = $response->getData(true);
        $this->assertTrue($payload['status'] ?? false);

        $booking = Booking::query()->latest()->first();
        $this->assertNotNull($booking);
        $this->assertNotEmpty($booking->payments()->get()->toArray());
        $this->assertEquals('REF-1001', $booking->payments()->first()->transaction_reference);
        $this->assertEquals('cash', $booking->payments()->first()->method);
    }

    public function test_reused_guest_removes_deleted_id_proofs()
    {
        $guest = Guest::create([
            'hotel_id' => $this->hotel->id,
            'full_name' => 'John Doe',
            'mobile' => '9999999999',
            'email' => 'john@example.com',
            'address' => 'Test Address',
            'dob' => '1990-01-01',
            'country_id' => $this->countryId,
            'state_id' => $this->stateId,
        ]);

        $keepProof = GuestIdProof::create([
            'guest_id' => $guest->id,
            'id_type' => 'Aadhaar',
            'id_number' => '1111',
            'id_file_path' => null,
        ]);

        $removeProof = GuestIdProof::create([
            'guest_id' => $guest->id,
            'id_type' => 'Passport',
            'id_number' => '2222',
            'id_file_path' => null,
        ]);

        GuestService::createOrUpdateGuest([
            'mobile' => $guest->mobile,
            'name' => $guest->full_name,
            'email' => $guest->email,
            'address' => $guest->address,
            'dob' => $guest->dob,
            'country_id' => $guest->country_id,
            'state_id' => $guest->state_id,
            'id_proofs' => [[
                'proof_id' => $keepProof->id,
                'id_type' => $keepProof->id_type,
                'id_number' => $keepProof->id_number,
            ]],
            'deleted_proof_ids' => [$removeProof->id],
        ], $this->hotel->id);

        $this->assertSame([$keepProof->id], $guest->fresh()->idProofs()->pluck('id')->all());
    }

    public function test_invoice_data_includes_additional_services()
    {
        $checkIn = Carbon::today()->addDays(2);
        $checkOut = $checkIn->copy()->addDays(2);

        $service = \App\Models\AdditionalService::create([
            'hotel_id' => $this->hotel->id,
            'service_name' => 'Spa',
            'cost' => 500,
            'status' => true,
        ]);

        $booking = BookingService::createSingleBooking([
            'hotel_id' => $this->hotel->id,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'room_type_id' => $this->roomType->id,
            'no_of_rooms' => 1,
            'no_of_guests' => 1,
            'guests' => [[
                'name' => 'Spa Guest',
                'mobile' => '1112223333',
                'email' => 'spa@example.com',
                'country_id' => $this->countryId,
                'state_id' => $this->stateId,
                'is_primary' => true,
            ]],
            'additional_services' => [[
                'service_id' => $service->id,
                'quantity' => 1,
            ]],
            'discount_amount' => 0,
            'discount_type' => null,
            'payment' => [],
            'agent_id' => null,
            'remark' => 'Spa booking',
        ])['booking'];

        $response = (new \App\Http\Controllers\InvoiceController())->getInvoiceData($booking->id);
        $payload = $response->getData(true);

        $this->assertSame(200, $response->status());
        $this->assertNotEmpty($payload['additional_services']);
        $this->assertSame('Spa', $payload['additional_services'][0]['name']);
        $this->assertSame('500.00', number_format((float) $payload['additional_services'][0]['amount'], 2, '.', ''));
    }

    public function test_booking_update_updates_basic_details_and_services()
    {
        $checkIn = Carbon::today()->addDays(2);
        $checkOut = $checkIn->copy()->addDays(2);

        $booking = BookingService::createSingleBooking([
            'hotel_id' => $this->hotel->id,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'room_type_id' => $this->roomType->id,
            'no_of_rooms' => 1,
            'no_of_guests' => 1,
            'guests' => [[
                'name' => 'Original Guest',
                'mobile' => '9876543210',
                'email' => 'original@example.com',
                'country_id' => $this->countryId,
                'state_id' => $this->stateId,
                'is_primary' => true,
            ]],
            'additional_services' => [],
            'discount_amount' => 0,
            'discount_type' => null,
            'payment' => [],
            'agent_id' => null,
            'remark' => 'Old remark',
        ])['booking'];

        $service = \App\Models\AdditionalService::create([
            'hotel_id' => $this->hotel->id,
            'service_name' => 'Breakfast',
            'cost' => 250,
            'status' => true,
        ]);

        $request = new \Illuminate\Http\Request();
        $request->setMethod('PUT');
        $request->replace([
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'no_of_guests' => 2,
            'no_of_rooms' => 1,
            'remark' => 'Updated remark',
            'agent_id' => null,
            'services' => [$service->id],
            'service_qty' => [$service->id => 2],
            'payment_method' => 'cash',
            'paid_at' => now()->toDateTimeString(),
            'reference' => 'REF-100',
            'amount' => '500',
            'total_amount' => '3000',
            'primary_guest' => 0,
            'guests' => [[
                'name' => 'Updated Guest',
                'mobile' => '9998887777',
                'email' => 'updated@example.com',
                'address' => 'Updated address',
                'dob' => '1990-01-01',
                'country_id' => $this->countryId,
                'state_id' => $this->stateId,
                'is_primary' => true,
            ]],
        ]);

        $response = (new \App\Http\Controllers\BookingController())->update($request, $booking);

        $this->assertSame(200, $response->getStatusCode());
        $payload = $response->getData(true);
        $this->assertTrue($payload['status']);

        $booking->refresh();
        $this->assertEquals('Updated remark', $booking->remark);
        $this->assertEquals(2, $booking->no_of_guests);
        $this->assertEquals('3000', (string) $booking->total_amount);
        $this->assertEquals('Updated Guest', $booking->bookingGuests()->first()->full_name);
        $this->assertEquals(2, $booking->additionalServices()->first()->pivot->quantity);
    }

    /**
     * Test 2: Booking rejected when insufficient rooms available
     */
    public function test_booking_rejected_insufficient_rooms()
    {
        $checkIn = Carbon::today()->addDays(2);
        $checkOut = $checkIn->copy()->addDays(2);

        $bookingData = [
            'hotel_id' => $this->hotel->id,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'room_type_id' => $this->roomType->id,
            'no_of_rooms' => 10, // Only 5 available
            'no_of_guests' => 2,
            'guests' => [],
            'additional_services' => [],
            'discount_amount' => 0,
            'discount_type' => null,
            'payment' => [],
            'agent_id' => null,
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Only');

        BookingService::createSingleBooking($bookingData);
    }

    /**
     * Test 3: Availability checked for every night of multi-night stay
     */
    public function test_availability_checked_for_every_night()
    {
        $checkIn = Carbon::today()->addDays(2);
        $checkOut = $checkIn->copy()->addDays(3); // 3-night stay

        // Book 4 rooms for the entire period
        BookingService::createSingleBooking([
            'hotel_id' => $this->hotel->id,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'room_type_id' => $this->roomType->id,
            'no_of_rooms' => 4,
            'no_of_guests' => 2,
            'guests' => [],
            'additional_services' => [],
            'discount_amount' => 0,
            'discount_type' => null,
            'payment' => [],
            'agent_id' => null,
        ]);

        // Now only 1 room is available
        $available = AvailabilityService::getAvailableCount(
            $this->hotel->id,
            $this->roomType->id,
            $checkIn,
            $checkOut
        );

        $this->assertEquals(1, $available);

        // Attempt to book 2 rooms should fail
        $this->expectException(\Exception::class);

        BookingService::createSingleBooking([
            'hotel_id' => $this->hotel->id,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'room_type_id' => $this->roomType->id,
            'no_of_rooms' => 2,
            'no_of_guests' => 2,
            'guests' => [],
            'additional_services' => [],
            'discount_amount' => 0,
            'discount_type' => null,
            'payment' => [],
            'agent_id' => null,
        ]);
    }

    /**
     * Test 4: Back-to-back bookings (checkout same day as next checkin)
     */
    public function test_back_to_back_bookings_allowed()
    {
        $checkIn1 = Carbon::today()->addDays(2);
        $checkOut1 = $checkIn1->copy()->addDays(2);

        // First booking
        BookingService::createSingleBooking([
            'hotel_id' => $this->hotel->id,
            'check_in_date' => $checkIn1->toDateString(),
            'check_out_date' => $checkOut1->toDateString(),
            'room_type_id' => $this->roomType->id,
            'no_of_rooms' => 1,
            'no_of_guests' => 2,
            'guests' => [],
            'additional_services' => [],
            'discount_amount' => 0,
            'discount_type' => null,
            'payment' => [],
            'agent_id' => null,
        ]);

        // Second booking (check-in same as first checkout)
        $result = BookingService::createSingleBooking([
            'hotel_id' => $this->hotel->id,
            'check_in_date' => $checkOut1->toDateString(),
            'check_out_date' => $checkOut1->copy()->addDays(2)->toDateString(),
            'room_type_id' => $this->roomType->id,
            'no_of_rooms' => 1,
            'no_of_guests' => 2,
            'guests' => [],
            'additional_services' => [],
            'discount_amount' => 0,
            'discount_type' => null,
            'payment' => [],
            'agent_id' => null,
        ]);

        $this->assertTrue($result['success']);
    }

    /**
     * Test 5: Cancelled booking releases inventory
     */
    public function test_cancelled_booking_releases_inventory()
    {
        $checkIn = Carbon::today()->addDays(2);
        $checkOut = $checkIn->copy()->addDays(2);

        // Create booking
        $result = BookingService::createSingleBooking([
            'hotel_id' => $this->hotel->id,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'room_type_id' => $this->roomType->id,
            'no_of_rooms' => 4,
            'no_of_guests' => 2,
            'guests' => [],
            'additional_services' => [],
            'discount_amount' => 0,
            'discount_type' => null,
            'payment' => [],
            'agent_id' => null,
        ]);

        $bookingId = $result['booking']->id;

        // Check availability before cancel
        $availableBefore = AvailabilityService::getAvailableCount(
            $this->hotel->id,
            $this->roomType->id,
            $checkIn,
            $checkOut
        );
        $this->assertEquals(1, $availableBefore);

        // Cancel booking
        BookingService::cancelBooking($bookingId, 'Customer request');

        // Check availability after cancel
        $availableAfter = AvailabilityService::getAvailableCount(
            $this->hotel->id,
            $this->roomType->id,
            $checkIn,
            $checkOut
        );
        $this->assertEquals(5, $availableAfter);
    }

    /**
     * Test 6: Pricing calculation with taxes
     */
    public function test_pricing_calculation_with_taxes()
    {
        // Use fixed dates: Monday Jan 27, 2025 to Thursday Jan 30, 2025 (3 nights, no weekends)
        $checkIn = Carbon::parse('2025-01-27'); // Monday
        $checkOut = Carbon::parse('2025-01-30'); // Thursday (3 nights: Jan 27, 28, 29)

        $pricingResult = PricingService::calculateBookingPrice(
            $this->hotel->id,
            $this->roomType->id,
            $checkIn,
            $checkOut,
            2, // 2 guests
            1  // 1 room
        );

        // 3 nights * 2000 base = 6000
        $this->assertEquals(6000, $pricingResult['base_amount']);

        // CGST 9% + SGST 9% = 18% of 6000 = 1080
        $this->assertEquals(540, $pricingResult['cgst']); // 9% of 6000
        $this->assertEquals(540, $pricingResult['sgst']); // 9% of 6000
        $this->assertEquals(0, $pricingResult['igst']);

        $this->assertEquals(7080, $pricingResult['total_amount']);
    }

    /**
     * Test 7: Concurrent booking requests cannot reserve same room
     */
    public function test_concurrent_bookings_prevent_double_booking()
    {
        $checkIn = Carbon::today()->addDays(2);
        $checkOut = $checkIn->copy()->addDays(2);

        // Book all 5 rooms
        BookingService::createSingleBooking([
            'hotel_id' => $this->hotel->id,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'room_type_id' => $this->roomType->id,
            'no_of_rooms' => 5,
            'no_of_guests' => 2,
            'guests' => [],
            'additional_services' => [],
            'discount_amount' => 0,
            'discount_type' => null,
            'payment' => [],
            'agent_id' => null,
        ]);

        // Attempt to book 1 more room should fail
        $this->expectException(\Exception::class);

        BookingService::createSingleBooking([
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
        ]);
    }

    /**
     * Test 8: Maintenance rooms excluded from availability
     */
    public function test_maintenance_rooms_excluded()
    {
        // Mark one room as maintenance
        RoomNumber::first()->update(['status' => 2]); // 2 = Maintenance

        $checkIn = Carbon::today()->addDays(2);
        $checkOut = $checkIn->copy()->addDays(2);

        // Only 4 rooms should be available (5 - 1 in maintenance)
        $available = AvailabilityService::getAvailableCount(
            $this->hotel->id,
            $this->roomType->id,
            $checkIn,
            $checkOut
        );

        $this->assertEquals(4, $available);
    }

    /**
     * Test 9: Booking creation fails when room type capacity exceeded
     */
    public function test_booking_rejected_capacity_exceeded()
    {
        $checkIn = Carbon::today()->addDays(2);
        $checkOut = $checkIn->copy()->addDays(2);

        // Try to book with 6 guests (max capacity is 4: 2 base + 2 extra)
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('accommodate maximum');

        PricingService::calculateBookingPrice(
            $this->hotel->id,
            $this->roomType->id,
            $checkIn,
            $checkOut,
            6, // Exceeds max 4
            1
        );
    }

    /**
     * Test 10: Invoice generated on booking creation
     */
    public function test_invoice_generated_on_booking()
    {
        $checkIn = Carbon::today()->addDays(2);
        $checkOut = $checkIn->copy()->addDays(2);

        $result = BookingService::createSingleBooking([
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
        ]);

        $this->assertNotNull($result['invoice']);
        $this->assertEquals($result['booking']->id, $result['invoice']->booking_id);
        $this->assertGreaterThan(0, $result['invoice']->grand_total);
    }

    /**
     * Test 11: Successful multi-room bulk booking creation
     */
    public function test_successful_bulk_booking_creation()
    {
        // Use fixed dates: Monday Jan 27, 2025 to Thursday Jan 30, 2025 (3 nights, weekday rates)
        $checkIn = Carbon::parse('2025-01-27'); // Monday
        $checkOut = Carbon::parse('2025-01-30'); // Thursday (3 nights: Jan 27, 28, 29)

        // Create a second room type for bulk booking
        $roomType2 = RoomType::create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Deluxe',
            'default_base_guests' => 2,
            'default_max_extra_guests' => 1,
            'status' => true,
        ]);

        RoomRate::create([
            'room_type_id' => $roomType2->id,
            'hotel_id' => $this->hotel->id,
            'base_price' => 3000,
            'weekend_price' => 3500,
            'extra_guest_price' => 600,
            'base_guest_count' => 2,
            'max_extra_guests' => 1,
            'status' => true,
        ]);

        // Create 5 more rooms for the Deluxe type
        for ($i = 1; $i <= 5; $i++) {
            RoomNumber::create([
                'hotel_id' => $this->hotel->id,
                'room_type_id' => $roomType2->id,
                'room_number' => '20' . $i,
                'status' => 0,
            ]);
        }

        $bookingData = [
            'hotel_id' => $this->hotel->id,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'room_details' => [
                ['room_type_id' => $this->roomType->id, 'quantity' => 2, 'guests_per_room' => 2],
                ['room_type_id' => $roomType2->id, 'quantity' => 1, 'guests_per_room' => 2],
            ],
            'guests' => [
                [
                    'name' => 'Group Leader',
                    'mobile' => '8888888888',
                    'email' => 'leader@group.com',
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
            'remark' => 'Bulk booking for group',
        ];

        $result = BookingService::createBulkBooking($bookingData);

        // Verify booking
        $this->assertTrue($result['success']);
        $this->assertNotNull($result['booking']->id);
        $this->assertEquals('bulk', $result['booking']->booking_type);
        $this->assertEquals(3, $result['booking']->no_of_rooms); // 2 + 1
        $this->assertEquals(6, $result['booking']->no_of_guests); // (2*2) + (1*2) = 6
        
        // Verify pricing: 2 Standard @ 2000 = 4000, 1 Deluxe @ 3000 = 3000, total = 7000 (before tax)
        $baseExpected = (2000 * 2 * 3 * 1) + (3000 * 1 * 3 * 1); // Standard: 2 rooms * 3 nights; Deluxe: 1 room * 3 nights
        $this->assertEquals($baseExpected, $result['booking']->base_amount);
        
        // Invoice should exist
        $this->assertNotNull($result['invoice']);
        $this->assertGreaterThan(0, $result['invoice']->grand_total);
    }

    /**
     * Test 12: Bulk booking rejected when one room type has insufficient inventory
     */
    public function test_bulk_booking_rejected_insufficient_rooms()
    {
        $checkIn = Carbon::today()->addDays(2);
        $checkOut = $checkIn->copy()->addDays(2);

        // Create a second room type with only 2 rooms
        $roomType2 = RoomType::create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Suite',
            'default_base_guests' => 4,
            'default_max_extra_guests' => 0,
            'status' => true,
        ]);

        RoomRate::create([
            'room_type_id' => $roomType2->id,
            'hotel_id' => $this->hotel->id,
            'base_price' => 5000,
            'weekend_price' => 0,
            'extra_guest_price' => 0,
            'base_guest_count' => 4,
            'max_extra_guests' => 0,
            'status' => true,
        ]);

        // Only 2 rooms available
        for ($i = 1; $i <= 2; $i++) {
            RoomNumber::create([
                'hotel_id' => $this->hotel->id,
                'room_type_id' => $roomType2->id,
                'room_number' => '30' . $i,
                'status' => 0,
            ]);
        }

        // Try to book 2 Standard + 5 Suites (only 2 available)
        $bookingData = [
            'hotel_id' => $this->hotel->id,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'room_details' => [
                ['room_type_id' => $this->roomType->id, 'quantity' => 2, 'guests_per_room' => 2],
                ['room_type_id' => $roomType2->id, 'quantity' => 5, 'guests_per_room' => 4], // Only 2 available!
            ],
            'guests' => [],
            'additional_services' => [],
            'discount_amount' => 0,
            'discount_type' => null,
            'payment' => [],
            'agent_id' => null,
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Only');

        BookingService::createBulkBooking($bookingData);
    }

    /**
     * Test 13: Bulk booking availability validated independently per room type
     */
    public function test_bulk_booking_validates_per_room_type()
    {
        $checkIn = Carbon::today()->addDays(2);
        $checkOut = $checkIn->copy()->addDays(2);

        // Create a second room type
        $roomType2 = RoomType::create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Premium',
            'default_base_guests' => 2,
            'default_max_extra_guests' => 1,
            'status' => true,
        ]);

        RoomRate::create([
            'room_type_id' => $roomType2->id,
            'hotel_id' => $this->hotel->id,
            'base_price' => 2500,
            'weekend_price' => 0,
            'extra_guest_price' => 400,
            'base_guest_count' => 2,
            'max_extra_guests' => 1,
            'status' => true,
        ]);

        // 3 Premium rooms
        for ($i = 1; $i <= 3; $i++) {
            RoomNumber::create([
                'hotel_id' => $this->hotel->id,
                'room_type_id' => $roomType2->id,
                'room_number' => '40' . $i,
                'status' => 0,
            ]);
        }

        // Book all 5 Standard rooms
        BookingService::createSingleBooking([
            'hotel_id' => $this->hotel->id,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'room_type_id' => $this->roomType->id,
            'no_of_rooms' => 5,
            'no_of_guests' => 2,
            'guests' => [],
            'additional_services' => [],
            'discount_amount' => 0,
            'discount_type' => null,
            'payment' => [],
            'agent_id' => null,
        ]);

        // Now book 2 Standard (fail) + 2 Premium (should succeed if Premium availability checked independently)
        $this->expectException(\Exception::class);

        BookingService::createBulkBooking([
            'hotel_id' => $this->hotel->id,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'room_details' => [
                ['room_type_id' => $this->roomType->id, 'quantity' => 2, 'guests_per_room' => 2], // None available
                ['room_type_id' => $roomType2->id, 'quantity' => 2, 'guests_per_room' => 2], // 3 available
            ],
            'guests' => [],
            'additional_services' => [],
            'discount_amount' => 0,
            'discount_type' => null,
            'payment' => [],
            'agent_id' => null,
        ]);
    }

    /**
     * Test 14: Bulk booking with multiple room types generates correct invoice breakdown
     */
    public function test_bulk_booking_invoice_breakdown()
    {
        $checkIn = Carbon::parse('2025-01-27'); // Monday
        $checkOut = Carbon::parse('2025-01-30'); // Thursday (3 nights)

        // Create a second room type
        $roomType2 = RoomType::create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Economy',
            'default_base_guests' => 1,
            'default_max_extra_guests' => 0,
            'status' => true,
        ]);

        RoomRate::create([
            'room_type_id' => $roomType2->id,
            'hotel_id' => $this->hotel->id,
            'base_price' => 1000,
            'weekend_price' => 0,
            'extra_guest_price' => 0,
            'base_guest_count' => 1,
            'max_extra_guests' => 0,
            'status' => true,
        ]);

        // 5 Economy rooms
        for ($i = 1; $i <= 5; $i++) {
            RoomNumber::create([
                'hotel_id' => $this->hotel->id,
                'room_type_id' => $roomType2->id,
                'room_number' => '50' . $i,
                'status' => 0,
            ]);
        }

        $result = BookingService::createBulkBooking([
            'hotel_id' => $this->hotel->id,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'room_details' => [
                ['room_type_id' => $this->roomType->id, 'quantity' => 1, 'guests_per_room' => 2],
                ['room_type_id' => $roomType2->id, 'quantity' => 2, 'guests_per_room' => 1],
            ],
            'guests' => [],
            'additional_services' => [],
            'discount_amount' => 0,
            'discount_type' => null,
            'payment' => [],
            'agent_id' => null,
        ]);

        // Standard: 1 room * 3 nights * 2000 = 6000
        // Economy: 2 rooms * 3 nights * 1000 = 6000
        // Total base: 12000
        $this->assertEquals(12000, $result['booking']->base_amount);
        
        // Invoice should have 2 room items (one per type)
        $roomItems = $result['invoice']->items()->where('item_type', 'room')->get();
        $this->assertCount(2, $roomItems);
    }
}
