<?php

namespace App\Http\Controllers;

use App\Models\RoomNumber;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class RoomNumberController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:room_numbers,view', only: ['index', 'show']),
            new Middleware('permission:room_numbers,create', only: ['create', 'store']),
            new Middleware('permission:room_numbers,edit', only: ['edit', 'update']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $roomNumbersQuery = RoomNumber::with('roomType')
            ->where('hotel_id', Auth::user()->hotel_id);

        /* =========================
            ROOM TYPE FILTER
        ========================== */
        if ($request->filled('room_type_id')) {
            $roomNumbersQuery->where('room_type_id', $request->room_type_id);
        }

        $roomnumbers = $roomNumbersQuery
            ->orderBy('room_type_id')
            ->orderBy('room_number')
            ->get()
            ->groupBy('room_type_id');

        $roomTypes = RoomType::where('hotel_id', Auth::user()->hotel_id)->get();

        return view(
            'pages.roommanagement.roomnumbers.index',
            compact('roomnumbers', 'roomTypes')
        );
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
     */
    public function store(Request $request)
    {
        $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'room_numbers' => 'required|array|min:1',
            'room_numbers.*' => [
                'required',
                'string',
                'max:10',
                'distinct',
                function ($attribute, $value, $fail) use ($request) {
                    $exists = \App\Models\RoomNumber::where('hotel_id', Auth::user()->hotel_id)
                        ->where('room_number', $value)
                        ->exists();
                    if ($exists) {
                        $fail("Room number {$value} already exists for this hotel.");
                    }
                },
            ],
            'status' => 'required|in:0,1,2',
        ]);

        try {
            $hotel_id = Auth::user()->hotel_id;
            foreach ($request->room_numbers as $room_number) {
                RoomNumber::create([
                    'hotel_id'     => $hotel_id,
                    'room_type_id' => $request->room_type_id,
                    'room_number'  => $room_number,
                    'status'       => $request->status,
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Rooms added successfully.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Room Bulk Create Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to add rooms.' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(RoomNumber $roomNumber)
    { 
        $data = [
            'title' => 'View Room Number Details',

            'fields' => [
                [
                     'label' => 'Room Type',
                    'value' => $roomNumber->roomType->name,
                ],
                [
                    'label' => 'Room Number',
                    'value' => $roomNumber->room_number,
                ],
                [
                    'label' => 'Status',
                    'value' => match($roomNumber->status) {
                        0 => 'Available',
                        1 => 'Occupied',
                        2 => 'Maintenance',
                        3 => 'Dirty',
                        4 => 'Cleaning',
                        5 => 'Blocked', 
                    },
                ], 
                [
                    'label' => 'Created At',
                    'value' => formatDateTime($roomNumber->created_at),
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render();
    }
    

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RoomNumber $roomNumber)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $room = RoomNumber::findorfail($request->id);
        $hotelId = Auth::user()->hotel_id;

        $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'room_number'  => [
                'required',
                'string',
                'max:10',
                Rule::unique('room_numbers')
                    ->where(fn($query) => $query->where('hotel_id', $hotelId))
                    ->ignore($room->id),
            ],
            'status' => 'required|in:0,1,2,3,4,5',
        ]);
        try {

            $room->update([
                'room_type_id' => $request->room_type_id,
                'room_number'  => $request->room_number,
                'status'       => $request->status,
            ]);


            return response()->json([
                'status' => true,
                'message' => 'Room Number updated successfully.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Room Number Update Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to update room number.' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RoomNumber $roomNumber)
    {
        //
    }
}
