<?php

namespace App\Http\Controllers;

use App\Models\AdditionalService;
use App\Models\Agent;
use App\Models\Booking;
use App\Models\BookingAdditionalService;
use App\Models\BookingGuest;
use App\Models\BookingGuestIdProof;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\BookingPayment;
use App\Models\GuestIdProof;
use App\Models\Hotel;
use Illuminate\Support\Facades\Storage;
use App\Models\RoomDistribution;
use App\Models\RoomNumber;
use App\Models\RoomType;
use App\Services\GuestService;
use Carbon\Carbon;
use App\Services\RoomAllocationService;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use App\Services\PricingService;
use App\Services\RoomChangeService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class BookingController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:bookings,view', only: ['index', 'show', 'bookmap', 'getAvailableRooms']),
            new Middleware('permission:bookings,create', only: ['create', 'store', 'storeBulk']),
            new Middleware('permission:bookings,edit', only: ['edit', 'update']),
            new Middleware('permission:bookings,cancel', only: ['cancel']),
            new Middleware('permission:bookings,checkin', only: ['checkIn']),
            new Middleware('permission:bookings,checkout', only: ['checkOut']),
            new Middleware('permission:bookings,noshow', only: ['noShow']),
            new Middleware('permission:house_keeping,view', only: ['housekeeping']),
            new Middleware('permission:house_keeping,edit', only: ['updateHousekeepingRoomStatus']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $additional_services = auth()->user()->hotel?->additionalServices()->where('status', 1)->get();
        $countries = Country::all();
        $states = State::all();
        $agents = Agent::where('hotel_id', auth()->user()->hotel_id)
            ->where('status', 1)
            ->get();
        $query = Booking::where('hotel_id', Auth::user()->hotel_id)->latest();

        // 🔍 Filters


        $today = Carbon::today();

        // Check-in Today
        if (request('checkin_today')) {
            $query->whereDate('check_in_date', $today);
        }

        // Check-out Today
        if (request('checkout_today')) {
            $query->whereDate('check_out_date', $today);
        }
        // Booking Code
        if (request('booking_code')) {
            $query->where('booking_code', 'like', '%' . request('booking_code') . '%');
        }

        // Guest Name (relation)
        if (request('guest_name')) {
            $query->whereHas('primaryGuest', function ($q) {
                $q->where('full_name', 'like', '%' . request('guest_name') . '%');
            });
        }

        // Check-in Date
        if (request('check_in_date')) {
            $query->whereDate('check_in_date', request('check_in_date'));
        }

        // Check-out Date
        if (request('check_out_date')) {
            $query->whereDate('check_out_date', request('check_out_date'));
        }

        // Status
        if (request('status')) {
            $query->where('status', request('status'));
        }

        // Final Data
        $bookings = $query->paginate(perPage())->withQueryString();
        $roomTypes = RoomType::where('hotel_id', auth()->user()->hotel_id)->where('status', 1)->get();
        return view('pages.bookings.index', compact('additional_services', 'countries', 'states', 'agents', 'bookings', 'roomTypes'));
    }

    public function housekeeping(Request $request)
    {
        $hotelId = auth()->user()->hotel_id;

        $roomTypes = RoomType::with(['roomNumbers' => function ($query) {
                $query->orderBy('room_number');
            }])
            ->where('hotel_id', $hotelId)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        return view('pages.housekeeping.index', compact('roomTypes'));
    }

    public function updateHousekeepingRoomStatus(Request $request)
    {
        $validated = $request->validate([
            'room_id' => ['required', 'integer', 'exists:room_numbers,id'],
            'status' => ['required', 'integer', 'in:0,2,3,4,5'],
        ]);

        $room = RoomNumber::where('id', $validated['room_id'])
            ->where('hotel_id', auth()->user()->hotel_id)
            ->first();

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room not found for this hotel.'
            ], 404);
        }

        $room->update([
            'status' => (int) $validated['status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Room status updated successfully.',
            'status' => (int) $room->status,
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     * Uses BookingService for server-side validation and pricing calculation.
     */
    public function store(Request $request)
    {
        try {
            $hotel = auth()->user()->hotel;
            $allowedRateTypes = $hotel?->getBookingRateOptions() ?? ['room'];
            $allowedRateTypesString = implode(',', $allowedRateTypes);

            // ===============================
            // ✅ VALIDATION
            // ===============================
            $rateType = $request->input('rate_type', $hotel?->getDefaultBookingRateType() ?? 'room');

            $paymentMethod = $request->input('payment_method', $request->input('payment.method'));
            $paymentAmount = $request->input('amount_paid', $request->input('amount', $request->input('payment.amount_paid')));
            $paymentReference = $request->input('reference', $request->input('payment.reference'));
            $paymentPaidAt = $request->input('paid_at', $request->input('payment.paid_at'));

            $normalizedPayment = [];
            if ($request->filled('payment_method') || $request->filled('payment.method') || $request->filled('amount_paid') || $request->filled('amount') || $request->filled('payment.amount_paid') || $request->filled('reference') || $request->filled('payment.reference') || $request->filled('paid_at') || $request->filled('payment.paid_at')) {
                $normalizedPayment = [
                    'method' => $paymentMethod,
                    'amount_paid' => $paymentAmount,
                    'reference' => $paymentReference,
                    'paid_at' => $paymentPaidAt,
                ];
            }

            $request->merge(['payment' => $normalizedPayment]);

            $validated = $request->validate([
                'check_in_date'        => 'required|date|after_or_equal:today',
                'check_out_date'       => ['required', 'date', $rateType === 'hourly' ? 'after_or_equal:check_in_date' : 'after:check_in_date'],
                'room_type_id'         => 'required|exists:room_types,id',
                'no_of_guests'         => 'required|integer|min:1',
                'no_of_rooms'          => 'required|integer|min:1',
                'rate_type'            => ['nullable', 'in:' . $allowedRateTypesString],

                'agent_id'             => 'nullable|exists:agents,id',

                'discount_amount'      => 'nullable|numeric|min:0',
                'discount_type'        => 'nullable|in:flat,percent',

                'guests'               => 'nullable|array|min:1',
                'primary_guest'        => 'nullable|integer|min:0',
                'guests.*.name'        => 'required|string|max:255',
                'guests.*.mobile'      => 'required|string|max:20',
                'guests.*.email'       => 'nullable|email',
                'guests.*.address'     => 'nullable|string',
                'guests.*.dob'         => 'nullable|date',
                'guests.*.country_id'  => 'nullable|integer',
                'guests.*.state_id'    => 'nullable|integer',
                'guests.*.is_primary'  => 'nullable|boolean',
                'guests.*.id_proofs'   => 'nullable|array',

                'additional_services'  => 'nullable|array',
                'additional_services.*.service_id'  => 'exists:additional_services,id',
                'additional_services.*.quantity'    => 'nullable|integer|min:1',

                'payment'              => 'nullable|array',
                'payment.method'       => 'nullable|in:cash,card,upi,online',
                'payment.amount_paid'  => ['nullable', 'numeric', 'min:0', Rule::requiredIf($request->filled('payment.paid_at') || $request->filled('paid_at'))],
                'payment.reference'    => ['nullable', 'string', 'max:255', Rule::requiredIf($request->filled('payment.paid_at') || $request->filled('paid_at'))],
                'payment.paid_at'      => 'nullable|date',

                'payment_method'       => 'nullable|in:cash,card,upi,online',
                'amount_paid'          => ['nullable', 'numeric', 'min:0', Rule::requiredIf($request->filled('paid_at'))],
                'reference'            => ['nullable', 'string', 'max:255', Rule::requiredIf($request->filled('paid_at'))],
                'paid_at'              => 'nullable|date',

                'remark'               => 'nullable|string|max:1000',
            ]);

            $guests = $validated['guests'] ?? [];
            $primaryGuestIndex = $validated['primary_guest'] ?? null;

            if (!empty($guests)) {
                if (
                    $primaryGuestIndex !== null &&
                    !array_key_exists($primaryGuestIndex, $guests)
                ) {
                    return back()
                        ->withErrors(['primary_guest' => 'Please select a valid lead guest.'])
                        ->withInput();
                }

                foreach ($guests as $index => &$guest) {
                    $guest['is_primary'] = (
                        $primaryGuestIndex !== null &&
                        (string) $index === (string) $primaryGuestIndex
                    );
                }
                unset($guest);
            }


            // ===============================
            // ✅ USE BOOKING SERVICE
            // ===============================
            $bookingData = [
                'hotel_id' => Auth::user()->hotel_id,
                'check_in_date' => $validated['check_in_date'],
                'check_out_date' => $validated['check_out_date'],
                'room_type_id' => $validated['room_type_id'],
                'no_of_rooms' => $validated['no_of_rooms'],
                'no_of_guests' => $validated['no_of_guests'],
                'rate_type' => $validated['rate_type'] ?? ($hotel?->getDefaultBookingRateType() ?? 'room'),
                'guests' => $guests,
                'additional_services' => $validated['additional_services'] ?? [],
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'discount_type' => $validated['discount_type'] ?? null,
                'payment' => $validated['payment'] ?? [],
                'agent_id' => $validated['agent_id'] ?? null,
                'remark' => $validated['remark'] ?? null,
            ];

            $result = BookingService::createSingleBooking($bookingData);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Booking failed.',
                ], 422);
            }

            return response()->json([
                'status' => true,
                'message' => $result['message'],
                'booking_id' => $result['booking']->id,
                'booking_code' => $result['booking']->booking_code,
                'total_amount' => $result['booking']->total_amount,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Booking Store Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to create booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a bulk booking with multiple room types.
     */
    public function storeBulk(Request $request)
    {
        try {
            // ===============================
            // ✅ VALIDATION
            // ===============================
            $rateType = $request->input('rate_type', 'room');

            $paymentMethod = $request->input('payment_method', $request->input('payment.method'));
            $paymentAmount = $request->input('amount_paid', $request->input('amount', $request->input('payment.amount_paid')));
            $paymentReference = $request->input('reference', $request->input('payment.reference'));
            $paymentPaidAt = $request->input('paid_at', $request->input('payment.paid_at'));

            $normalizedPayment = [];
            if ($request->filled('payment_method') || $request->filled('payment.method') || $request->filled('amount_paid') || $request->filled('amount') || $request->filled('payment.amount_paid') || $request->filled('reference') || $request->filled('payment.reference') || $request->filled('paid_at') || $request->filled('payment.paid_at')) {
                $normalizedPayment = [
                    'method' => $paymentMethod,
                    'amount_paid' => $paymentAmount,
                    'reference' => $paymentReference,
                    'paid_at' => $paymentPaidAt,
                ];
            }

            $request->merge(['payment' => $normalizedPayment]);

            $validated = $request->validate([
                'check_in_date'        => 'required|date|after_or_equal:today',
                'check_out_date'       => ['required', 'date', $rateType === 'hourly' ? 'after_or_equal:check_in_date' : 'after:check_in_date'],

                'room_details'         => 'required|array|min:1',
                'room_details.*.room_type_id'   => 'required|exists:room_types,id',
                'room_details.*.quantity'       => 'required|integer|min:1',
                'room_details.*.guests_per_room' => 'nullable|integer|min:1',

                'agent_id'             => 'nullable|exists:agents,id',

                'discount_amount'      => 'nullable|numeric|min:0',
                'discount_type'        => 'nullable|in:flat,percent',

                'guests'               => 'nullable|array',
                'guests.*.name'        => 'required|string|max:255',
                'guests.*.mobile'      => 'required|string|max:20',
                'guests.*.email'       => 'nullable|email',
                'guests.*.address'     => 'nullable|string',
                'guests.*.dob'         => 'nullable|date',
                'guests.*.country_id'  => 'nullable|integer',
                'guests.*.state_id'    => 'nullable|integer',
                'guests.*.is_primary'  => 'nullable|boolean',

                'additional_services'  => 'nullable|array',
                'additional_services.*.service_id'  => 'exists:additional_services,id',
                'additional_services.*.quantity'    => 'nullable|integer|min:1',

                'payment'              => 'nullable|array',
                'payment.method'       => 'nullable|in:cash,card,upi,online',
                'payment.amount_paid'  => ['nullable', 'numeric', 'min:0', Rule::requiredIf($request->filled('payment.paid_at') || $request->filled('paid_at'))],
                'payment.reference'    => ['nullable', 'string', 'max:255', Rule::requiredIf($request->filled('payment.paid_at') || $request->filled('paid_at'))],
                'payment.paid_at'      => 'nullable|date',

                'payment_method'       => 'nullable|in:cash,card,upi,online',
                'amount_paid'          => ['nullable', 'numeric', 'min:0', Rule::requiredIf($request->filled('paid_at'))],
                'reference'            => ['nullable', 'string', 'max:255', Rule::requiredIf($request->filled('paid_at'))],
                'paid_at'              => 'nullable|date',

                'remark'               => 'nullable|string|max:1000',
            ]);

            // ===============================
            // ✅ USE BOOKING SERVICE
            // ===============================
            $bookingData = [
                'hotel_id' => Auth::user()->hotel_id,
                'check_in_date' => $validated['check_in_date'],
                'check_out_date' => $validated['check_out_date'],
                'room_details' => $validated['room_details'],
                'guests' => $validated['guests'] ?? [],
                'additional_services' => $validated['additional_services'] ?? [],
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'discount_type' => $validated['discount_type'] ?? null,
                'payment' => $validated['payment'] ?? [],
                'agent_id' => $validated['agent_id'] ?? null,
                'remark' => $validated['remark'] ?? null,
            ];

            $result = BookingService::createBulkBooking($bookingData);

            return response()->json([
                'status' => true,
                'message' => $result['message'],
                'booking_id' => $result['booking']->id,
                'booking_code' => $result['booking']->booking_code,
                'total_rooms' => $result['booking']->no_of_rooms,
                'total_amount' => $result['booking']->total_amount,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Bulk Booking Store Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to create bulk booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $booking = Booking::with([
                'bookingGuests.idProofs',
                'additionalServices',
                'payments',
                'roomDistributions.roomType',
                'agent'
            ])->findOrFail($id);

            $roomType = optional($booking->roomDistributions->first())->roomType;

            return response()->json([
                'status' => true,
                'data' => [
                    'booking'   => $booking,
                    'room_type' => $roomType,
                    'guests'    => $booking->bookingGuests,
                    'services'  => $booking->additionalServices,
                    'payments'  => $booking->payments,
                    'agent'     => $booking->agent
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Booking not found or ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $booking = Booking::with([
            'bookingGuests.idProofs',
            'additionalServices',
            'payments',
            'roomDistributions.roomType',
            'payments',
            'additionalServices'
        ])->findOrFail($id);

        $roomType = optional($booking->roomDistributions->first())->roomType;

        return response()->json([
            'status' => true,
            'data' => [
                'booking' => $booking,
                'room_type' => $roomType,
                'guests' => $booking->bookingGuests,
                'services' => $booking->additionalServices,
                'payments' => $booking->payments
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Booking $booking)
    {
        try {
            $request->merge([
                'check_in_date' => $request->input('check_in_date', $request->input('check_in')),
                'check_out_date' => $request->input('check_out_date', $request->input('check_out')),
            ]);

            $hourlyBooking = ($booking->booking_type ?? null) === 'hourly' || ($request->input('rate_type') ?? null) === 'hourly';

            $validated = $request->validate([
                'check_in_date' => 'required|date',
                'check_out_date' => ['required', 'date', $hourlyBooking ? 'after_or_equal:check_in_date' : 'after:check_in_date'],
                'no_of_guests' => 'required|integer|min:1',
                'no_of_rooms' => 'required|integer|min:1',
                'agent_id' => 'nullable|exists:agents,id',
                'remark' => 'nullable|string|max:1000',
                'guests' => 'nullable|array|min:1',
                'primary_guest' => 'nullable|integer|min:0',
                'guests.*.name' => 'required|string|max:255',
                'guests.*.mobile' => 'required|string|max:20',
                'guests.*.email' => 'nullable|email|max:255',
                'guests.*.address' => 'nullable|string|max:500',
                'guests.*.dob' => 'nullable|date',
                'guests.*.country_id' => 'nullable|exists:countries,id',
                'guests.*.state_id' => 'nullable|exists:states,id',
                'services' => 'nullable|array',
                'services.*' => 'exists:additional_services,id',
                'service_qty' => 'nullable|array',
                'payment_method' => 'nullable|in:cash,card,upi,online',
                'paid_at' => 'nullable|date',
                'reference' => ['nullable', 'string', 'max:255', Rule::requiredIf($request->filled('paid_at'))],
                'amount' => ['nullable', 'numeric', 'min:0', Rule::requiredIf($request->filled('paid_at'))],
                'total_amount' => 'nullable|numeric|min:0',
            ]);

            $primaryGuestIndex = (int) ($request->input('primary_guest', 0));
            $guestPayloads = $request->input('guests', []);
            $services = $request->input('services', []);
            $serviceQtyMap = $request->input('service_qty', []);

            DB::transaction(function () use ($booking, $validated, $primaryGuestIndex, $guestPayloads, $services, $serviceQtyMap, $request) {
                $booking->update([
                    'check_in_date' => $validated['check_in_date'],
                    'check_out_date' => $validated['check_out_date'],
                    'no_of_guests' => $validated['no_of_guests'],
                    'no_of_rooms' => $validated['no_of_rooms'],
                    'agent_id' => $validated['agent_id'] ?? null,
                    'remark' => $validated['remark'] ?? null,
                    'base_amount' => $booking->base_amount ?: 0,
                    'total_amount' => $request->filled('total_amount') ? (float) $validated['total_amount'] : $booking->total_amount,
                    'status' => in_array($booking->status, ['pending', 'confirmed']) ? 'confirmed' : $booking->status,
                ]);

                $processedGuestIds = [];

                if (!empty($guestPayloads)) {
                    foreach ($guestPayloads as $index => $guestData) {
                        $guest = GuestService::createOrUpdateGuest([
                            'name' => $guestData['name'] ?? null,
                            'mobile' => $guestData['mobile'] ?? null,
                            'email' => $guestData['email'] ?? null,
                            'address' => $guestData['address'] ?? null,
                            'dob' => $guestData['dob'] ?? null,
                            'country_id' => $guestData['country_id'] ?? null,
                            'state_id' => $guestData['state_id'] ?? null,
                            'id_proofs' => $guestData['id_proofs'] ?? [],
                            'is_primary' => ((string) $primaryGuestIndex === (string) $index),
                        ], $booking->hotel_id);

                        $bookingGuest = $booking->bookingGuests()->firstOrNew([
                            'guest_id' => $guest->id,
                        ]);

                        $bookingGuest->fill([
                            'booking_id' => $booking->id,
                            'guest_id' => $guest->id,
                            'full_name' => $guest->full_name,
                            'mobile' => $guest->mobile,
                            'email' => $guest->email,
                            'address' => $guest->address,
                            'dob' => $guest->dob,
                            'country_id' => $guest->country_id,
                            'state_id' => $guest->state_id,
                            'is_primary_guest' => ((string) $primaryGuestIndex === (string) $index),
                        ]);
                        $bookingGuest->save();

                        $processedGuestIds[] = $bookingGuest->id;

                        $deletedProofIds = array_map('intval', $request->input("guests.$index.deleted_proof_ids", []));
                        $deletedProofIds = array_values(array_filter(array_unique($deletedProofIds)));

                        if (!empty($deletedProofIds)) {
                            $bookingGuest->idProofs()->whereIn('id', $deletedProofIds)->get()->each(function ($proof) {
                                if ($proof->id_file_path && Storage::disk('public')->exists($proof->id_file_path)) {
                                    Storage::disk('public')->delete($proof->id_file_path);
                                }
                                $proof->delete();
                            });
                        }

                        $incomingProofs = $request->input("guests.$index.id_proofs", []);
                        $keptProofIds = [];

                        foreach ($incomingProofs as $proofIndex => $proofData) {
                            $idType = trim((string) ($proofData['id_type'] ?? ''));
                            $proofId = isset($proofData['proof_id']) ? (int) $proofData['proof_id'] : null;

                            if ($proofId) {
                                $existingProof = $bookingGuest->idProofs()->find($proofId);
                                if (!$existingProof) {
                                    continue;
                                }

                                $keptProofIds[] = $existingProof->id;

                                $updates = [
                                    'id_type' => $idType !== '' ? $idType : $existingProof->id_type,
                                    'id_number' => $proofData['id_number'] ?? $existingProof->id_number,
                                ];

                                if (!empty($proofData['id_file']) && $proofData['id_file'] instanceof \Illuminate\Http\UploadedFile) {
                                    if ($existingProof->id_file_path && Storage::disk('public')->exists($existingProof->id_file_path)) {
                                        Storage::disk('public')->delete($existingProof->id_file_path);
                                    }

                                    $folder = "booking_guest_ids/{$booking->hotel_id}/{$bookingGuest->id}";
                                    $updates['id_file_path'] = $proofData['id_file']->store($folder, 'public');
                                }

                                $existingProof->update($updates);
                                continue;
                            }

                            if (empty($proofData['id_file']) && $idType === '') {
                                continue;
                            }

                            if (empty($proofData['id_file']) || !($proofData['id_file'] instanceof \Illuminate\Http\UploadedFile)) {
                                continue;
                            }

                            $folder = "booking_guest_ids/{$booking->hotel_id}/{$bookingGuest->id}";
                            $path = $proofData['id_file']->store($folder, 'public');

                            $newProof = BookingGuestIdProof::create([
                                'booking_guest_id' => $bookingGuest->id,
                                'id_type' => $idType,
                                'id_number' => $proofData['id_number'] ?? null,
                                'id_file_path' => $path,
                            ]);

                            $keptProofIds[] = $newProof->id;
                        }

                        if (!empty($incomingProofs)) {
                            $keptProofIds = array_values(array_unique(array_filter($keptProofIds)));

                            $bookingGuest->idProofs()
                                ->whereNotIn('id', $keptProofIds)
                                ->get()
                                ->each(function ($proof) {
                                    if ($proof->id_file_path && Storage::disk('public')->exists($proof->id_file_path)) {
                                        Storage::disk('public')->delete($proof->id_file_path);
                                    }
                                    $proof->delete();
                                });
                        }
                    }
                }

                $booking->bookingGuests()
                    ->whereNotIn('id', $processedGuestIds)
                    ->get()
                    ->each(function ($guestRecord) {
                        $guestRecord->idProofs()->get()->each(function ($proof) {
                            if ($proof->id_file_path && Storage::disk('public')->exists($proof->id_file_path)) {
                                Storage::disk('public')->delete($proof->id_file_path);
                            }
                            $proof->delete();
                        });
                        $guestRecord->delete();
                    });

                $booking->additionalServices()->detach();

                if (!empty($services)) {
                    foreach ($services as $serviceId) {
                        $qty = (int) ($serviceQtyMap[$serviceId] ?? 1);
                        $service = AdditionalService::find($serviceId);

                        if (!$service) {
                            continue;
                        }

                        $booking->additionalServices()->attach($serviceId, [
                            'quantity' => max(1, $qty),
                            'amount' => (float) $service->cost * max(1, $qty),
                        ]);
                    }
                }

                if ($request->filled('amount') && (float) $request->input('amount') > 0) {
                    BookingPayment::create([
                        'booking_id' => $booking->id,
                        'hotel_id' => $booking->hotel_id,
                        'method' => $request->input('payment_method', 'cash'),
                        'amount' => (float) $request->input('amount'),
                        'paid_at' => $request->input('paid_at') ?? now(),
                        'transaction_reference' => $request->input('reference'),
                        'status' => 'paid',
                        'collected_by' => Auth::id(),
                    ]);
                }
            });

            return response()->json([
                'status' => true,
                'message' => 'Booking updated successfully',
                'data' => $booking->fresh(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Booking Update Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'booking_id' => $booking->id,
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to update booking',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking)
    {
        //
    }

    public function noShow(Request $request, $id)
    {
        try {

            $booking = Booking::findOrFail($id);

            if ($booking->actual_check_in_at) {
                throw new \Exception('Guest already checked in');
            }

            $booking->update([
                'status' => 'no_show'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Marked as no show'
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'status' => false,
                'message' => 'Failed to mark no show',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkOut(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            try {
                $booking = Booking::with(['roomDistributions', 'additionalServices'])->findOrFail($id);

                if (!$booking->actual_check_in_at) {
                    throw new \Exception('Guest not checked in');
                }

                if ($booking->actual_check_out_at) {
                    throw new \Exception('Guest already checked out');
                }

                // Handle custom checkout date and time
                $checkOutDate = $request->input('checkout_date');
                $checkOutTime = $request->input('checkout_time');
                
                if ($checkOutDate && $checkOutTime) {
                    $checkOutDateTime = Carbon::createFromFormat('Y-m-d H:i', $checkOutDate . ' ' . $checkOutTime);
                    $now = $checkOutDateTime;
                } else {
                    $now = now();
                }

                $chargeBreakdown = [];
                if ($booking->booking_type === 'hourly') {
                    $chargeBreakdown = PricingService::calculateActualHourlyCharge($booking, $now);
                    $booking->update([
                        'base_amount' => $chargeBreakdown['base_price'] + $chargeBreakdown['extra_amount'],
                        'cgst' => $chargeBreakdown['cgst'],
                        'sgst' => $chargeBreakdown['sgst'],
                        'igst' => $chargeBreakdown['igst'],
                        'total_amount' => $chargeBreakdown['total_amount'],
                        'actual_check_out_at' => $now,
                        'status' => 'checked_out',
                    ]);
                } else {
                    $booking->update([
                        'actual_check_out_at' => $now,
                        'status' => 'checked_out'
                    ]);
                }

                // Set rooms to 'dirty' (status=3) for housekeeping workflow, not directly available
                $roomIds = RoomDistribution::where('booking_id', $booking->id)
                    ->pluck('room_number_id');

                RoomNumber::whereIn('id', $roomIds)->update([
                    'status' => 3 // Dirty - requires cleaning before re-rental
                ]);

                // Log the check-out action
                \App\Models\AuditLog::create([
                    'user_id' => Auth::id() ?? 1, // Default to 1 in tests
                    'hotel_id' => $booking->hotel_id,
                    'module' => 'booking',
                    'action' => 'checkout',
                    'record_id' => $booking->id,
                    'description' => 'Guest checked out',
                    'old_value' => json_encode(['status' => 'checked_in', 'actual_check_out_at' => null]),
                    'new_value' => json_encode(['status' => 'checked_out', 'actual_check_out_at' => $now->toDateTimeString()]),
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Guest checked out successfully'
                ]);
            } catch (\Throwable $e) {
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                    'error' => 'Check-out failed'
                ], 500);
            }
        });
    }

    // public function checkIn(Request $request)
    // {
    //     return DB::transaction(function () use ($request) {
    //         try {
    //             $request->validate([
    //                 'booking_id' => 'required|exists:bookings,id',
    //                 'rooms'      => 'required|array|min:1'
    //             ]);

    //             if (empty($request->rooms)) {
    //                 throw new \Exception('Please select at least one room');
    //             }

    //             $booking = Booking::with([
    //                 'roomDistributions.roomNumber',
    //                 'roomDistributions.roomType'
    //             ])->findOrFail($request->booking_id);

    //             if ($booking->actual_check_in_at) {
    //                 throw new \Exception('Guest already checked in');
    //             }

    //             $roomsToAssign = array_values($request->rooms);

    //             // Group distributions by room_number (since there can be multiple per night)
    //             $distributionsByRoom = $booking->roomDistributions
    //                 ->groupBy('room_number_id')
    //                 ->values();

    //             if (count($roomsToAssign) !== $distributionsByRoom->count()) {
    //                 throw new \Exception('Room count mismatch. Expected ' . $distributionsByRoom->count() . ', got ' . count($roomsToAssign));
    //             }

    //             // Process each group of distributions (one per unique room)
    //             foreach ($distributionsByRoom as $index => $distributions) {
    //                 $selectedRoomId = $roomsToAssign[$index];
    //                 $selectedRoom = RoomNumber::findOrFail($selectedRoomId);

    //                 // Use the first distribution in the group as reference for validation
    //                 $distribution = $distributions->first();

    //                 // Validate selected room belongs to the same hotel and room type
    //                 if ($selectedRoom->hotel_id !== $booking->hotel_id) {
    //                     throw new \Exception("Room {$selectedRoom->room_number} does not belong to this hotel");
    //                 }

    //                 if ($selectedRoom->room_type_id !== $distribution->room_type_id) {
    //                     throw new \Exception("Room {$selectedRoom->room_number} is not of type {$distribution->roomType->name}");
    //                 }

    //                 // Check if selected room is available (status = 0)
    //                 if ($selectedRoom->status !== 0) {
    //                     throw new \Exception("Room {$selectedRoom->room_number} is not available for check-in");
    //                 }

    //                 $oldRoomId = $distribution->room_number_id;

    //                 // Check if the selected room is assigned to another current booking
    //                 $existingDistribution = RoomDistribution::where('hotel_id', $distribution->hotel_id)
    //                     ->where('room_type_id', $distribution->room_type_id)
    //                     ->where('room_number_id', $selectedRoomId)
    //                     ->whereHas('booking', function ($q) use ($distribution) {
    //                         $q->whereNull('actual_check_out_at') // only active guests
    //                             ->where('id', '!=', $distribution->booking_id);
    //                     })
    //                     ->lockForUpdate()
    //                     ->first();

    //                 // Swap only if the room is currently occupied by another booking
    //                 if ($existingDistribution && $oldRoomId) {
    //                     $existingDistribution->update([
    //                         'room_number_id' => $oldRoomId,
    //                         'is_room_assigned' => 0
    //                     ]);
    //                 }

    //                 // Assign the selected room to all distributions for this room
    //                 RoomDistribution::where('booking_id', $booking->id)
    //                     ->whereIn('id', $distributions->pluck('id'))
    //                     ->update([
    //                         'room_number_id'   => $selectedRoomId,
    //                         'is_room_assigned' => 1
    //                     ]);
    //             }

    //             // Update room status to 1 (Occupied) for all selected rooms
    //             RoomNumber::whereIn('id', $roomsToAssign)->update([
    //                 'status' => 1
    //             ]);

    //             // Update booking
    //             $booking->update([
    //                 'actual_check_in_at' => now(),
    //                 'status'             => 'checked_in'
    //             ]);

    //             // Log the check-in action
    //             \App\Models\AuditLog::create([
    //                 'user_id' => Auth::id() ?? 1, // Default to 1 in tests
    //                 'hotel_id' => $booking->hotel_id,
    //                 'module' => 'booking',
    //                 'action' => 'checkin',
    //                 'record_id' => $booking->id,
    //                 'description' => 'Guest checked in',
    //                 'old_value' => json_encode(['status' => 'pending', 'actual_check_in_at' => null]),
    //                 'new_value' => json_encode(['status' => 'checked_in', 'actual_check_in_at' => now()->toDateTimeString(), 'rooms' => $roomsToAssign]),
    //             ]);

    //             return response()->json([
    //                 'status'  => true,
    //                 'message' => 'Guest checked in successfully'
    //             ]);
    //         } catch (\Throwable $e) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => $e->getMessage(),
    //                 'error' => 'Check-in failed'
    //             ], 500);
    //         }
    //     });
    // }

    public function checkIn(Request $request)
    {
        try {
            $request->validate([
                'booking_id' => ['required', 'exists:bookings,id'],
                'rooms'      => ['required', 'array', 'min:1'],
                'rooms.*'    => ['required', 'integer', 'distinct', 'exists:room_numbers,id'],
            ]);

            return DB::transaction(function () use ($request) {

                // Get booking and lock it to prevent concurrent check-ins
                $booking = Booking::with([
                    'roomDistributions.roomNumber',
                    'roomDistributions.roomType'
                ])
                    ->whereKey($request->booking_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($booking->actual_check_in_at) {
                    throw new \Exception('Guest already checked in.');
                }

                // Handle custom check-in date and time
                $checkInDate = $request->input('checkin_date');
                $checkInTime = $request->input('checkin_time');
                
                if ($checkInDate && $checkInTime) {
                    $checkInDateTime = Carbon::createFromFormat('Y-m-d H:i', $checkInDate . ' ' . $checkInTime);
                    $checkInAt = $checkInDateTime;
                } else {
                    $checkInAt = now();
                }

                // Selected room IDs from the request
                $roomsToAssign = array_map('intval', $request->input('rooms', []));

                // Group the booking distributions by their original room
                $distributionsByRoom = $booking->roomDistributions
                    ->groupBy('room_number_id')
                    ->values();

                $requiredRooms = $distributionsByRoom->count();

                // The selected count must match the booking requirement exactly
                if (count($roomsToAssign) !== $requiredRooms) {
                    throw new \Exception(
                        "Please select exactly {$requiredRooms} room(s). You selected " .
                            count($roomsToAssign) . '.'
                    );
                }

                if ($requiredRooms === 0) {
                    throw new \Exception(
                        'No room distributions were found for this booking.'
                    );
                }

                // Lock selected rooms and verify they exist
                $selectedRooms = RoomNumber::whereIn('id', $roomsToAssign)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                if ($selectedRooms->count() !== count($roomsToAssign)) {
                    throw new \Exception(
                        'One or more selected rooms could not be found.'
                    );
                }

                // Process each room assignment
                foreach ($distributionsByRoom as $index => $distributions) {

                    $selectedRoomId = $roomsToAssign[$index];

                    $selectedRoom = $selectedRooms->get($selectedRoomId);

                    if (!$selectedRoom) {
                        throw new \Exception(
                            'The selected room could not be found.'
                        );
                    }

                    // Reference distribution for this room group
                    $distribution = $distributions->first();

                    // Validate hotel
                    if ((int) $selectedRoom->hotel_id !== (int) $booking->hotel_id) {
                        throw new \Exception(
                            "Room {$selectedRoom->room_number} does not belong to this hotel."
                        );
                    }

                    // Validate room type
                    if ((int) $selectedRoom->room_type_id !== (int) $distribution->room_type_id) {
                        $roomTypeName = optional($distribution->roomType)->name
                            ?? 'the required room type';

                        throw new \Exception(
                            "Room {$selectedRoom->room_number} is not of type {$roomTypeName}."
                        );
                    }

                    // Validate room status
                    if ((int) $selectedRoom->status !== 0) {
                        throw new \Exception(
                            "Room {$selectedRoom->room_number} is not available for check-in."
                        );
                    }

                    // Check every nightly distribution for a conflicting assignment.
                    // Exclude the current booking's own distribution records.
                    foreach ($distributions as $nightDistribution) {

                        $conflict = RoomDistribution::where(
                            'hotel_id',
                            $nightDistribution->hotel_id
                        )
                            ->where('room_type_id', $nightDistribution->room_type_id)
                            ->where('room_number_id', $selectedRoomId)
                            ->whereDate('allocation_date', $nightDistribution->date)
                            ->where('id', '!=', $nightDistribution->id)
                            ->where('booking_id', '!=', $booking->id)
                            ->lockForUpdate()
                            ->exists();

                        if ($conflict) {
                            throw new \Exception(
                                "Room {$selectedRoom->room_number} is already assigned on " .
                                    \Carbon\Carbon::parse($nightDistribution->date)->format('d-m-Y') .
                                    '. Please select another room.'
                            );
                        }
                    }

                    // Update the current booking's room distributions.
                    // Do not move another booking's distribution to the old room.
                    RoomDistribution::where('booking_id', $booking->id)
                        ->whereIn('id', $distributions->pluck('id'))
                        ->update([
                            'room_number_id'   => $selectedRoomId,
                            'is_room_assigned' => 1,
                        ]);
                }

                // Mark selected rooms as occupied
                RoomNumber::whereIn('id', $roomsToAssign)->update([
                    'status' => 1,
                ]);

                // Update booking check-in status
                $booking->update([
                    'actual_check_in_at' => $checkInAt,
                    'status'             => 'checked_in',
                ]);

                // Audit log
                \App\Models\AuditLog::create([
                    'user_id'    => Auth::id() ?? 1,
                    'hotel_id'   => $booking->hotel_id,
                    'module'     => 'booking',
                    'action'     => 'checkin',
                    'record_id'  => $booking->id,
                    'description' => 'Guest checked in',
                    'old_value'  => json_encode([
                        'status' => $booking->getOriginal('status'),
                        'actual_check_in_at' => null,
                    ]),
                    'new_value'  => json_encode([
                        'status' => 'checked_in',
                        'actual_check_in_at' => $checkInAt->toDateTimeString(),
                        'rooms' => $roomsToAssign,
                    ]),
                ]);

                return response()->json([
                    'status'  => true,
                    'message' => 'Guest checked in successfully',
                ]);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {

            \Illuminate\Support\Facades\Log::error('Check-in failed', [
                'booking_id' => $request->booking_id,
                'rooms'      => $request->rooms,
                'message'    => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
                'error'   => 'Check-in failed',
            ], 422);
        }
    }

    public function getAvailableRooms(Request $request, $bookingId)
    {
        try {
            $booking = Booking::with(['roomDistributions'])->findOrFail($bookingId);

            $hotelId     = $booking->hotel_id;
            $roomTypeIds = $booking->roomDistributions->pluck('room_type_id')->unique()->values();
            $checkIn     = $booking->check_in_date;
            $checkOut    = $booking->check_out_date;
            $noOfRooms   = $booking->no_of_rooms;

            if ($roomTypeIds->isEmpty()) {
                throw new \Exception('No room types found in booking');
            }

            // For availability check, we include the checkout date (guest occupies through checkout)
            $start = Carbon::parse($checkIn);
            $end = Carbon::parse($checkOut); // Include checkout date itself

            // Find rooms already allocated to OTHER active bookings during this period
            $allocatedRoomIds = RoomDistribution::where('hotel_id', $hotelId)
                ->whereIn('room_type_id', $roomTypeIds)
                ->where('booking_id', '!=', $bookingId) // Exclude current booking
                ->whereHas('booking', function ($q) {
                    $q->whereIn('status', ['pending', 'confirmed', 'checked_in']);
                })
                ->where('is_room_assigned', 1)
                ->whereBetween('allocation_date', [
                    $start->toDateString(),
                    $end->subDay()->toDateString() // Exclude checkout date itself (guest leaves that day)
                ])
                ->distinct()
                ->pluck('room_number_id');

            // Get available rooms that:
            // 1. Belong to the hotel
            // 2. Are of correct room type
            // 3. Are not allocated to other active bookings
            // 4. Are in available status (0) and not in maintenance/dirty/cleaning/blocked states
            $availableRooms = RoomNumber::where('hotel_id', $hotelId)
                ->whereIn('room_type_id', $roomTypeIds)
                ->whereNotIn('id', $allocatedRoomIds)
                ->where('status', 0) // Only available rooms (not occupied, maintenance, dirty, cleaning, blocked)
                ->get(['id', 'room_number', 'room_type_id']);

            // Group by room type for better UX
            $roomsByType = $availableRooms->groupBy('room_type_id');

            return response()->json([
                'status' => true,
                'rooms_by_type'  => $roomsByType,
                'rooms'  => $availableRooms,
                'rooms_required' => $noOfRooms,
                'available_count' => $availableRooms->count(),
                'booking_id' => $bookingId,
                'booking_code' => $booking->booking_code
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Unable to fetch available rooms',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // public function cancel(Request $request, Booking $booking)
    // {
    //     try {
    //         // Only allow cancel if not already checked-in or checked-out
    //         if (in_array($booking->status, ['checked_in', 'checked_out'])) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Cannot cancel a booking that is already checked in or checked out.'
    //             ]);
    //         }

    //         $booking->update([
    //             'status' => 'cancelled',
    //         ]);

    //         // Free up rooms
    //         foreach ($booking->roomDistributions as $distribution) {
    //             $distribution->update([
    //                 'is_room_assigned' => 0,
    //             ]);
    //         }

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Booking cancelled successfully.'
    //         ]);
    //     } catch (\Throwable $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Cancel failed.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function cancel(Request $request, $id)
    {
        try {
            // Only allow cancel if not already checked-in or checked-out
            $booking = Booking::findOrFail($id);
            if (in_array($booking->status, ['checked_in', 'checked_out'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cannot cancel a booking that is already checked in or checked out.'
                ]);
            }

            $request->validate([
                'reason' => 'nullable|string|max:500',
                'refund_amount' => 'nullable|numeric|min:0'
            ]);

            $result = BookingService::cancelBooking(
                $id,
                $request->reason ?? 'No reason provided',
                $request->refund_amount ?? null
            );

            return response()->json($result, 200);
        } catch (\Exception $e) {
            Log::error('Booking Cancel Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to cancel booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Book Map
    public function bookmap()
    {
        $additional_services = auth()->user()->hotel?->additionalServices()->where('status', 1)->get();
        $countries = Country::all();
        $states = State::all();
        $agents = Agent::where('hotel_id', auth()->user()->hotel_id)
            ->where('status', 1)
            ->get();
        $hotelRateTypes = auth()->user()->hotel?->getBookingRateOptions() ?? ['room'];
        $defaultHotelRateType = auth()->user()->hotel?->getDefaultBookingRateType() ?? 'room';

        $bookings = Booking::with('roomDistributions.roomNumber', 'primaryGuest', 'roomDistributions.roomType','payments','additionalServices')
            ->where('hotel_id', auth()->user()->hotel_id)
            ->get();
        $roomTypes = RoomType::with('roomNumbers')
            ->where('hotel_id', auth()->user()->hotel_id)
            ->has('roomNumbers') // ✅ ONLY roomTypes having rooms
            ->get();
        return view('pages.bookings.bookmap.index', compact('additional_services', 'countries', 'states', 'agents', 'hotelRateTypes', 'defaultHotelRateType', 'roomTypes', 'bookings'));
    }

    /**
     * Change a guest's room number.
     * 
     * @param Request $request
     * @param int $bookingId
     * @return JSON response
     */
    public function changeRoom(Request $request, $bookingId)
    {
        try {
            $request->validate([
                'new_room_id' => 'required|exists:room_numbers,id',
                'old_room_id' => 'nullable|exists:room_numbers,id',
                'reason' => 'nullable|string|max:500'
            ]);

            $result = RoomChangeService::changeRoomNumber(
                $bookingId,
                $request->new_room_id,
                $request->reason ?? '',
                $request->old_room_id ?? null
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Change Room Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to change room',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change a guest's room type from a specified date.
     * 
     * @param Request $request
     * @param int $bookingId
     * @return JSON response
     */
    public function changeRoomType(Request $request, $bookingId)
    {
        try {
            $request->validate([
                'new_room_type_id' => 'required|exists:room_types,id',
                'effective_date' => 'required|date_format:Y-m-d',
                'new_room_id' => 'nullable|exists:room_numbers,id',
                'old_room_id' => 'nullable|exists:room_numbers,id',
                'reason' => 'nullable|string|max:500'
            ]);

            $result = RoomChangeService::changeRoomType(
                $bookingId,
                $request->new_room_type_id,
                $request->effective_date,
                $request->new_room_id ?? null,
                $request->reason ?? '',
                $request->old_room_id ?? null
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Change Room Type Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to change room type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available rooms for replacement.
     * 
     * @param Request $request
     * @param int $bookingId
     * @return JSON response
     */
    public function getReplacementRooms(Request $request, $bookingId)
    {
        try {
            $result = RoomChangeService::getAvailableReplacementRooms(
                $bookingId,
                $request->old_room_id ?? null,
                $request->room_type_id ?? null
            );

            return response()->json([
                'status' => true,
                ...$result
            ]);
        } catch (\Exception $e) {
            Log::error('Get Replacement Rooms Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch replacement rooms',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
