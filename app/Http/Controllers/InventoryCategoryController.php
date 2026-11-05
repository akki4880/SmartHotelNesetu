<?php

namespace App\Http\Controllers;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class InventoryCategoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:category_master,view', only: ['index', 'show']),
            new Middleware('permission:category_master,create', only: ['create', 'store']),
            new Middleware('permission:category_master,edit', only: ['edit', 'update']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = InventoryCategory::where('hotel_id', Auth::user()->hotel_id)
            ->orderBy('id', 'desc');

        // 🔍 Filters

        // Name
        if (request('name')) {
            $query->where('name', 'like', '%' . request('name') . '%');
        }

        // Status
        if (request('status') !== null && request('status') !== '') {
            $query->where('status', request('status'));
        }

        // Final Data
        $categories = $query->paginate(perPage())->withQueryString();

        return view('pages.inventoryManagement.categories.index', compact('categories'));
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
        $user = Auth::user();

        // hotel_code = $user->hotel->hotel_code;

        try {

            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('inventory_categories')
                        ->where(
                            fn($query) =>
                            $query->where('hotel_id', $user->hotel_id)
                        ),
                ],
                'description' => 'nullable|string',
            ]);

            DB::beginTransaction();


            // Get last category of this hotel
            $code = CodeGenerator(
                'inventory_categories', // table
                'code',                 // column
                'CAT-',                 // prefix
                4,                      // padding
                $user->hotel_id         // hotel आधारित sequence
            );
            // dd($code);

            $category = InventoryCategory::create([
                'name'        => $validated['name'],
                'description' => $validated['description'] ?? null,
                'code'        => $code,
                'user_id'     => $user->id,
                'hotel_id'    => $user->hotel_id,
            ]);

            auditLog(
                module: 'Inventory Category',
                action: 'Create',
                recordId: $category->id,
                new: $category->toArray(),
                description: 'Inventory category created successfully'
            );

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Inventory category created successfully',
                'data'    => $category
            ], 200);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryCategory $category)
    {
        $data = [
            'title' => 'View Inventory Category Details',

            'fields' => [
                [
                    'label' => 'Name',
                    'value' => $category->name,
                ],
                [
                    'label' => 'Description',
                    'value' => $category->description,
                ],
                [
                    'label' => 'Code',
                    'value' => $category->code,
                ],
                [
                    'label' => 'Status',
                    'value' => $category->status == 1 ? 'Active' : 'Inactive',
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryCategory $inventoryCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        try {

            $category = InventoryCategory::where('hotel_id', $user->hotel_id)
                ->findOrFail($id);

            $hotelId = $user->hotel_id;

            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('inventory_categories')
                        ->where(fn($query) => $query->where('hotel_id', $hotelId))
                        ->ignore($category->id),
                ],
                'description' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $oldData = $category->toArray();

            $category->update([
                'name'        => $validated['name'],
                'description' => $validated['description'] ?? null,
                'status'      => $request->status
            ]);

            auditLog(
                module: 'Inventory Category',
                action: 'Update',
                recordId: $category->id,
                old: $oldData,
                new: $category->fresh()->toArray(),
                description: 'Inventory category updated successfully'
            );

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Inventory category updated successfully',
                'data'    => $category
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
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryCategory $inventoryCategory)
    {
        //
    }

}
