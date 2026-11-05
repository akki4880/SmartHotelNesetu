<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class RoomTypeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:room_types,view', only: ['index', 'show']),
            new Middleware('permission:room_types,create', only: ['create', 'store']),
            new Middleware('permission:room_types,edit', only: ['edit', 'update']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = RoomType::where('hotel_id', Auth::user()->hotel_id);

        // 🔍 Room Type filter
        if ($request->filled('room_type')) {
            $query->where('name', 'like', '%' . $request->room_type . '%');
        }

        // 🔍 Description filter
        if ($request->filled('description')) {
            $query->where('description', 'like', '%' . $request->description . '%');
        }

        // 🔍 Status filter
        if ($request->filled('status')) {
            $query->where('status', 'like', '%' . $request->status . '%');
            // OR if status is numeric:
            // $query->where('status', $request->status);
        }

        // ⬇ Order by DESC (latest first)
        $query->orderBy('id', 'desc');
        // OR: $query->latest();

        // 📄 Pagination
        $roomtypes = $query->paginate(perPage())->withQueryString();

        return view('pages.roommanagement.roomtypes.index', compact('roomtypes'));
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
        $hotelId = Auth::user()->hotel_id;

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('room_types')
                    ->where(fn($query) => $query->where('hotel_id', $hotelId)),
            ],
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:0,1',
            'default_base_guests' => 'required|integer|min:1',
            'default_max_extra_guests' => 'required|integer|min:0',
        ]);


        try {

            DB::beginTransaction();

            RoomType::create([
                'name'        => $request->name,
                'hotel_id' => Auth::user()->hotel_id,
                'description' => $request->description,
                'status'      => $request->status,
                'default_base_guests' => $request->default_base_guests,
                'default_max_extra_guests' => $request->default_max_extra_guests,
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Room type created successfully.'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('RoomType Create Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to create room type.' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(RoomType $roomType)
    { 
        $data = [
            'title' => 'View RoomType Details',

            'fields' => [
                [
                    'label' => 'Room NAme',
                    'value' => $roomType->name,
                ],
                [
                    'label' => 'Description',
                    'value' => $roomType->description,
                ],
                [
                    'label' => 'Status',
                    'value' => $roomType->status ? 'Active' : 'Inactive',
                ],
                [
                    'label' => 'Min guests',
                    'value' => $roomType->default_base_guests,
                ],
                [
                    'label' => 'Max extra guests',
                    'value' => $roomType->default_max_extra_guests,
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render(); 
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RoomType $roomType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $hotelId = Auth::user()->hotel_id;

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('room_types')
                    ->where(fn($query) => $query->where('hotel_id', $hotelId))
                    ->ignore($id),
            ],
            'default_base_guests' => 'required|integer|min:1',
            'default_max_extra_guests' => 'required|integer|min:0',
            'status' => 'required|in:0,1',
        ]);

        try {

            $roomType = RoomType::findOrFail($id);

            $roomType->update([
                'name' => $request->name,
                'description' => $request->description,
                'default_base_guests' => $request->default_base_guests,
                'default_max_extra_guests' => $request->default_max_extra_guests,
                'status' => $request->status,
            ]);

            return response()->json(['message' => 'Room type updated successfully']);
        } catch (\Exception $e) {
            Log::error('RoomType Update Error', [
                'roomType_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to update room type.' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RoomType $roomType)
    {
        //
    }

    public function guestConfig($id)
    {
        try {
            $roomType = RoomType::select('default_base_guests', 'default_max_extra_guests')
                ->findOrFail($id);

            return response()->json([
                'status' => true,
                'data' => $roomType
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Room type not found'
            ], 404);
        }
    }
}
