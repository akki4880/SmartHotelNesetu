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
                'in:0,2,3,4,5',
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

        $room->update([
            'status' => (int) $validated['status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Room status updated successfully.',
            'data' => [
                'room_id' => $room->id,
                'status' => (int) $room->status,
            ],
        ]);
    }
}
