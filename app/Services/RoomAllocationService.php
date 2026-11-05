<?php

namespace App\Services;

use App\Models\RoomNumber;
use App\Models\RoomDistribution;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RoomAllocationService
{
    /**
     * Allocate rooms to a booking with database transaction safety.
     *
     * @param int $hotelId
     * @param int $bookingId
     * @param int $roomTypeId
     * @param int $roomsRequired
     * @param Carbon|string $checkIn
     * @param Carbon|string $checkOut
     * @param int|null $excludeBookingId - Booking ID to exclude from conflict check
     * @throws \Exception
     */
    public static function allocateRooms(
        $hotelId,
        $bookingId,
        $roomTypeId,
        $roomsRequired,
        $checkIn,
        $checkOut,
        $excludeBookingId = null
    ) {
        $start = Carbon::parse($checkIn);
        $end = Carbon::parse($checkOut);

        if ($end->lt($start)) {
            throw new \Exception('Check-out date must be after check-in date');
        }

        $lastNightDate = $end->copy()->eq($start) ? $start->copy() : $end->copy()->subDay();

        DB::transaction(function () use (
            $hotelId,
            $bookingId,
            $roomTypeId,
            $roomsRequired,
            $start,
            $lastNightDate,
            $excludeBookingId
        ) {
            // Get available rooms with database-level locking
            $availableRooms = RoomNumber::where('hotel_id', $hotelId)
                ->where('room_type_id', $roomTypeId)
                ->where('status', 0) // 0=Available
                ->orderByRaw('CAST(room_number AS UNSIGNED) ASC')
                ->lockForUpdate()
                ->get();

            // Filter in transaction to confirm no conflicts exist
            $eligibleRooms = [];

            foreach ($availableRooms as $room) {
                $hasConflict = RoomDistribution::where([
                    'hotel_id' => $hotelId,
                    'room_type_id' => $roomTypeId,
                    'room_number_id' => $room->id,
                ])
                    ->whereBetween('allocation_date', [
                        $start->toDateString(),
                        $lastNightDate->toDateString(),
                    ])
                    ->whereHas('booking', function ($q) {
                        $q->whereIn('status', [
                            'pending',
                            'confirmed',
                            'checked_in',
                        ]);
                    })
                    ->when($excludeBookingId, function ($q) use ($excludeBookingId) {
                        $q->where('booking_id', '!=', $excludeBookingId);
                    })
                    ->exists();

                if (!$hasConflict) {
                    $eligibleRooms[] = $room;
                }

                if (count($eligibleRooms) >= $roomsRequired) {
                    break;
                }
            }

            if (count($eligibleRooms) < $roomsRequired) {
                throw new \Exception(
                    'Insufficient available rooms after locking. '
                    . count($eligibleRooms) . ' found, ' . $roomsRequired . ' required.'
                );
            }

            // Allocate rooms (one RoomDistribution per night)
            foreach ($eligibleRooms as $room) {
                $date = $start->copy();

                while ($date->lte($lastNightDate)) {
                    RoomDistribution::create([
                        'hotel_id' => $hotelId,
                        'booking_id' => $bookingId,
                        'room_type_id' => $roomTypeId,
                        'room_number_id' => $room->id,
                        'allocation_date' => $date->toDateString(),
                        'is_room_assigned' => 1,
                    ]);

                    $date->addDay();
                }
            }
        });
    }

    /**
     * Release room allocations for a booking (on cancellation or change).
     *
     * @param int $bookingId
     * @param int|null $roomDistributionIdToExclude - if updating specific allocation
     */
    public static function releaseAllocations(
        $bookingId,
        $roomDistributionIdToExclude = null
    ) {
        $query = RoomDistribution::where('booking_id', $bookingId);

        if ($roomDistributionIdToExclude) {
            $query->where('id', '!=', $roomDistributionIdToExclude);
        }

        $query->delete();
    }

    /**
     * Release allocations for a specific date range (for partial room type changes).
     *
     * @param int $bookingId
     * @param string $fromDate (YYYY-MM-DD)
     * @param string $toDate (YYYY-MM-DD, inclusive)
     */
    public static function releaseAllocationsForDateRange(
        $bookingId,
        $fromDate,
        $toDate
    ) {
        RoomDistribution::where('booking_id', $bookingId)
            ->whereBetween('allocation_date', [$fromDate, $toDate])
            ->delete();
    }

    /**
     * Update room number for specific allocation dates (e.g., on check-in or room change).
     *
     * @param int $bookingId
     * @param int $oldRoomNumberId
     * @param int $newRoomNumberId
     * @param string|null $fromDate - if null, update all dates
     * @param string|null $toDate
     */
    public static function updateRoomNumberForDates(
        $bookingId,
        $oldRoomNumberId,
        $newRoomNumberId,
        $fromDate = null,
        $toDate = null
    ) {
        $query = RoomDistribution::where('booking_id', $bookingId)
            ->where('room_number_id', $oldRoomNumberId);

        if ($fromDate && $toDate) {
            $query->whereBetween('allocation_date', [$fromDate, $toDate]);
        }

        $query->update([
            'room_number_id' => $newRoomNumberId,
        ]);
    }
}
