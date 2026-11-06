<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ShiftController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:shifts,view', only: ['index', 'show']),
            new Middleware('permission:shifts,create', only: ['create', 'store']),
            new Middleware('permission:shifts,edit', only: ['edit', 'update']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Shift::where('hotel_id', Auth::user()->hotel_id)
            ->orderBy('id', 'desc');

        // 🔍 Filters

        // Name
        if (request('name')) {
            $query->where('name', 'like', '%' . request('name') . '%');
        }

        // Start Time From
        if (request('start_from')) {
            $query->whereTime('start_time', '>=', request('start_from'));
        }

        // Start Time To
        if (request('start_to')) {
            $query->whereTime('start_time', '<=', request('start_to'));
        }

        // End Time From
        if (request('end_from')) {
            $query->whereTime('end_time', '>=', request('end_from'));
        }

        // End Time To
        if (request('end_to')) {
            $query->whereTime('end_time', '<=', request('end_to'));
        }

        // Grace Minutes
        if (request('grace_minutes')) {
            $query->where('grace_minutes', request('grace_minutes'));
        }

        // Night Shift
        if (request('is_night_shift') !== null && request('is_night_shift') !== '') {
            $query->where('is_night_shift', request('is_night_shift'));
        }

        // Status
        if (request('status') !== null && request('status') !== '') {
            $query->where('status', request('status'));
        }

        // Final Data
        $shifts = $query->paginate(perPage())->withQueryString();

        return view('pages.employeeManagement.shift.index', compact('shifts'));
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
        try {
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('shifts')->where(function ($q) {
                        return $q->where('hotel_id', auth()->user()->hotel_id);
                    }),
                ],
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i',
                'grace_minutes' => 'nullable|integer|min:0',
                'status' => 'nullable|boolean',
            ]);

            $start = $validated['start_time'];
            $end = $validated['end_time'];

            // ✅ Auto detect night shift
            $isNightShift = $end < $start;

            // ❌ Prevent same start & end time
            if ($start === $end) {
                return response()->json([
                    'status' => false,
                    'errors' => [
                        'end_time' => ['Start time and end time cannot be same']
                    ]
                ], 422);
            }

            DB::beginTransaction();
            $start = Carbon::parse($request->start_time);
            $end = Carbon::parse($request->end_time);

            if ($end < $start) {
                $end->addDay();
            }

            $calculatedHours = $start->diffInMinutes($end) / 60;

            $expectedHours = $request->expected_hours ?? $calculatedHours;

            $shift = Shift::create([
                'hotel_id' => auth()->user()->hotel_id,
                'name' => $validated['name'],
                'start_time' => $start,
                'end_time' => $end,
                'expected_hours' => $expectedHours,
                'grace_minutes' => $validated['grace_minutes'] ?? 0,
                'is_night_shift' => $isNightShift, // 🔥 auto
                'status' => $validated['status'] ?? 1,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Shift created successfully',
                'data' => $shift
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            if ($e->errorInfo[1] == 1062) {
                return response()->json([
                    'status' => false,
                    'message' => 'Shift already exists for this hotel'
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Database error occurred'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Shift $shift)
    {
        $data = [
            'title' => 'View Shift Details',

            'fields' => [
                [
                    'label' => 'Shift Name',
                    'value' => $shift->name,
                ],
                [
                    'label' => 'Start Time',
                    'value' => formatTime($shift->start_time),
                ],
                [
                    'label' => 'End Time',
                    'value' => formatTime($shift->end_time),
                ],
                [
                    'label' => 'Grace Minutes',
                    'value' => $shift->grace_minutes . ' mins',
                ],
                [
                    'label' => 'Night Shift',
                    'value' => $shift->is_night_shift ? 'Yes' : 'No',
                ],
                [
                    'label' => 'Expected Hours',
                    'value' => $shift->expected_hours,
                ],
                [
                    'label' => 'Status',
                    'value' => $shift->status ? 'Active' : 'Inactive',
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shift $shift)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $shift = Shift::where('id', $id)
                ->where('hotel_id', auth()->user()->hotel_id)
                ->firstOrFail();

            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('shifts')
                        ->where(fn($q) => $q->where('hotel_id', auth()->user()->hotel_id))
                        ->ignore($id),
                ],
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i',
                'grace_minutes' => 'nullable|integer|min:0',
                'expected_hours' => 'nullable|numeric|min:0|max:24',
                'status' => 'nullable|boolean',
            ]);

            $start = Carbon::parse($validated['start_time']);
            $end = Carbon::parse($validated['end_time']);

            // ❌ Prevent same time
            if ($start->eq($end)) {
                return response()->json([
                    'status' => false,
                    'errors' => [
                        'end_time' => ['Start and end time cannot be same']
                    ]
                ], 422);
            }

            // ✅ Auto detect night shift
            $isNightShift = $end->lt($start);

            if ($isNightShift) {
                $end->addDay();
            }

            // ✅ Calculate hours
            $calculatedHours = $start->diffInMinutes($end) / 60;

            // ✅ Use user input OR fallback
            $expectedHours = $validated['expected_hours'] ?? $calculatedHours;

            DB::beginTransaction();

            $shift->update([
                'name' => $validated['name'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'grace_minutes' => $validated['grace_minutes'] ?? 0,
                'expected_hours' => $expectedHours,
                'is_night_shift' => $isNightShift, // 🔥 auto
                'status' => $validated['status'] ?? 1,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Shift updated successfully',
                'data' => $shift
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            if ($e->errorInfo[1] == 1062) {
                return response()->json([
                    'status' => false,
                    'message' => 'Shift name already exists for this hotel'
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Database error occurred'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shift $shift)
    {
        //
    }

}
