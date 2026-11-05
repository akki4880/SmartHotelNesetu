<?php

namespace App\Http\Controllers;

use App\Models\WeeklyOff;
use Illuminate\Http\Request;

class WeeklyOffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'employee_id' => 'required|exists:employees,id',
        ]);

        $employee = \App\Models\Employee::where('id', $request->employee_id)
            ->where('hotel_id', auth()->user()->hotel_id)
            ->firstOrFail();

        try {
            WeeklyOff::create([
                'hotel_id' => auth()->user()->hotel_id,
                'employee_id' => $employee->id,
                'day_of_week' => $request->day_of_week,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Weekly off assigned successfully.'
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'status' => false,
                'message' => 'This weekly off already exists for the selected employee.'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Unable to assign weekly off.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(WeeklyOff $weeklyOff)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WeeklyOff $weeklyOff)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WeeklyOff $weeklyOff)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WeeklyOff $weeklyOff)
    {
        //
    }
}
