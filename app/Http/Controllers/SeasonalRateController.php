<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use App\Models\SeasonalRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SeasonalRateController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:seasonal_room_rate_setup,view', only: ['index', 'show']),
            new Middleware('permission:seasonal_room_rate_setup,create', only: ['create', 'store']),
            new Middleware('permission:seasonal_room_rate_setup,edit', only: ['edit', 'update']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SeasonalRate::with('roomType')->where('hotel_id', Auth::user()->hotel_id);

        /* Room Type */
        if ($request->filled('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
        }

        /* Season Name */
        if ($request->filled('season_name')) {
            $query->where('season_name', 'like', '%' . $request->season_name . '%');
        }

        /* Start Date */
        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }

        /* End Date */
        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        /* Price Range */
        if ($request->filled('min_price')) {
            $query->where('seasonal_price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('seasonal_price', '<=', $request->max_price);
        }

        /* Status */
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $seasonalRates = $query
            ->orderBy('start_date')
            ->paginate(perPage())
            ->withQueryString();

        $roomTypes = RoomType::where('status', 1)->where('hotel_id', Auth::user()->hotel_id)->get();

        return view(
            'pages.roommanagement.seasonalrates.index',
            compact('seasonalRates', 'roomTypes')
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
        $validated = $request->validate(
            [
                'room_type_id'   => ['required', 'exists:room_types,id'],
                'season_name'    => ['nullable', 'string', 'max:255'],
                'start_date'     => ['required', 'date'],
                'end_date'       => ['required', 'date', 'after_or_equal:start_date'],
                'seasonal_price' => ['required', 'numeric', 'min:0'],
                'status'         => ['required', 'boolean'],

                // Prevent overlapping seasons
                'start_date' => [
                    'required',
                    function ($attr, $value, $fail) use ($request) {
                        $exists = SeasonalRate::where('room_type_id', $request->room_type_id)
                            ->where(function ($q) use ($request) {
                                $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                                    ->orWhereBetween('end_date', [$request->start_date, $request->end_date]);
                            })->exists();

                        if ($exists) {
                            $fail('A seasonal rate already exists for this room type within the selected date range.');
                        }
                    }
                ],
            ],
            [
                'room_type_id.required' => 'Please select a room type.',
                'seasonal_price.required' => 'Seasonal price is required.',
            ]
        );
        DB::beginTransaction();

        try {
            DB::commit();
            $validated['hotel_id'] = Auth::user()->hotel_id;

            SeasonalRate::create($validated);

            return response()->json([
                'status' => true,
                'message' => 'Seasonal rate added successfully.'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            \Log::error('SeasonalRate Create Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to create seasonal rate.' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SeasonalRate $seasonalRate)
    { 
        $data = [
            'title' => 'View SeasonalRate Details',

            'fields' => [
                [
                    'label' => 'Room Type',
                    'value' => $seasonalRate->roomType->name,
                ],
                [
                    'label' => 'Season Name',
                    'value' => $seasonalRate->season_name,
                ],
                [
                    'label' => 'Start Date',
                    'value' => formatDateTime($seasonalRate->start_date),
                ],
                [
                    'label' => 'End Date',
                    'value' => formatDateTime($seasonalRate->end_date),
                ],
                [
                    'label' => 'Seasonal Price',
                    'value' => $seasonalRate->seasonal_price,
                ],
                [
                    'label' => 'Status',
                    'value' => $seasonalRate->status ? 'Active' : 'Inactive',
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render(); 
    } 

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SeasonalRate $seasonalRate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate(
            [
                'room_type_id'   => ['required', 'exists:room_types,id'],
                'season_name'    => ['nullable', 'string', 'max:255'],
                'start_date'     => ['required', 'date'],
                'end_date'       => ['required', 'date', 'after_or_equal:start_date'],
                'seasonal_price' => ['required', 'numeric', 'min:0'],
                'status'         => ['required', 'in:0,1'],

                // 🔥 Prevent overlapping seasons (IGNORE current record)
                'start_date' => [
                    'required',
                    function ($attr, $value, $fail) use ($request, $id) {
                        $exists = SeasonalRate::where('room_type_id', $request->room_type_id)
                            ->where('id', '!=', $id)
                            ->where(function ($q) use ($request) {
                                $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                                    ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                                    ->orWhere(function ($q2) use ($request) {
                                        $q2->where('start_date', '<=', $request->start_date)
                                            ->where('end_date', '>=', $request->end_date);
                                    });
                            })
                            ->exists();

                        if ($exists) {
                            $fail('A seasonal rate already exists for this room type within the selected date range.');
                        }
                    }
                ],
            ],
            [
                'room_type_id.required'      => 'Please select a room type.',
                'seasonal_price.required'    => 'Seasonal price is required.',
                'end_date.after_or_equal'    => 'End date must be after or equal to start date.',
            ]
        );

        DB::beginTransaction();

        try {
            $season = SeasonalRate::where('id', $id)
                ->firstOrFail();
                
            $validated['hotel_id'] = Auth::user()->hotel_id;

            $season->update($validated);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Seasonal rate updated successfully.'
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            \Log::error('SeasonalRate Update Error', [
                'error' => $e->getMessage(),
                'id'    => $id
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update seasonal rate.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SeasonalRate $seasonalRate)
    {
        //
    }

}
