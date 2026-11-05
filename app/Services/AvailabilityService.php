<?php

namespace App\Services;

use App\Models\RoomDistribution;
use App\Models\RoomNumber;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AvailabilityService
{
    /**
     * Check room availability with proper validation.
     *
     * @param int $hotelId
     * @param int $roomTypeId
     * @param Carbon|string $checkIn
     * @param Carbon|string $checkOut
     * @param int $roomsRequired
     * @param int|null $excludeBookingId - Booking ID to exclude from check (for updates/changes)
     * @return array ['available' => int, 'required' => int, 'message' => string]
     * @throws \Exception
     */
    public static function validateAvailability(
        $hotelId,
        $roomTypeId,
        $checkIn,
        $checkOut,
        $roomsRequired,
        $excludeBookingId = null,
        $allowSameDay = false
    ) {
        $start = Carbon::parse($checkIn);
        $end = Carbon::parse($checkOut);

        if ($end->lt($start) || ($end->eq($start) && !$allowSameDay)) {
            throw new \Exception('Check-out date must be after check-in date');
        }

        // Same-day stays still occupy one day in the room allocation records.
        $lastNightDate = $end->copy()->eq($start) ? $start->copy() : $end->copy()->subDay();

        // Get total rooms that are operationally available (not in maintenance, dirty, etc.)
        $totalOperationalRooms = RoomNumber::where([
            'hotel_id' => $hotelId,
            'room_type_id' => $roomTypeId,
        ])
            ->whereIn('status', [0]) // 0=Available only; exclude 1=Occupied, 2=Maintenance, 3=Dirty, 4=Cleaning, 5=Blocked
            ->count();

        if ($totalOperationalRooms === 0) {
            throw new \Exception('No rooms available of this type');
        }

        // Find available rooms (those without conflicting allocations)
        $availableRoomIds = self::findAvailableRooms(
            $hotelId,
            $roomTypeId,
            $start->toDateString(),
            $lastNightDate->toDateString(),
            $excludeBookingId
        );

        $availableCount = count($availableRoomIds);

        if ($availableCount < $roomsRequired) {
            throw new \Exception(
                "Only {$availableCount} room(s) available; {$roomsRequired} requested"
            );
        }

        return [
            'available' => $availableCount,
            'required' => $roomsRequired,
            'total_rooms' => $totalOperationalRooms,
            'message' => 'Availability validated'
        ];
    }

    /**
     * Find list of available room IDs for a date range (with database locking).
     *
     * @param int $hotelId
     * @param int $roomTypeId
     * @param string $checkInDate (YYYY-MM-DD)
     * @param string $lastNightDate (YYYY-MM-DD) - last night of stay
     * @param int|null $excludeBookingId - exclude allocations of this booking
     * @return array Room IDs that are available
     */
    public static function findAvailableRooms(
        $hotelId,
        $roomTypeId,
        $checkInDate,
        $lastNightDate,
        $excludeBookingId = null
    ) {
        // Use database transaction to ensure consistency
        return DB::transaction(function () use ($hotelId, $roomTypeId, $checkInDate, $lastNightDate, $excludeBookingId) {
            // Lock rows for consistency - only rooms that are operationally available
            $allRoomsInType = RoomNumber::where([
                'hotel_id' => $hotelId,
                'room_type_id' => $roomTypeId,
            ])
                ->whereIn('status', [0]) // 0=Available only; exclude maintenance, dirty, cleaning, blocked
                ->lockForUpdate()
                ->pluck('id');

            $availableRooms = [];

            foreach ($allRoomsInType as $roomId) {
                $hasConflict = RoomDistribution::where([
                    'hotel_id' => $hotelId,
                    'room_type_id' => $roomTypeId,
                    'room_number_id' => $roomId,
                ])
                    ->whereBetween('allocation_date', [$checkInDate, $lastNightDate])
                    ->whereHas('booking', function ($q) {
                        // Only active/pending bookings block availability
                        $q->whereIn('status', [
                            'pending',
                            'confirmed',
                            'checked_in'
                        ]);
                    })
                    ->when($excludeBookingId, function ($q) use ($excludeBookingId) {
                        // Exclude allocations belonging to the specified booking
                        $q->where('booking_id', '!=', $excludeBookingId);
                    })
                    ->exists();

                if (!$hasConflict) {
                    $availableRooms[] = $roomId;
                }
            }

            return $availableRooms;
        });
    }

    /**
     * Get available room count for a date range (fast check).
     *
     * @param int $hotelId
     * @param int $roomTypeId
     * @param Carbon|string $checkIn
     * @param Carbon|string $checkOut
     * @param int|null $excludeBookingId
     * @return int
     */
    public static function getAvailableCount(
        $hotelId,
        $roomTypeId,
        $checkIn,
        $checkOut,
        $excludeBookingId = null
    ) {
        $start = Carbon::parse($checkIn);
        $end = Carbon::parse($checkOut);
        $lastNightDate = $end->copy()->eq($start) ? $start->copy() : $end->copy()->subDay();

        $availableRooms = self::findAvailableRooms(
            $hotelId,
            $roomTypeId,
            $start->toDateString(),
            $lastNightDate->toDateString(),
            $excludeBookingId
        );

        return count($availableRooms);
    }

    /**
     * Get rooms that will conflict with a booking (for change/update operations).
     *
     * @param int $hotelId
     * @param int $roomTypeId
     * @param Carbon|string $checkIn
     * @param Carbon|string $checkOut
     * @param int|null $excludeBookingId
     * @return array List of room numbers that conflict
     */
    public static function getConflictingRooms(
        $hotelId,
        $roomTypeId,
        $checkIn,
        $checkOut,
        $excludeBookingId = null
    ) {
        $start = Carbon::parse($checkIn);
        $end = Carbon::parse($checkOut);
        $lastNightDate = $end->copy()->eq($start) ? $start->copy() : $end->copy()->subDay();

        $conflictingRoomIds = RoomDistribution::where([
            'hotel_id' => $hotelId,
            'room_type_id' => $roomTypeId,
        ])
            ->whereBetween('allocation_date', [$start->toDateString(), $lastNightDate->toDateString()])
            ->whereHas('booking', function ($q) {
                $q->whereIn('status', ['pending', 'confirmed', 'checked_in']);
            })
            ->when($excludeBookingId, function ($q) use ($excludeBookingId) {
                $q->where('booking_id', '!=', $excludeBookingId);
            })
            ->distinct()
            ->pluck('room_number_id');

        return RoomNumber::whereIn('id', $conflictingRoomIds)
            ->pluck('room_number')
            ->toArray();
    }
}
