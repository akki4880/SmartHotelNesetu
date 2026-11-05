<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class HolidayController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:holidays,view', only: ['index', 'show']),
            new Middleware('permission:holidays,create', only: ['create', 'store']),
            new Middleware('permission:holidays,edit', only: ['edit', 'update']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Holiday::where('hotel_id', Auth::user()->hotel_id)
            ->orderBy('date', 'desc');

        // 🔍 Filters

        // Name
        if (request('name')) {
            $query->where('name', 'like', '%' . request('name') . '%');
        }

        // Date
        if (request('date')) {
            $query->whereDate('date', request('date'));
        }

        // Is Optional
        if (request('is_optional') !== null && request('is_optional') !== '') {
            $query->where('is_optional', request('is_optional'));
        }

        // Final Data
        $holidays = $query->paginate(perPage())->withQueryString();

        return view('pages.employeeManagement.holiday.index', compact('holidays'));
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
            // ✅ Validation
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255'
                ],
                'date' => [
                    'required',
                    'date',
                    'after:today'
                ],
                'is_optional' => [
                    'nullable',
                    'boolean'
                ],
                'description' => [
                    'nullable',
                    'string'
                ],
            ]);

            // ✅ Assuming hotel_id comes from logged-in user
            $hotelId = auth()->user()->hotel_id;

            DB::beginTransaction();

            // ✅ Create holiday
            $holiday = Holiday::create([
                'hotel_id'   => $hotelId,
                'name'       => $validated['name'],
                'date'       => $validated['date'],
                'is_optional' => $validated['is_optional'] ?? false,
                'description' => $validated['description'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Holiday created successfully',
                'data' => $holiday
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            // Handle duplicate entry (hotel_id + date unique)
            if ($e->errorInfo[1] == 1062) {
                return response()->json([
                    'status' => false,
                    'message' => 'Holiday already exists for this date'
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
    public function show(Holiday $holiday)
    {
        $data = [
            'title' => 'View Holiday Details',

            'fields' => [
                [
                    'label' => 'Name',
                    'value' => $holiday->name,
                ],
                [
                    'label' => 'Date',
                    'value' => $holiday->date->format('d M Y'),
                ],
                [
                    'label' => 'Type',
                    'value' => $holiday->is_optional ? 'Optional Holiday' : 'Mandatory Holiday',
                ],
                [
                    'label' => 'Description',
                    'value' => $holiday->description ?? 'N/A',
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Holiday $holiday)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255'
                ],
                'date' => [
                    'required',
                    'date'
                ],
                'is_optional' => [
                    'nullable',
                    'boolean'
                ],
                'description' => [
                    'nullable',
                    'string'
                ],
            ]);

            // ✅ Find holiday (ensure it belongs to same hotel)
            $holiday = Holiday::where('id', $id)
                ->where('hotel_id', auth()->user()->hotel_id)
                ->firstOrFail();

            DB::beginTransaction();

            // ✅ Update values
            $holiday->update([
                'name'        => $validated['name'],
                'date'        => $validated['date'],
                'is_optional' => $validated['is_optional'] ?? false,
                'description' => $validated['description'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Holiday updated successfully',
                'data' => $holiday
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            // ✅ Handle unique constraint (hotel_id + date)
            if ($e->errorInfo[1] == 1062) {
                return response()->json([
                    'status' => false,
                    'message' => 'Holiday already exists for this date'
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
    public function destroy(Holiday $holiday)
    {
        try {
            // Ensure holiday belongs to the user's hotel
            if ($holiday->hotel_id !== auth()->user()->hotel_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $holiday->delete();

            return response()->json([
                'status' => true,
                'message' => 'Holiday deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error deleting holiday: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $user = Auth::user();

        try {
            $holiday = Holiday::where('hotel_id', $user->hotel_id)->findOrFail($id);
            $oldStatus = $holiday->is_optional;
            $holiday->update([
                'is_optional' => !$holiday->is_optional
            ]);

            auditLog(
                module: 'Holiday',
                action: 'Status Toggle',
                recordId: $holiday->id,
                old: ['is_optional' => $oldStatus],
                new: ['is_optional' => $holiday->is_optional],
                description: 'Holiday type updated successfully'
            );

            return response()->json([
                'status'  => true,
                'message' => 'Holiday type updated successfully',
                'is_optional' => $holiday->is_optional
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {

            return response()->json([
                'status'  => false,
                'message' => 'Holiday not found'
            ], 404);
        } catch (\Throwable $e) {

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
