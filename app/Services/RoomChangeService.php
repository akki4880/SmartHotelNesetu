<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingRoomTypeChange;
use App\Models\RoomDistribution;
use App\Models\RoomNumber;
use App\Models\RoomType;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RoomChangeService
{
    /**
     * Resolve the current room assignment that should be changed.
     *
     * For single-room bookings, the selected room is inferred automatically.
     * For multi-room bookings, the caller must choose the specific room using `old_room_id`.
     */
    private static function resolveSelectedRoomAllocations(Booking $booking, $oldRoomNumberId = null)
    {
        $allAllocations = $booking->roomDistributions
            ->filter(fn ($distribution) => !empty($distribution->room_number_id))
            ->values();

        if ($allAllocations->isEmpty()) {
            throw new \Exception('No room allocations found for this booking');
        }

        $grouped = $allAllocations->groupBy('room_number_id');
        $selectedRoomId = $oldRoomNumberId ? (int) $oldRoomNumberId : null;

        if (!$selectedRoomId) {
            if ($grouped->count() > 1) {
                throw new \Exception('Please select the specific room you want to change.');
            }

            $selectedRoomId = (int) $grouped->keys()->first();
        }

        $groupKey = (string) $selectedRoomId;

        if (!$grouped->has($groupKey)) {
            throw new \Exception('The selected room is not assigned to this booking.');
        }

        $selectedAllocations = $grouped->get($groupKey);

        return [
            'room_id' => $selectedRoomId,
            'allocations' => $selectedAllocations,
            'old_room' => $selectedAllocations->first()->roomNumber,
        ];
    }

    /**
     * Change a guest's room number before or during a stay.
     *
     * @param int $bookingId
     * @param int $newRoomNumberId The new physical room to assign
     * @param string $reason Optional reason for the change
     * @param int|null $oldRoomNumberId The currently selected room in a multi-room booking
     * @return array ['success' => bool, 'message' => string, 'old_room' => RoomNumber, 'new_room' => RoomNumber]
     * @throws \Exception
     */
    public static function changeRoomNumber($bookingId, $newRoomNumberId, $reason = '', $oldRoomNumberId = null)
    {
        return DB::transaction(function () use ($bookingId, $newRoomNumberId, $reason, $oldRoomNumberId) {
            $booking = Booking::with('roomDistributions')->findOrFail($bookingId);

            if (!in_array($booking->status, ['pending', 'confirmed', 'checked_in'])) {
                throw new \Exception('Cannot change room for booking with status: ' . $booking->status);
            }

            if (auth()->check() && $booking->hotel_id !== auth()->user()->hotel_id) {
                throw new \Exception('Unauthorized: Booking does not belong to your hotel');
            }

            $selectedRoom = self::resolveSelectedRoomAllocations($booking, $oldRoomNumberId);
            $selectedRoomId = $selectedRoom['room_id'];
            $selectedAllocations = $selectedRoom['allocations'];
            $oldRoom = $selectedRoom['old_room'];

            $newRoom = RoomNumber::findOrFail($newRoomNumberId);

            if ($newRoom->hotel_id !== $booking->hotel_id) {
                throw new \Exception('New room does not belong to this booking\'s hotel');
            }

            $requiredTypeId = $selectedAllocations->first()->room_type_id;

            if ((int) $newRoom->room_type_id !== (int) $requiredTypeId) {
                throw new \Exception(
                    "New room is type '{$newRoom->roomType->name}' but booking requires '{$selectedAllocations->first()->roomType->name}'"
                );
            }

            $allocationDates = $selectedAllocations
                ->pluck('allocation_date')
                ->filter()
                ->unique()
                ->sort()
                ->values();

            if ($allocationDates->isEmpty()) {
                throw new \Exception('No dates found for the selected room assignment.');
            }

            if ((int) $newRoom->id === (int) $selectedRoomId) {
                return [
                    'success' => true,
                    'message' => "Room {$oldRoom?->room_number} is already assigned.",
                    'old_room' => $oldRoom,
                    'new_room' => $newRoom,
                ];
            }

            if ((int) $newRoom->status !== 0) {
                throw new \Exception("Room {$newRoom->room_number} is not available (status: {$newRoom->status})");
            }

            self::validateRoomAvailabilityForBooking(
                $booking->hotel_id,
                $newRoom->id,
                $allocationDates->first(),
                $allocationDates->last(),
                $bookingId
            );

            if ($oldRoom && $oldRoom->id) {
                RoomNumber::where('id', $oldRoom->id)->update(['status' => 0]);
            }

            RoomDistribution::where('booking_id', $bookingId)
                ->where('room_number_id', $selectedRoomId)
                ->update([
                    'room_number_id' => $newRoom->id,
                    'is_room_assigned' => ($booking->status === 'checked_in') ? 1 : 0,
                ]);

            if ($booking->status === 'checked_in') {
                $newRoom->update(['status' => 1]);
            }

            AuditLog::create([
                'user_id' => Auth::id() ?? 1,
                'hotel_id' => $booking->hotel_id,
                'module' => 'booking',
                'action' => 'change_room',
                'record_id' => $booking->id,
                'description' => $reason ?: 'Room change requested',
                'old_value' => json_encode([
                    'room_number_id' => $selectedRoomId,
                    'room_number' => $oldRoom?->room_number,
                ]),
                'new_value' => json_encode([
                    'room_number_id' => $newRoom->id,
                    'room_number' => $newRoom->room_number,
                ]),
            ]);

            return [
                'success' => true,
                'message' => "Room changed from {$oldRoom?->room_number} to {$newRoom->room_number}",
                'old_room' => $oldRoom,
                'new_room' => $newRoom,
            ];
        });
    }

    /**
     * Change room type for a booking effective from a specified date.
     * Used for mid-stay upgrades/downgrades.
     *
     * @param int $bookingId
     * @param int $newRoomTypeId The new room type
     * @param string $effectiveDate Date from which change applies (YYYY-MM-DD)
     * @param int|null $newRoomNumberId Optional: specific room to assign (if different from current)
     * @param string $reason Optional reason for the change
     * @return array ['success' => bool, 'message' => string, 'old_type' => RoomType, 'new_type' => RoomType]
     * @throws \Exception
     */
    public static function changeRoomType(
        $bookingId,
        $newRoomTypeId,
        $effectiveDate,
        $newRoomNumberId = null,
        $reason = '',
        $oldRoomNumberId = null
    ) {
        return DB::transaction(function () use ($bookingId, $newRoomTypeId, $effectiveDate, $newRoomNumberId, $reason, $oldRoomNumberId) {
            $booking = Booking::with('roomDistributions')->findOrFail($bookingId);
            $effectiveDate = Carbon::parse($effectiveDate);

            if (!in_array($booking->status, ['pending', 'confirmed', 'checked_in'])) {
                throw new \Exception('Cannot change room type for booking with status: ' . $booking->status);
            }

            if (auth()->check() && $booking->hotel_id !== auth()->user()->hotel_id) {
                throw new \Exception('Unauthorized: Booking does not belong to your hotel');
            }

            $checkIn = Carbon::parse($booking->check_in_date);
            $checkOut = Carbon::parse($booking->check_out_date);

            if ($effectiveDate->lt($checkIn) || $effectiveDate->gte($checkOut)) {
                throw new \Exception(
                    "Effective date must be between {$checkIn->toDateString()} and " .
                        $checkOut->copy()->subDay()->toDateString()
                );
            }

            $selectedRoom = self::resolveSelectedRoomAllocations($booking, $oldRoomNumberId);
            $selectedRoomId = $selectedRoom['room_id'];
            $selectedAllocations = $selectedRoom['allocations'];
            $oldRoomType = $selectedAllocations->first()->roomType;
            $newRoomType = RoomType::findOrFail($newRoomTypeId);

            if ($newRoomType->hotel_id !== $booking->hotel_id) {
                throw new \Exception('New room type does not belong to this hotel');
            }

            if ((int) $newRoomTypeId === (int) $oldRoomType->id && (int) $selectedRoomId === (int) ($newRoomNumberId ?? $selectedRoomId)) {
                return [
                    'success' => true,
                    'message' => "Room type already set to '{$newRoomType->name}' for the selected room.",
                    'old_type' => $oldRoomType,
                    'new_type' => $newRoomType,
                    'effective_date' => $effectiveDate->toDateString(),
                ];
            }

            if (!$newRoomNumberId) {
                $replacementRooms = self::getAvailableReplacementRooms($bookingId, $selectedRoomId, $newRoomTypeId);
                $newRoom = collect($replacementRooms['available_rooms'])->first();

                if (!$newRoom) {
                    throw new \Exception("No rooms of type '{$newRoomType->name}' are available from {$effectiveDate->toDateString()}");
                }

                $newRoomNumberId = $newRoom->id;
            } else {
                $newRoom = RoomNumber::findOrFail($newRoomNumberId);

                if ((int) $newRoom->hotel_id !== (int) $booking->hotel_id) {
                    throw new \Exception('Selected replacement room does not belong to this hotel');
                }

                if ((int) $newRoom->room_type_id !== (int) $newRoomTypeId) {
                    throw new \Exception('Selected room is not of the requested room type');
                }
            }

            self::validateRoomAvailabilityForBooking(
                $booking->hotel_id,
                $newRoomNumberId,
                $effectiveDate->toDateString(),
                $booking->check_out_date,
                $bookingId
            );

            $selectedAllocationsAfterDate = $selectedAllocations->filter(function ($distribution) use ($effectiveDate) {
                return Carbon::parse($distribution->allocation_date)->gte($effectiveDate);
            });

            if ($selectedAllocationsAfterDate->isEmpty()) {
                throw new \Exception('No bookings remain after the selected effective date for the selected room.');
            }

            RoomDistribution::where('booking_id', $bookingId)
                ->where('room_number_id', $selectedRoomId)
                ->where('allocation_date', '>=', $effectiveDate->toDateString())
                ->update([
                    'room_type_id' => $newRoomTypeId,
                    'room_number_id' => $newRoomNumberId,
                    'is_room_assigned' => $booking->status === 'checked_in' ? 1 : 0,
                ]);

            if ($booking->status === 'checked_in') {
                RoomNumber::where('id', $selectedRoomId)->update(['status' => 0]);
                RoomNumber::where('id', $newRoomNumberId)->update(['status' => 1]);
            }

            BookingRoomTypeChange::create([
                'booking_id' => $bookingId,
                'hotel_id' => $booking->hotel_id,
                'old_room_type_id' => $oldRoomType->id,
                'new_room_type_id' => $newRoomTypeId,
                'old_room_number_id' => $selectedRoomId,
                'new_room_number_id' => $newRoomNumberId,
                'effective_from' => $effectiveDate->toDateString(),
                'changed_by' => Auth::id() ?? 1,
                'reason' => $reason ?: 'Room type change',
            ]);

            AuditLog::create([
                'user_id' => Auth::id() ?? 1,
                'hotel_id' => $booking->hotel_id,
                'module' => 'booking',
                'action' => 'change_room_type',
                'record_id' => $booking->id,
                'description' => $reason ?: 'Room type change',
                'old_value' => json_encode([
                    'room_type_id' => $oldRoomType->id,
                    'room_type_name' => $oldRoomType->name,
                    'room_number_id' => $selectedRoomId,
                ]),
                'new_value' => json_encode([
                    'room_type_id' => $newRoomTypeId,
                    'room_type_name' => $newRoomType->name,
                    'room_number_id' => $newRoomNumberId,
                    'effective_date' => $effectiveDate->toDateString(),
                ]),
            ]);

            return [
                'success' => true,
                'message' => "Room type changed from '{$oldRoomType->name}' to '{$newRoomType->name}' effective {$effectiveDate->toDateString()}",
                'old_type' => $oldRoomType,
                'new_type' => $newRoomType,
                'effective_date' => $effectiveDate->toDateString(),
            ];
        });
    }

    /**
     * Get available rooms for replacing current room during stay.
     *
     * @param int $bookingId
     * @param int $oldRoomNumberId The room being replaced
     * @return array ['available_rooms' => array, 'old_room' => RoomNumber]
     * @throws \Exception
     */
    // public static function getAvailableReplacementRooms($bookingId, $oldRoomNumberId = null)
    // {
    //     $booking = Booking::with('roomDistributions')->findOrFail($bookingId);

    //     // Get old room if specified
    //     $oldRoom = null;
    //     if ($oldRoomNumberId) {
    //         $oldRoom = RoomNumber::findOrFail($oldRoomNumberId);
    //     } else {
    //         // Get first allocated room
    //         $oldRoom = $booking->roomDistributions->first()?->roomNumber;
    //     }

    //     // Get room type requirements
    //     $roomTypeIds = $booking->roomDistributions->pluck('room_type_id')->unique();

    //     // Get date range for occupation
    //     $checkIn = Carbon::parse($booking->check_in_date);
    //     $checkOut = Carbon::parse($booking->check_out_date);

    //     // Find allocated rooms (excluding current booking, excluding old room)
    //     $allocatedRoomIds = RoomDistribution::where('hotel_id', $booking->hotel_id)
    //         ->whereIn('room_type_id', $roomTypeIds)
    //         ->where('booking_id', '!=', $bookingId)
    //         ->whereHas('booking', function ($q) {
    //             $q->whereIn('status', ['pending', 'confirmed', 'checked_in']);
    //         })
    //         ->where('is_room_assigned', 1)
    //         ->whereBetween('allocation_date', [
    //             $checkIn->toDateString(),
    //             $checkOut->subDay()->toDateString()
    //         ])
    //         ->distinct()
    //         ->pluck('room_number_id');

    //     // Get available replacement rooms
    //     $availableRooms = RoomNumber::where('hotel_id', $booking->hotel_id)
    //         ->whereIn('room_type_id', $roomTypeIds)
    //         ->whereNotIn('id', $allocatedRoomIds)
    //         ->where('status', 0) // Only available
    //         ->when($oldRoom, function ($q) use ($oldRoom) {
    //             return $q->where('id', '!=', $oldRoom->id);
    //         })
    //         ->get(['id', 'room_number', 'room_type_id']);

    //     return [
    //         'available_rooms' => $availableRooms,
    //         'old_room' => $oldRoom,
    //         'booking_id' => $bookingId,
    //         'booking_code' => $booking->booking_code
    //     ];
    // }

    public static function getAvailableReplacementRooms(
        $bookingId,
        $oldRoomNumberId = null,
        $selectedRoomTypeId = null
    ) {
        $booking = Booking::with('roomDistributions')->findOrFail($bookingId);

        $oldRoom = null;

        if ($oldRoomNumberId) {
            $oldRoom = RoomNumber::findOrFail($oldRoomNumberId);
        } else {
            $oldRoom = $booking->roomDistributions->first()?->roomNumber;
        }

        $roomTypeIds = $selectedRoomTypeId
            ? collect([(int) $selectedRoomTypeId])
            : $booking->roomDistributions
                ->pluck('room_type_id')
                ->filter()
                ->unique()
                ->values();

        $checkIn = Carbon::parse($booking->check_in_date);
        $checkOut = Carbon::parse($booking->check_out_date);

        $allocatedRoomIds = RoomDistribution::where('hotel_id', $booking->hotel_id)
            ->whereIn('room_type_id', $roomTypeIds)
            ->where('booking_id', '!=', $bookingId)
            ->whereHas('booking', function ($q) {
                $q->whereIn('status', ['pending', 'confirmed', 'checked_in']);
            })
            ->where('is_room_assigned', 1)
            ->whereBetween('allocation_date', [
                $checkIn->toDateString(),
                $checkOut->copy()->subDay()->toDateString(),
            ])
            ->whereNotNull('room_number_id')
            ->distinct()
            ->pluck('room_number_id');

        $availableRooms = RoomNumber::where('hotel_id', $booking->hotel_id)
            ->whereIn('room_type_id', $roomTypeIds)
            ->whereNotIn('id', $allocatedRoomIds)
            ->where('status', 0)
            ->when($oldRoom, function ($q) use ($oldRoom, $selectedRoomTypeId) {
                if (!$selectedRoomTypeId || (int) $oldRoom->room_type_id !== (int) $selectedRoomTypeId) {
                    $q->where('id', '!=', $oldRoom->id);
                }

                return $q;
            })
            ->get(['id', 'room_number', 'room_type_id']);

        $assignedRooms = $booking->roomDistributions
            ->filter(fn ($distribution) => !empty($distribution->room_number_id))
            ->pluck('room_number_id')
            ->filter()
            ->unique()
            ->map(function ($roomId) {
                $room = RoomNumber::find($roomId);

                return $room ? [
                    'id' => $room->id,
                    'room_number' => $room->room_number,
                    'room_type_id' => $room->room_type_id,
                    'room_type_name' => optional($room->roomType)->name,
                ] : null;
            })
            ->filter()
            ->values();

        return [
            'available_rooms' => $availableRooms,
            'old_room' => $oldRoom,
            'assigned_rooms' => $assignedRooms,
            'booking_id' => $bookingId,
            'booking_code' => $booking->booking_code,
        ];
    }

    /**
     * Validate that a room is available for the booking dates
     * (doesn't overlap with other active bookings).
     *
     * @param int $hotelId
     * @param int $roomNumberId
     * @param string $checkInDate
     * @param string $checkOutDate
     * @param int $excludeBookingId (Optional) Booking to exclude from conflict check
     * @return bool
     * @throws \Exception
     */
    private static function validateRoomAvailabilityForBooking(
        $hotelId,
        $roomNumberId,
        $checkInDate,
        $checkOutDate,
        $excludeBookingId = null
    ) {
        $checkIn = Carbon::parse($checkInDate);
        $checkOut = Carbon::parse($checkOutDate);

        $query = RoomDistribution::where('hotel_id', $hotelId)
            ->where('room_number_id', $roomNumberId)
            ->whereHas('booking', function ($q) {
                $q->whereIn('status', ['pending', 'confirmed', 'checked_in']);
            })
            ->where('is_room_assigned', 1)
            ->whereBetween('allocation_date', [
                $checkIn->toDateString(),
                $checkOut->subDay()->toDateString()
            ]);

        if ($excludeBookingId) {
            $query->where('booking_id', '!=', $excludeBookingId);
        }

        if ($query->exists()) {
            throw new \Exception('Room is already allocated for this date range');
        }

        return true;
    }
}
