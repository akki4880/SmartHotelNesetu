<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\RoomType;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\HourlyRate;
use App\Models\RoomDistribution;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AvailabilityController extends Controller
{
    public function search(Request $request)
    {
        try {
            $rateType = $request->rate_type ?? 'room';

            $request->validate([
                'check_in_date' => 'required|date',
                'check_out_date' => [
                    'required',
                    'date',
                    Rule::when($rateType === 'hourly', 'after_or_equal:check_in_date', 'after:check_in_date')
                ],
                'no_of_guests' => 'required|integer|min:1',
                'no_of_rooms'  => 'required|integer|min:1',
                'rate_type'    => 'nullable|in:room,hourly',
                'check_in_time' => ['nullable', 'date_format:H:i', Rule::requiredIf($rateType === 'hourly')],
                'check_out_time' => ['nullable', 'date_format:H:i', Rule::requiredIf($rateType === 'hourly')],
            ]);

            $checkIn        = Carbon::parse($request->check_in_date);
            $checkOut       = Carbon::parse($request->check_out_date);
            $lastNightDate  = $checkOut->copy()->eq($checkIn) ? $checkIn->copy() : $checkOut->copy()->subDay();
            $noOfGuests     = (int) $request->no_of_guests;
            $roomsRequired  = (int) $request->no_of_rooms;

            $hotel = Hotel::findOrFail(Auth::user()->hotel_id);

            if ($rateType === 'hourly') {
                $checkInDateTime = Carbon::parse($request->check_in_date . ' ' . ($request->check_in_time ?? '00:00'));
                $checkOutDateTime = Carbon::parse($request->check_out_date . ' ' . ($request->check_out_time ?? '00:00'));

                $sameDayStay = Carbon::parse($request->check_in_date)->eq(Carbon::parse($request->check_out_date));

                if ($sameDayStay && $checkOutDateTime->lessThanOrEqualTo($checkInDateTime)) {
                    throw new \Exception('Check-out time must be after check-in time.');
                }

                $roomTypes = RoomType::with(['roomNumbers'])
                    ->where('status', 1)
                    ->get();

                $availability = [];

                foreach ($roomTypes as $roomType) {
                    $hourlyRate = \App\Models\HourlyRate::where('hotel_id', $hotel->id)
                        ->where('room_type_id', $roomType->id)
                        ->where('status', 1)
                        ->first();

                    if (!$hourlyRate) {
                        continue;
                    }

                    $baseGuests = (int) $hourlyRate->base_guest_count;
                    $maxGuests = $baseGuests + (int) ($hourlyRate->max_extra_guests ?? 0);

                    if ($noOfGuests > $maxGuests) {
                        continue;
                    }

                    $totalRooms = $roomType->roomNumbers->count();
                    $readyRooms = $roomType->roomNumbers
                        ->where('status', 0)
                        ->count();
                    $dirtyRoomNumbers = $roomType->roomNumbers
                        ->where('status', 3)
                        ->pluck('room_number')
                        ->values()
                        ->all();
                    $dirtyRooms = count($dirtyRoomNumbers);

                    $bookedRooms = RoomDistribution::where('hotel_id', $hotel->id)
                        ->where('room_type_id', $roomType->id)
                        ->whereHas('booking', function ($q) {
                            $q->whereIn('status', ['pending', 'confirmed', 'checked_in']);
                        })
                        ->whereBetween('allocation_date', [
                            $checkIn->toDateString(),
                            $lastNightDate->toDateString(),
                        ])
                        ->count();

                    $availableRooms = max($readyRooms - $bookedRooms, 0);

                    $hoursUsed = max(1, (int) ceil($checkInDateTime->diffInMinutes($checkOutDateTime) / 60));
                    $baseHours = (int) $hourlyRate->base_hours;
                    $extraHours = max(0, $hoursUsed - $baseHours);
                    $totalPrice = (float) $hourlyRate->base_price + ((float) $hourlyRate->extra_hour_price * $extraHours);
                    $totalPrice *= (int) $roomsRequired;

                    $availability[] = [
                        'room_type_id' => $roomType->id,
                        'room_type' => $roomType->name,
                        'description' => $roomType->description,
                        'total_rooms' => $totalRooms,
                        'available' => $availableRooms,
                        'dirty_rooms' => $dirtyRooms,
                        'booked_rooms' => $bookedRooms,
                        'dirty_room_numbers' => $dirtyRoomNumbers,
                        'rooms_required' => $roomsRequired,
                        'base_guests' => $baseGuests,
                        'extra_allowed' => (int) ($hourlyRate->max_extra_guests ?? 0),
                        'extra_used' => max(0, $noOfGuests - $baseGuests),
                        'price_breakup' => [
                            'base_hours' => $baseHours,
                            'hours_used' => $hoursUsed,
                            'base_price' => (float) $hourlyRate->base_price,
                            'extra_hour_price' => (float) $hourlyRate->extra_hour_price,
                            'extra_hours' => $extraHours,
                        ],
                        'total_price' => round($totalPrice, 2),
                        'daily_prices' => [],
                    ];
                }

                return response()->json([
                    'status' => true,
                    'data' => $availability
                ]);
            }

            $roomTypes = RoomType::with([
                'roomNumbers',
                'roomRate',
                'seasonalRates'
            ])
                ->where('status', 1)
                ->get();

            $availability = [];

            foreach ($roomTypes as $roomType) {

                /* ---------------- GUEST CAPACITY ---------------- */

                if (!$roomType->roomRate) {
                    continue;
                }

                $baseGuests   = $roomType->roomRate->base_guest_count;
                $extraAllowed = $roomType->roomRate->max_extra_guests;
                $maxGuests    = $baseGuests + $extraAllowed;

                if ($noOfGuests > $maxGuests) {
                    continue;
                }

                $extraGuests = max(0, $noOfGuests - $baseGuests);

                /* ---------------- ROOM AVAILABILITY ---------------- */

                $totalRooms = $roomType->roomNumbers->count();
                $readyRooms = $roomType->roomNumbers
                    ->where('status', 0)
                    ->count();
                $dirtyRoomNumbers = $roomType->roomNumbers
                    ->where('status', 3)
                    ->pluck('room_number')
                    ->values()
                    ->all();
                $dirtyRooms = count($dirtyRoomNumbers);

                $bookedRooms = RoomDistribution::where('hotel_id', $hotel->id)
                    ->where('room_type_id', $roomType->id)
                    ->whereHas('booking', function ($q) {
                        $q->whereIn('status', [
                            'pending',
                            'confirmed',
                            'checked_in'
                        ]);
                    })
                    ->whereBetween('allocation_date', [
                        $checkIn->toDateString(),
                        $lastNightDate->toDateString()
                    ])
                    ->count();

                $availableRooms = max($readyRooms - $bookedRooms, 0);

                /* ---------------- PRICE CALCULATION ---------------- */

                $priceBreakup = [];
                $roomTotal    = 0;
                $extraTotal   = 0;

                $extraGuestPrice = $roomType->roomRate->extra_guest_price ?? 0;

                $date = $checkIn->copy();

                while ($date->lte($checkOut)) {

                    $rateInfo = $roomType->getRateForDate($date, $hotel);

                    $roomTotal += $rateInfo['price'];

                    if ($extraGuests > 0) {
                        $extraTotal += ($extraGuests * $extraGuestPrice);
                    }

                    $priceBreakup[$date->toDateString()] = [
                        'price' => $rateInfo['price'],
                        'type'  => $rateInfo['type']
                    ];

                    $date->addDay();
                }

                // multiply by rooms
                $finalRoomTotal  = $roomTotal * $roomsRequired;
                $finalExtraTotal = $extraTotal * $roomsRequired;

                $availability[] = [
                    'room_type_id' => $roomType->id,
                    'room_type'    => $roomType->name,
                    'description'  => $roomType->description,

                    'total_rooms' => $totalRooms,
                    'available'   => $availableRooms,
                    'dirty_rooms' => $dirtyRooms,
                    'booked_rooms' => $bookedRooms,
                    'dirty_room_numbers' => $dirtyRoomNumbers,
                    'rooms_required' => $roomsRequired,

                    'base_guests' => $baseGuests,
                    'extra_allowed' => $extraAllowed,
                    'extra_used' => $extraGuests,

                    'price_breakup' => [
                        'room_total'  => $finalRoomTotal,
                        'extra_total' => $finalExtraTotal
                    ],

                    'total_price' => $finalRoomTotal + $finalExtraTotal,
                    'daily_prices' => $priceBreakup
                ];
            }

            return response()->json([
                'status' => true,
                'data'   => $availability
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch availability',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
