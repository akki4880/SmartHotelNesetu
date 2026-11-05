<?php

namespace App\Http\Controllers;

use App\Models\HourlyRate;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class HourlyRateController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:hourly_room_rate_setup,view', only: ['index', 'show']),
            new Middleware('permission:hourly_room_rate_setup,create', only: ['create', 'store']),
            new Middleware('permission:hourly_room_rate_setup,edit', only: ['edit', 'update']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $hotelId = auth()->user()->hotel_id;

        $query = HourlyRate::with('roomType')
            ->where('hotel_id', $hotelId);

        /* Room Type */
        if ($request->filled('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
        }
 
        /* Status */
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $hourlyRates = $query
            ->latest()
            ->paginate(perPage())
            ->withQueryString();

        $roomTypes = RoomType::where('hotel_id', $hotelId)
            ->where('status', 1)
            ->get();

        return view(
            'pages.roommanagement.hourlyrates.index',
            compact('hourlyRates', 'roomTypes')
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
        $hotelId = auth()->user()->hotel_id;

        $request->validate([
            'room_type_id' => [
                'required',
                'exists:room_types,id',
                Rule::unique('hourly_rates')->where(
                    fn($q) =>
                    $q->where('hotel_id', $hotelId)
                ),
            ],
            'base_hours'         => 'required|integer|min:1',
            'base_price'         => 'required|numeric|min:0',
            'extra_hour_price'   => 'required|numeric|min:0',
            'base_guest_count'   => 'required|integer|min:1',
            'max_extra_guests'   => 'nullable|integer|min:0',
            'max_hours'          => 'nullable|integer|min:1',
            'status'             => 'required|boolean',
        ], [
            'room_type_id.unique' => 'Hourly rate already exists for this room type.',
        ]);

        try {
            DB::beginTransaction();

            HourlyRate::create([
                'hotel_id'         => $hotelId,
                'room_type_id'     => $request->room_type_id,
                'base_hours'       => $request->base_hours,
                'base_price'       => $request->base_price,
                'extra_hour_price' => $request->extra_hour_price,
                'base_guest_count' => $request->base_guest_count,
                'max_extra_guests' => $request->max_extra_guests ?? 0,
                'max_hours'        => $request->max_hours,
                'status'           => $request->status,
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Hourly rate created successfully.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Failed to create hourly rate.',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(HourlyRate $hourlyRate)
    { 
        $data = [
            'title' => 'View Hourly Rate Details',

            'fields' => [
                [
                    'label' => 'Room Type',
                    'value' => $hourlyRate->roomType->name ?? '-',
                ],
                [
                    'label' => 'Base Hours',
                    'value' => $hourlyRate->base_hours . ' hrs',
                ],
                [
                    'label' => 'Base Price',
                    'value' => '₹' . number_format($hourlyRate->base_price, 2),
                ],
                [
                    'label' => 'Extra Hour Price',
                    'value' => '₹' . number_format($hourlyRate->extra_hour_price, 2),
                ],
                [
                    'label' => 'Base Guests',
                    'value' => $hourlyRate->base_guest_count,
                ],
                [
                    'label' => 'Max Extra Guests',
                    'value' => $hourlyRate->max_extra_guests ?? '-',
                ],
                [
                    'label' => 'Max Hours',
                    'value' => $hourlyRate->max_hours ?? '-',
                ],
                [
                    'label' => 'Status',
                    'value' => $hourlyRate->status ? 'Active' : 'Inactive',
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render();
    } 

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HourlyRate $hourlyRate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $hotelId = auth()->user()->hotel_id;

        $request->validate([
            'room_type_id' => [
                'required',
                'exists:room_types,id',
                Rule::unique('hourly_rates')
                    ->where(fn($q) => $q->where('hotel_id', $hotelId))
                    ->ignore($id),
            ],
            'base_hours'         => 'required|integer|min:1',
            'base_price'         => 'required|numeric|min:0',
            'extra_hour_price'   => 'required|numeric|min:0',
            'base_guest_count'   => 'required|integer|min:1',
            'max_extra_guests'   => 'nullable|integer|min:0',
            'max_hours'          => 'nullable|integer|min:1',
            'status'             => 'required|boolean',
        ], [
            'room_type_id.unique' => 'Hourly rate already exists for this room type.',
        ]);

        try {
            DB::beginTransaction();

            $hourlyRate = HourlyRate::where('id', $id)
                ->where('hotel_id', $hotelId)
                ->firstOrFail();

            $hourlyRate->update([
                'room_type_id'     => $request->room_type_id,
                'base_hours'       => $request->base_hours,
                'base_price'       => $request->base_price,
                'extra_hour_price' => $request->extra_hour_price,
                'base_guest_count' => $request->base_guest_count,
                'max_extra_guests' => $request->max_extra_guests ?? 0,
                'max_hours'        => $request->max_hours,
                'status'           => $request->status,
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Hourly rate updated successfully.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update hourly rate.',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HourlyRate $hourlyRate)
    {
        //
    }

}
