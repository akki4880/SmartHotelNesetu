<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use App\Models\RoomNumber;
use Illuminate\Http\Request;

class HousekeepingController extends Controller
{
    /**
     * Get housekeeping room types and room numbers.
     */
    public function index(Request $request)
    {
        $hotelId = $request->user()->hotel_id;

        $roomTypes = RoomType::with([
            'roomNumbers' => function ($query) use ($hotelId) {
                $query->where('hotel_id', $hotelId)
                    ->orderBy('room_number');
            }
        ])
            ->where('hotel_id', $hotelId)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Housekeeping rooms retrieved successfully.',
            'data' => $roomTypes,
        ]);
    }

    /**
     * Update housekeeping status for a room.
     */

    public function updateRoomStatus(Request $request)
    {
        $validated = $request->validate([
            'room_id' => [
                'required',
                'integer',
                'exists:room_numbers,id',
            ],
            'status' => [
                'required',
                'integer',
                'in:0,1,2,3,4,5',
            ],
        ]);

        $room = RoomNumber::where('id', $validated['room_id'])
            ->where('hotel_id', $request->user()->hotel_id)
            ->first();

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room not found for this hotel.',
            ], 404);
        }

        $currentStatus = (int) $room->status;
        $newStatus = (int) $validated['status'];

        // Define allowed status transitions
        $allowedTransitions = [
            0 => [1, 2, 5], // Available -> Occupied, Maintenance, Blocked
            1 => [3],       // Occupied -> Dirty (Checkout)
            2 => [0],       // Maintenance -> Available
            3 => [4],       // Dirty -> Cleaning
            4 => [0, 3],    // Cleaning -> Available, Dirty
            5 => [0],       // Blocked -> Available
        ];

        if (
            !isset($allowedTransitions[$currentStatus]) ||
            !in_array($newStatus, $allowedTransitions[$currentStatus])
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid room status transition.',
            ], 422);
        }

        $room->update([
            'status' => $newStatus,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Room status updated successfully.',
            'data' => [
                'room_id' => $room->id,
                'previous_status' => $currentStatus,
                'status' => $newStatus,
            ],
        ]);
    }
}
