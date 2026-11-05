<?php

namespace App\Http\Controllers;

use App\Models\InventoryUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class InventoryUnitController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:unit_master,view', only: ['index', 'show']),
            new Middleware('permission:unit_master,create', only: ['create', 'store']),
            new Middleware('permission:unit_master,edit', only: ['edit', 'update']),
        ];
    }

    /**
     * Display listing
     */
    public function index()
    {
        $query = InventoryUnit::orderBy('id', 'desc');

        // 🔍 Filters

        // Name
        if (request('name')) {
            $query->where('name', 'like', '%' . request('name') . '%');
        }

        // Short Name
        if (request('short_name')) {
            $query->where('short_name', 'like', '%' . request('short_name') . '%');
        }

        // Unit Type
        if (request('unit_type')) {
            $query->where('unit_type', 'like', '%' . request('unit_type') . '%');
        }

        // Status
        if (request('status') !== null && request('status') !== '') {
            $query->where('status', request('status'));
        }

        // Final Data
        $units = $query->paginate(perPage())->withQueryString();

        return view('pages.inventoryManagement.units.index', compact('units'));
    }

    /**
     * Store Unit (ADMIN CONTROLLED)
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        try {

            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:inventory_units,name,NULL,id,deleted_at,NULL',
                'short_name' => 'required|string|max:50|unique:inventory_units,short_name,NULL,id,deleted_at,NULL',
                'unit_type' => 'nullable|string|max:100',
            ]);

            DB::beginTransaction();

            $unit = InventoryUnit::create([
                'name'       => strtolower($validated['name']),
                'short_name' => strtolower($validated['short_name']),
                'unit_type'  => $validated['unit_type'] ?? null,
                'user_id'    => $user->id,
                'status'     => $request->input('status', true),
            ]);

            // auditLog(
            //     module: 'Inventory Unit',
            //     action: 'Create',
            //     recordId: $unit->id,
            //     new: $unit->toArray(),
            //     description: 'Inventory unit created successfully'
            // );

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Inventory unit created successfully',
                'data'    => $unit,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'status'  => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors()
            ], 422);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Unit
     */
    public function update(Request $request, $id)
    {
        try {

            $unit = InventoryUnit::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:inventory_units,name,' . $unit->id . ',id,deleted_at,NULL',
                'short_name' => 'required|string|max:50|unique:inventory_units,short_name,' . $unit->id . ',id,deleted_at,NULL',
                'unit_type' => 'nullable|string|max:100',
                'status'    => 'required|boolean',
            ]);

            DB::beginTransaction();

            // $oldData = $unit->toArray();

            $unit->update([
                'name'       => strtolower($validated['name']),
                'short_name' => strtolower($validated['short_name']),
                'unit_type'  => $validated['unit_type'] ?? null,
                'status'     => $validated['status'],
            ]);

            // auditLog(
            //     module: 'Inventory Unit',
            //     action: 'Update',
            //     recordId: $unit->id,
            //     old: $oldData,
            //     new: $unit->fresh()->toArray(),
            //     description: 'Inventory unit updated successfully'
            // );

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Inventory unit updated successfully',
                'data'    => $unit
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'status'  => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors()
            ], 422);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Show
    public function show(InventoryUnit $unit){
        $data = [
            'title' => 'View Units Details',

            'fields' => [
                [
                    'label' => 'Name',
                    'value' => $unit->name,
                ],
                [
                    'label' => 'Short Name',
                    'value' => $unit->short_name,
                ],
                [
                    'label' => 'Unit Type',
                    'value' => $unit->unit_type,
                ],
                 [
                    'label' => 'Status',
                    'value' => $unit->status == 1 ? 'Active' : 'Inactive',
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render();
    }
} 