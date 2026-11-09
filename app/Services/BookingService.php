<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingGuest;
use App\Models\BookingGuestIdProof;
use App\Models\BookingAdditionalService;
use App\Models\BookingPayment;
use App\Models\Guest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Hotel;
use App\Models\RoomDistribution;
use App\Models\RoomType;
use App\Models\AdditionalService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class BookingService
{
    private static function validateGuestNotAlreadyCheckedIn(int $hotelId, array $guests): void
    {
        if (empty($guests)) {
            return;
        }

        $mobileNumbers = collect($guests)
            ->pluck('mobile')
            ->filter(fn ($mobile) => filled($mobile))
            ->map(fn ($mobile) => preg_replace('/\D+/', '', (string) $mobile))
            ->filter(fn ($mobile) => $mobile !== '')
            ->unique()
            ->values()
            ->all();

        if (empty($mobileNumbers)) {
            return;
        }

        $guestIds = Guest::where('hotel_id', $hotelId)
            ->whereIn('mobile', $mobileNumbers)
            ->pluck('id');

        if ($guestIds->isEmpty()) {
            return;
        }

        $activeGuest = BookingGuest::whereIn('guest_id', $guestIds)
            ->whereHas('booking', function ($query) use ($hotelId) {
                $query->where('hotel_id', $hotelId)
                    ->where('status', 'checked_in')
                    ->whereNull('actual_check_out_at');
            })
            ->first();

        if (!$activeGuest) {
            return;
        }

        $guest = Guest::find($activeGuest->guest_id);
        $guestName = $guest?->full_name ?? 'This guest';

        throw ValidationException::withMessages([
            'guests' => [
                $guestName . ' is already checked in and has not checked out yet. A new booking is not allowed for this guest.'
            ],
        ]);
    }

    /**
     * Create a single booking with all related records in a transaction.
     *
     * @param array $data {
     *   'hotel_id' => int,
     *   'check_in_date' => date,
     *   'check_out_date' => date,
     *   'room_type_id' => int,
     *   'no_of_rooms' => int,
     *   'no_of_guests' => int,
     *   'guests' => [ // array of guest data
     *     ['name', 'mobile', 'email', 'address', 'dob', 'country_id', 'state_id', 'is_primary' => bool, 'id_proofs' => []],
     *     ...
     *   ],
     *   'additional_services' => [ // optional
     *     ['service_id' => int, 'quantity' => int],
     *     ...
     *   ],
     *   'discount_amount' => float (optional),
     *   'discount_type' => 'flat'|'percent' (optional),
     *   'payment' => [ // optional
     *     'method' => string,
     *     'amount_paid' => float,
     *     'reference' => string,
     *     'paid_at' => datetime
     *   ],
     *   'agent_id' => int (optional),
     *   'remark' => string (optional),
     *   'created_by' => int (optional, defaults to Auth::id()),
     *   'collected_by' => int (optional, defaults to Auth::id())
     * }
     * @return array ['success' => bool, 'booking' => Booking, 'message' => string]
     * @throws \Exception
     */
    public static function createSingleBooking($data)
    {
        return DB::transaction(function () use ($data) {
            $paymentData = $data['payment'] ?? [];
            if (empty($paymentData) && (
                !empty($data['payment_method']) ||
                !empty($data['method']) ||
                !empty($data['amount_paid']) ||
                !empty($data['amount']) ||
                !empty($data['reference']) ||
                !empty($data['transaction_reference']) ||
                !empty($data['paid_at'])
            )) {
                $paymentData = [
                    'method' => $data['payment_method'] ?? $data['method'] ?? 'cash',
                    'amount_paid' => $data['amount_paid'] ?? $data['amount'] ?? 0,
                    'reference' => $data['reference'] ?? $data['transaction_reference'] ?? null,
                    'paid_at' => $data['paid_at'] ?? null,
                ];
            }
            $data['payment'] = $paymentData;

            $hotelId = $data['hotel_id'];
            $hotel = Hotel::findOrFail($hotelId);
            $createdBy = $data['created_by'] ?? (Auth::check() ? Auth::id() : null);
            $collectedBy = $data['collected_by'] ?? (Auth::check() ? Auth::id() : null);
            
            $rateType = self::resolveRateType($hotel, $data['rate_type'] ?? null);

            self::validateGuestNotAlreadyCheckedIn($hotelId, $data['guests'] ?? []);

            // 1. Validate availability
            AvailabilityService::validateAvailability(
                $hotelId,
                $data['room_type_id'],
                $data['check_in_date'],
                $data['check_out_date'],
                $data['no_of_rooms'],
                null,
                ($rateType ?? 'room') === 'hourly'
            );

            // 2. Calculate pricing (server-side)
            $pricingResult = PricingService::calculateBookingPrice(
                $hotelId,
                $data['room_type_id'],
                $data['check_in_date'],
                $data['check_out_date'],
                $data['no_of_guests'],
                $data['no_of_rooms'],
                $data['discount_amount'] ?? 0,
                $data['discount_type'] ?? null,
                $rateType
            );

            // 3. Calculate service charges if any
            $serviceResult = ['services' => [], 'total_service_amount' => 0];
            if (!empty($data['additional_services'])) {
                $serviceResult = PricingService::calculateServiceCharges(
                    $data['additional_services'],
                    $hotel
                );
            }

            // 4. Generate booking code
            $bookingCode = self::generateBookingCode($hotelId);

            // 5. Create booking record
            $booking = Booking::create([
                'hotel_id' => $hotelId,
                'booking_code' => $bookingCode,
                'booking_number' => self::getNextBookingNumber($hotelId),
                'booking_type' => $rateType === 'hourly' ? 'hourly' : 'regular',
                'agent_id' => $data['agent_id'] ?? null,
                'check_in_date' => $data['check_in_date'],
                'check_out_date' => $data['check_out_date'],
                'no_of_rooms' => $data['no_of_rooms'],
                'no_of_guests' => $data['no_of_guests'],
                'base_amount' => $pricingResult['base_amount'],
                'discount' => $pricingResult['discount_amount'],
                'discount_type' => $pricingResult['discount_type'],
                'cgst' => $pricingResult['cgst'],
                'sgst' => $pricingResult['sgst'],
                'igst' => $pricingResult['igst'],
                'total_amount' => $pricingResult['total_amount'] + $serviceResult['total_service_amount'],
                'status' => 'pending',
                'remark' => $data['remark'] ?? null,
                'created_by' => $createdBy,
            ]);

            // 6. Add guest records
            if (!empty($data['guests'])) {
                foreach ($data['guests'] as $guestData) {
                    $guest = GuestService::createOrUpdateGuest($guestData, $hotelId);

                    $bookingGuest = BookingGuest::create([
                        'booking_id' => $booking->id,
                        'guest_id' => $guest->id,
                        'full_name' => $guest->full_name,
                        'mobile' => $guest->mobile,
                        'email' => $guest->email,
                        'address' => $guest->address,
                        'dob' => $guest->dob,
                        'country_id' => $guest->country_id,
                        'state_id' => $guest->state_id,
                        'is_primary_guest' => $guestData['is_primary'] ?? false
                    ]);

                    $guest->fresh()->idProofs->each(function ($proof) use ($bookingGuest) {
                        BookingGuestIdProof::create([
                            'booking_guest_id' => $bookingGuest->id,
                            'id_type' => $proof->id_type,
                            'id_number' => $proof->id_number,
                            'id_file_path' => $proof->id_file_path,
                            'is_primary' => false
                        ]);
                    });
                }
            }

            // 7. Allocate rooms
            RoomAllocationService::allocateRooms(
                $hotelId,
                $booking->id,
                $data['room_type_id'],
                $data['no_of_rooms'],
                $data['check_in_date'],
                $data['check_out_date']
            );

            // 8. Add additional services
            if (!empty($serviceResult['services'])) {
                foreach ($serviceResult['services'] as $service) {
                    BookingAdditionalService::create([
                        'booking_id' => $booking->id,
                        'additional_service_id' => $service['service_id'],
                        'quantity' => $service['quantity'],
                        'amount' => $service['total_cost']
                    ]);
                }
            }

            // 9. Add payment if provided
            if (!empty($data['payment']) && ((float) ($data['payment']['amount_paid'] ?? 0)) > 0) {
                BookingPayment::create([
                    'booking_id' => $booking->id,
                    'hotel_id' => $hotelId,
                    'method' => $data['payment']['method'] ?? 'cash',
                    'amount' => (float) ($data['payment']['amount_paid'] ?? 0),
                    'paid_at' => $data['payment']['paid_at'] ?? now(),
                    'transaction_reference' => $data['payment']['reference'] ?? null,
                    'status' => 'paid',
                    'collected_by' => $collectedBy,
                ]);
            }

            // 10. Create invoice
            // $invoice = self::generateInvoice(
            //     $booking,
            //     $pricingResult,
            //     $serviceResult
            // );

            // 11. Update booking status to confirmed
            $booking->update(['status' => 'confirmed']);

            // 12. Log the event
            auditLog(
                module: 'booking',
                action: 'create',
                recordId: $booking->id,
                description: 'Single booking created: ' . $booking->booking_code,
                event: 'BOOKING_CREATED',
                new: $booking->toArray()
            );

            return [
                'success' => true,
                'booking' => $booking->load([
                    'bookingGuests.idProofs',
                    'additionalServices',
                    'payments',
                    'roomDistributions'
                ]),
                'message' => 'Booking created successfully'
            ];
        });
    }

    /**
     * Create a bulk booking with multiple room types in a single transaction.
     *
     * @param array $data {
     *   'hotel_id' => int,
     *   'check_in_date' => date,
     *   'check_out_date' => date,
     *   'room_details' => [ // array of room type + quantity pairs
     *     ['room_type_id' => int, 'quantity' => int, 'guests_per_room' => int],
     *     ...
     *   ],
     *   'guests' => [ // optional: overall booking guests
     *     ['name', 'mobile', 'email', 'address', 'dob', 'country_id', 'state_id', 'is_primary' => bool],
     *     ...
     *   ],
     *   'additional_services' => [ // optional
     *     ['service_id' => int, 'quantity' => int],
     *     ...
     *   ],
     *   'discount_amount' => float (optional),
     *   'discount_type' => 'flat'|'percent' (optional),
     *   'payment' => [ // optional
     *     'method' => string,
     *     'amount_paid' => float,
     *     'reference' => string,
     *     'paid_at' => datetime
     *   ],
     *   'agent_id' => int (optional),
     *   'remark' => string (optional),
     *   'created_by' => int (optional),
     *   'collected_by' => int (optional)
     * }
     * @return array ['success' => bool, 'booking' => Booking, 'invoice' => Invoice, 'message' => string]
     * @throws \Exception
     */
    public static function createBulkBooking($data)
    {
        return DB::transaction(function () use ($data) {
            $paymentData = $data['payment'] ?? [];
            if (empty($paymentData) && (
                !empty($data['payment_method']) ||
                !empty($data['method']) ||
                !empty($data['amount_paid']) ||
                !empty($data['amount']) ||
                !empty($data['reference']) ||
                !empty($data['transaction_reference']) ||
                !empty($data['paid_at'])
            )) {
                $paymentData = [
                    'method' => $data['payment_method'] ?? $data['method'] ?? 'cash',
                    'amount_paid' => $data['amount_paid'] ?? $data['amount'] ?? 0,
                    'reference' => $data['reference'] ?? $data['transaction_reference'] ?? null,
                    'paid_at' => $data['paid_at'] ?? null,
                ];
            }
            $data['payment'] = $paymentData;

            $hotelId = $data['hotel_id'];
            $hotel = Hotel::findOrFail($hotelId);
            $createdBy = $data['created_by'] ?? (Auth::check() ? Auth::id() : null);
            $collectedBy = $data['collected_by'] ?? (Auth::check() ? Auth::id() : null);

            $roomDetails = $data['room_details'] ?? [];
            if (empty($roomDetails)) {
                throw new \Exception('At least one room type must be specified for bulk booking');
            }

            self::validateGuestNotAlreadyCheckedIn($hotelId, $data['guests'] ?? []);

            // 1. Validate availability for each room type independently
            $totalRooms = 0;
            $totalGuests = 0;
            $pricingPerType = [];

            foreach ($roomDetails as $detail) {
                $roomTypeId = $detail['room_type_id'];
                $quantity = $detail['quantity'];
                $guestsPerRoom = $detail['guests_per_room'] ?? 1;

                if ($quantity < 1) {
                    throw new \Exception("Room quantity must be at least 1 for room type {$roomTypeId}");
                }

                // Validate availability for this room type
                AvailabilityService::validateAvailability(
                    $hotelId,
                    $roomTypeId,
                    $data['check_in_date'],
                    $data['check_out_date'],
                    $quantity
                );

                // Calculate pricing for this room type
                $pricingPerType[$roomTypeId] = PricingService::calculateBookingPrice(
                    $hotelId,
                    $roomTypeId,
                    $data['check_in_date'],
                    $data['check_out_date'],
                    $guestsPerRoom,
                    $quantity,
                    0, // discount handled at booking level
                    null
                );

                $totalRooms += $quantity;
                $totalGuests += ($guestsPerRoom * $quantity);
            }

            // 2. Calculate combined pricing (base amount from all types + discount + taxes)
            $baseAmount = array_sum(array_map(function ($pricing) {
                return $pricing['base_amount'];
            }, $pricingPerType));

            // Apply discount to total
            $discountApplied = 0;
            if (isset($data['discount_amount']) && $data['discount_amount'] > 0 && isset($data['discount_type'])) {
                if ($data['discount_type'] === 'percent') {
                    $discountApplied = ($baseAmount * $data['discount_amount']) / 100;
                } elseif ($data['discount_type'] === 'flat') {
                    $discountApplied = $data['discount_amount'];
                }
                $discountApplied = min($discountApplied, $baseAmount);
            }

            $subtotal = $baseAmount - $discountApplied;

            // 3. Calculate service charges if any
            $serviceResult = ['services' => [], 'total_service_amount' => 0];
            if (!empty($data['additional_services'])) {
                $serviceResult = PricingService::calculateServiceCharges(
                    $data['additional_services'],
                    $hotel
                );
            }

            // 4. Calculate taxes on combined amount
            $taxes = PricingService::calculateTaxes($subtotal, $hotel);
            $totalAmount = $subtotal + $taxes['total_tax'] + $serviceResult['total_service_amount'];

            // 5. Generate booking code
            $bookingCode = self::generateBookingCode($hotelId);

            // 6. Create booking record (bulk type)
            $booking = Booking::create([
                'hotel_id' => $hotelId,
                'booking_code' => $bookingCode,
                'booking_number' => self::getNextBookingNumber($hotelId),
                'booking_type' => 'bulk',
                'agent_id' => $data['agent_id'] ?? null,
                'check_in_date' => $data['check_in_date'],
                'check_out_date' => $data['check_out_date'],
                'no_of_rooms' => $totalRooms,
                'no_of_guests' => $totalGuests,
                'base_amount' => $baseAmount,
                'discount' => $discountApplied,
                'discount_type' => $data['discount_type'] ?? null,
                'cgst' => $taxes['cgst'],
                'sgst' => $taxes['sgst'],
                'igst' => $taxes['igst'],
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'remark' => $data['remark'] ?? null,
                'created_by' => $createdBy,
            ]);

            // 7. Add guest records
            if (!empty($data['guests'])) {
                foreach ($data['guests'] as $guestData) {
                    $guest = GuestService::createOrUpdateGuest($guestData, $hotelId);

                    $bookingGuest = BookingGuest::create([
                        'booking_id' => $booking->id,
                        'guest_id' => $guest->id,
                        'full_name' => $guest->full_name,
                        'mobile' => $guest->mobile,
                        'email' => $guest->email,
                        'address' => $guest->address,
                        'dob' => $guest->dob,
                        'country_id' => $guest->country_id,
                        'state_id' => $guest->state_id,
                        'is_primary_guest' => $guestData['is_primary'] ?? false
                    ]);

                    $guest->fresh()->idProofs->each(function ($proof) use ($bookingGuest) {
                        BookingGuestIdProof::create([
                            'booking_guest_id' => $bookingGuest->id,
                            'id_type' => $proof->id_type,
                            'id_number' => $proof->id_number,
                            'id_file_path' => $proof->id_file_path,
                            'is_primary' => false
                        ]);
                    });
                }
            }

            // 8. Allocate rooms for each room type
            foreach ($roomDetails as $detail) {
                RoomAllocationService::allocateRooms(
                    $hotelId,
                    $booking->id,
                    $detail['room_type_id'],
                    $detail['quantity'],
                    $data['check_in_date'],
                    $data['check_out_date']
                );
            }

            // 9. Add additional services
            if (!empty($serviceResult['services'])) {
                foreach ($serviceResult['services'] as $service) {
                    BookingAdditionalService::create([
                        'booking_id' => $booking->id,
                        'additional_service_id' => $service['service_id'],
                        'quantity' => $service['quantity'],
                        'amount' => $service['total_cost']
                    ]);
                }
            }

            // 10. Add payment if provided
            if (!empty($data['payment']) && ((float) ($data['payment']['amount_paid'] ?? 0)) > 0) {
                BookingPayment::create([
                    'booking_id' => $booking->id,
                    'hotel_id' => $hotelId,
                    'method' => $data['payment']['method'] ?? 'cash',
                    'amount' => (float) ($data['payment']['amount_paid'] ?? 0),
                    'paid_at' => $data['payment']['paid_at'] ?? now(),
                    'transaction_reference' => $data['payment']['reference'] ?? null,
                    'status' => 'paid',
                    'collected_by' => $collectedBy,
                ]);
            }

            // 11. Create invoice
            $invoice = self::generateBulkInvoice(
                $booking,
                $pricingPerType,
                $roomDetails,
                $serviceResult,
                $discountApplied,
                $taxes
            );

            // 12. Update booking status to confirmed
            $booking->update(['status' => 'confirmed']);

            // 13. Log the event
            auditLog(
                module: 'booking',
                action: 'create',
                recordId: $booking->id,
                description: 'Bulk booking created: ' . $booking->booking_code . ' (' . $totalRooms . ' rooms, ' . count($roomDetails) . ' types)',
                event: 'BOOKING_CREATED',
                new: $booking->toArray()
            );

            return [
                'success' => true,
                'booking' => $booking->load([
                    'bookingGuests.idProofs',
                    'additionalServices',
                    'payments',
                    'roomDistributions.roomType'
                ]),
                'invoice' => $invoice,
                'message' => 'Bulk booking created successfully'
            ];
        });
    }

    /**
     * Generate invoice for a bulk booking with per-type breakdown.
     *
     * @param Booking $booking
     * @param array $pricingPerType - pricing keyed by room_type_id
     * @param array $roomDetails - room details with quantities
     * @param array $serviceResult
     * @param float $discountApplied
     * @param array $taxes
     * @return Invoice
     */
    protected static function generateBulkInvoice(
        Booking $booking,
        array $pricingPerType,
        array $roomDetails,
        array $serviceResult,
        float $discountApplied,
        array $taxes
    ) {
        $hotel = $booking->hotel;

        $invoice = Invoice::create([
            'hotel_id' => $booking->hotel_id,
            'booking_id' => $booking->id,
            'invoice_number' => self::generateInvoiceNumber($hotel),
            'is_gst_applicable' => $hotel->is_gst_applicable,
            'invoice_tax_type' => $hotel->invoice_tax_type,
            'cgst_percentage' => $hotel->cgst_percentage,
            'sgst_percentage' => $hotel->sgst_percentage,
            'igst_percentage' => $hotel->igst_percentage,
            'room_amount' => array_sum(array_map(function ($p) { return $p['base_amount']; }, $pricingPerType)),
            'service_amount' => $serviceResult['total_service_amount'],
            'subtotal' => array_sum(array_map(function ($p) { return $p['base_amount']; }, $pricingPerType)) - $discountApplied,
            'cgst_amount' => $taxes['cgst'],
            'sgst_amount' => $taxes['sgst'],
            'igst_amount' => $taxes['igst'],
            'total_tax' => $taxes['total_tax'],
            'grand_total' => $booking->total_amount,
            'invoice_date' => now(),
        ]);

        // Create invoice items for each room type (bulk breakdown)
        $roomTypeMap = [];
        foreach ($roomDetails as $detail) {
            $roomTypeId = $detail['room_type_id'];
            $quantity = $detail['quantity'];
            $pricing = $pricingPerType[$roomTypeId];

            $roomType = RoomType::find($roomTypeId);
            $roomTypeName = $roomType->name ?? "Room Type {$roomTypeId}";

            // Create one invoice item per room type showing total for that type
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_type' => 'room',
                'item_name' => $roomTypeName,
                'description' => "{$roomTypeName} x{$quantity} rooms for {$pricing['nights']} nights",
                'quantity' => $quantity,
                'unit_price' => $pricing['base_amount'] / $quantity,
                'amount' => $pricing['base_amount'],
            ]);
        }

        // Create invoice items for services
        foreach ($serviceResult['services'] as $service) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_type' => 'service',
                'item_name' => $service['service_name'],
                'description' => "{$service['service_name']} (x{$service['quantity']})",
                'quantity' => $service['quantity'],
                'unit_price' => $service['cost_per_unit'],
                'amount' => $service['total_cost'],
            ]);
        }

        return $invoice;
    }

    /**
     * Generate booking number for hotel.
     *
     * @param int $hotelId
     * @return string
     */
    protected static function generateBookingCode($hotelId)
    {
        $hotel = Hotel::find($hotelId);
        $prefix = $hotel->booking_prefix ?? 'BK';
        $number = self::getNextBookingNumber($hotelId);

        return $prefix . '-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get next booking number for hotel.
     *
     * @param int $hotelId
     * @return int
     */
    protected static function getNextBookingNumber($hotelId)
    {
        $lastBooking = Booking::where('hotel_id', $hotelId)
            ->orderBy('booking_number', 'desc')
            ->lockForUpdate()
            ->first();

        $hotel = Hotel::find($hotelId);
        $startSequence = $hotel->booking_sequence ?? 1000;

        return $lastBooking
            ? $lastBooking->booking_number + 1
            : $startSequence;
    }

    /**
     * Generate invoice for a booking.
     *
     * @param Booking $booking
     * @param array $pricingResult
     * @param array $serviceResult
     * @return Invoice
     */
    protected static function resolveRateType(Hotel $hotel, ?string $rateType = null): string
    {
        $selectedType = $rateType ?: $hotel->getDefaultBookingRateType();

        if (!in_array($selectedType, ['room', 'hourly'], true)) {
            return $hotel->getDefaultBookingRateType();
        }

        if ($selectedType === 'hourly' && !$hotel->supportsHourlyRate()) {
            return $hotel->supportsRoomRate() ? 'room' : 'hourly';
        }

        if ($selectedType === 'room' && !$hotel->supportsRoomRate()) {
            return $hotel->supportsHourlyRate() ? 'hourly' : 'room';
        }

        return $selectedType;
    }

    protected static function generateInvoice(Booking $booking, array $pricingResult, array $serviceResult)
    {
        $hotel = $booking->hotel;

        $invoice = Invoice::create([
            'hotel_id' => $booking->hotel_id,
            'booking_id' => $booking->id,
            'invoice_number' => self::generateInvoiceNumber($hotel),
            'is_gst_applicable' => $hotel->is_gst_applicable,
            'invoice_tax_type' => $hotel->invoice_tax_type,
            'cgst_percentage' => $hotel->cgst_percentage,
            'sgst_percentage' => $hotel->sgst_percentage,
            'igst_percentage' => $hotel->igst_percentage,
            'room_amount' => $pricingResult['base_amount'],
            'service_amount' => $serviceResult['total_service_amount'],
            'subtotal' => $pricingResult['subtotal'],
            'cgst_amount' => $pricingResult['cgst'],
            'sgst_amount' => $pricingResult['sgst'],
            'igst_amount' => $pricingResult['igst'],
            'total_tax' => $pricingResult['total_tax'],
            'grand_total' => $pricingResult['total_amount'] + $serviceResult['total_service_amount'],
            'invoice_date' => now(),
        ]);

        // Create invoice items for rooms (daily breakdown)
        foreach ($pricingResult['daily_breakdown'] as $dateStr => $breakdown) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_type' => 'room',
                'item_name' => 'Room Charge',
                'description' => "Room - {$dateStr} ({$breakdown['rate_type']})",
                'quantity' => $pricingResult['rooms'],
                'unit_price' => $breakdown['room_price'] + $breakdown['extra_price'],
                'amount' => ($breakdown['room_price'] + $breakdown['extra_price']) * $pricingResult['rooms'],
            ]);
        }

        // Create invoice items for services
        foreach ($serviceResult['services'] as $service) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_type' => 'service',
                'item_name' => $service['service_name'],
                'description' => "{$service['service_name']} (x{$service['quantity']})",
                'quantity' => $service['quantity'],
                'unit_price' => $service['cost_per_unit'],
                'amount' => $service['total_cost'],
            ]);
        }

        return $invoice;
    }

    /**
     * Generate unique invoice number.
     *
     * @param Hotel $hotel
     * @return string
     */
    protected static function generateInvoiceNumber(Hotel $hotel)
    {
        $prefix = $hotel->invoice_prefix ?? 'INV';
        $lastInvoice = Invoice::where('hotel_id', $hotel->id)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastInvoice ? ((int)substr($lastInvoice->invoice_number, -5)) + 1 : 1;

        return $prefix . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Cancel a booking and release room allocations.
     *
     * @param int $bookingId
     * @param string $reason
     * @param float|null $refundAmount
     * @return array ['success' => bool, 'message' => string]
     * @throws \Exception
     */
    public static function cancelBooking($bookingId, $reason = '', $refundAmount = null)
    {
        return DB::transaction(function () use ($bookingId, $reason, $refundAmount) {
            $booking = Booking::findOrFail($bookingId);

            if (in_array($booking->status, ['checked_out', 'cancelled'])) {
                throw new \Exception('Cannot cancel a ' . $booking->status . ' booking');
            }

            // Calculate refund if not provided
            if ($refundAmount === null) {
                $totalPaid = $booking->payments->sum('amount');
                $refundAmount = $totalPaid;
            }

            // Update booking status
            $booking->update([
                'status' => 'cancelled',
                'refunded_amount' => $refundAmount,
                'refund_status' => $refundAmount > 0 ? 'full' : 'none',
                'remark' => ($booking->remark ?? '') . "\n\nCancelled: {$reason}",
            ]);

            // Release room allocations
            RoomAllocationService::releaseAllocations($bookingId);

            // Log the event
            auditLog(
                module: 'booking',
                action: 'cancel',
                recordId: $booking->id,
                description: 'Booking cancelled: ' . $reason,
                event: 'BOOKING_CANCELLED',
                old: ['status' => 'confirmed'],
                new: ['status' => 'cancelled']
            );

            return [
                'success' => true,
                'message' => 'Booking cancelled successfully'
            ];
        });
    }
}
