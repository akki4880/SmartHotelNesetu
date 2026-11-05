<?php

namespace App\Http\Controllers;

use App\Models\RoomRate;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class RoomRateController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:room_rate_setup,view', only: ['index', 'show']),
            new Middleware('permission:room_rate_setup,create', only: ['create', 'store']),
            new Middleware('permission:room_rate_setup,edit', only: ['edit', 'update']),
        ];
    }

    /**
     * Display a listing of the resource.
     */ 
    public function index(Request $request)
    {
        $roomTypes = RoomType::where('hotel_id', Auth::user()->hotel_id)
            ->where('status', 1)
            ->get();

        $roomRates = RoomRate::with('roomType')
            ->where('hotel_id', auth()->user()->hotel_id);

        /* Room Type */
        if ($request->filled('room_type_id')) {
            $roomRates->where('room_type_id', $request->room_type_id);
        }

        /* Status */
        if ($request->filled('status')) {
            $roomRates->where('status', $request->status);
        }

        /* Base Price Filter */
        if ($request->filled('base_min')) {
            $roomRates->where('base_price', '>=', $request->base_min);
        }

        if ($request->filled('base_max')) {
            $roomRates->where('base_price', '<=', $request->base_max);
        }

        /* Weekend Price Filter */
        if ($request->filled('weekend_min')) {
            $roomRates->where('weekend_price', '>=', $request->weekend_min);
        }

        if ($request->filled('weekend_max')) {
            $roomRates->where('weekend_price', '<=', $request->weekend_max);
        }

        $roomRates = $roomRates
            ->latest()
            ->paginate(perPage())
            ->withQueryString();

        return view(
            'pages.roommanagement.roomrates.index',
            compact('roomTypes', 'roomRates')
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
        $request->validate(
            [
                'room_type_id' => [
                    'required',
                    'exists:room_types,id',
                    Rule::unique('room_rates', 'room_type_id')
                        ->where(
                            fn($q) =>
                            $q->where('hotel_id', auth()->user()->hotel_id)
                        ),
                ],
                'base_price'         => 'required|numeric|min:0',
                'weekend_price'      => 'nullable|numeric|min:0',
                'extra_guest_price'  => 'nullable|numeric|min:0',
                'base_guest_count'   => 'required|integer|min:1',
                'max_extra_guests'   => 'required|integer|min:0',
                'status' => 'required'
            ],
            [
                'room_type_id.unique' => 'Room rate already exists for the selected room type.',
            ]
        );

        try {

            DB::beginTransaction();

            RoomRate::create([
                'hotel_id'          => auth()->user()->hotel_id, // same pattern you use
                'room_type_id'      => $request->room_type_id,
                'base_price'        => $request->base_price,
                'weekend_price'     => $request->weekend_price,
                'extra_guest_price' => $request->extra_guest_price,
                'base_guest_count'  => $request->base_guest_count,
                'max_extra_guests'  => $request->max_extra_guests,
                'status'         => $request->has('status'),
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Room rate created successfully.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Failed to create room rate.',
                'error'   => $e->getMessage() // remove in production if needed
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(RoomRate $roomRate)
    { 
        $data = [
            'title' => 'View Room Rate Details',

            'fields' => [
                [
                    'label' => 'Room Type',
                    'value' => $roomRate->roomType->name,
                ],
                [
                    'label' => 'Base Price',
                    'value' => '₹' . number_format($roomRate->base_price, 2),
                ],
                [
                    'label' => 'Weekend Price',
                    'value' => '₹' . number_format($roomRate->weekend_price, 2),
                ],
                [
                    'label' => 'Extra Guest Price',
                    'value' => '₹' . number_format($roomRate->extra_guest_price, 2),
                ],
                [
                    'label' => 'Base Guests',
                    'value' => $roomRate->base_guest_count,
                ],
                [
                    'label' => 'Max Extra Guests',
                    'value' => $roomRate->max_extra_guests,
                ],
                [
                    'label' => 'Status',
                    'value' => $roomRate->status ? 'Active' : 'Inactive',
                ],
                [
                    'label' => 'Created At',
                    'value' => formatDateTime($roomRate->created_at),
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render(); 
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RoomRate $roomRate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate(
            [
                'room_type_id' => [
                    'required',
                    'exists:room_types,id',
                    Rule::unique('room_rates', 'room_type_id')
                        ->where(
                            fn($q) =>
                            $q->where('hotel_id', auth()->user()->hotel_id)
                        )
                        ->ignore($id),
                ],
                'base_price'        => 'required|numeric|min:0',
                'weekend_price'     => 'nullable|numeric|min:0',
                'extra_guest_price' => 'nullable|numeric|min:0',
                'base_guest_count'  => 'required|integer|min:1',
                'max_extra_guests'  => 'required|integer|min:0',
                'status'            => 'required|in:0,1',
            ],
            [
                'room_type_id.unique' => 'Room rate already exists for the selected room type.',
            ]
        );

        try {
            DB::beginTransaction();

            $roomRate = RoomRate::where('id', $id)
                ->where('hotel_id', auth()->user()->hotel_id)
                ->firstOrFail();

            $roomRate->update([
                'room_type_id'      => $request->room_type_id,
                'base_price'        => $request->base_price,
                'weekend_price'     => $request->weekend_price,
                'extra_guest_price' => $request->extra_guest_price,
                'base_guest_count'  => $request->base_guest_count,
                'max_extra_guests'  => $request->max_extra_guests,
                'status'            => $request->status, // 🔥 FIXED
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Room rate updated successfully.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update room rate.',
                'error'   => $e->getMessage(), // remove in prod
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RoomRate $roomRate)
    {
        //
    }

}
