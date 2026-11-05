<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryVendor;
use App\Models\InventoryCategory;
use App\Models\InventoryUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;


class InventoryItemController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:item_master,view', only: ['index', 'show']),
            new Middleware('permission:item_master,create', only: ['create', 'store']),
            new Middleware('permission:item_master,edit', only: ['edit', 'update']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = InventoryItem::with(['category', 'unit', 'vendor'])
            ->where('hotel_id', $user->hotel_id);

        // 🔍 FILTERS

        // Name
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // Category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Unit
        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        // Vendor
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        // Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }



        // 🔽 ORDER + PAGINATION
        $items = $query->orderBy('id', 'desc')
            ->paginate(perPage())
            ->withQueryString();

        // 📦 Dropdown data
        $categories = InventoryCategory::where('hotel_id', $user->hotel_id)
            ->where('status', 1)
            ->get();

        $units = InventoryUnit::where('status', 1)->get(); // global

        $vendors = InventoryVendor::where('hotel_id', $user->hotel_id)
            ->where('status', 1)
            ->get();

        return view('pages.inventoryManagement.items.index', compact(
            'items',
            'categories',
            'units',
            'vendors'
        ));
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
            // Validation
            $request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:inventory_categories,id',
                'unit_id' => 'required|exists:inventory_units,id',
                'vendor_id' => 'nullable|exists:inventory_vendors,id',

                'opening_stock' => 'nullable|numeric|min:0',
                'minimum_stock' => 'nullable|numeric|min:0',
                'maximum_stock' => 'nullable|numeric|min:0',

                'purchase_price' => 'nullable|numeric|min:0',
                'selling_price' => 'nullable|numeric|min:0',
                'tax_percentage' => 'nullable|numeric|min:0',
            ]);

            DB::beginTransaction();

            $itemCode = CodeGenerator(
                'inventory_items',
                'item_code',
                'ITM-',
                4,
                Auth::user()->hotel_id
            );

            // Create Item
            $item = InventoryItem::create([
                'hotel_id' => Auth::user()->hotel_id ?? null,
                'user_id' => Auth::id(),

                'name' => $request->name,
                'item_code' => $itemCode,

                'category_id' => $request->category_id,
                'unit_id' => $request->unit_id,
                'vendor_id' => $request->vendor_id,

                'barcode' => $request->barcode,
                'description' => $request->description,

                'opening_stock' => $request->opening_stock ?? 0,
                'minimum_stock' => $request->minimum_stock ?? 0,
                'maximum_stock' => $request->maximum_stock,

                'purchase_price' => $request->purchase_price ?? 0,
                'selling_price' => $request->selling_price,
                'tax_percentage' => $request->tax_percentage,

                'status' => 1,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Item created successfully',
                'data' => $item
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Item Store Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryItem $Item)
    {
        $data = [
            'title' => 'View Item Master Details',

            'fields' => [
                [
                    'label' => 'Name',
                    'value' => $Item->name,
                ],   
                [
                    'label' => 'Category',
                    'value' => $Item->category ? $Item->category->name : 'N/A',
                ],   
                [
                    'label' => 'Item Code',
                    'value' => $Item->item_code,
                ],   
                [
                    'label' => 'Barcode',
                    'value' => $Item->barcode,
                ],   
                [
                    'label' => 'Unit',
                    'value' => $Item->unit ? $Item->unit->name : 'N/A',
                ],   
                [
                    'label' => 'Vendor',
                    'value' => $Item->vendor ? $Item->vendor->name : 'N/A',
                ],
                [
                    'label' => 'Opening Stock',
                    'value' => $Item->opening_stock,
                ], 
                [
                    'label' => 'Minimum Stock',
                    'value' => $Item->minimum_stock,
                ], 
                [
                    'label' => 'Maximum Stock',
                    'value' => $Item->maximum_stock,
                ], 
                [
                    'label' => 'Purchase Price',
                    'value' => $Item->purchase_price,
                ], 
                [
                    'label' => 'Selling Price',
                    'value' => $Item->selling_price,
                ], 
                [
                    'label' => 'Tax Percentage',
                    'value' => $Item->tax_percentage . '%',
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryItem $inventoryItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {

            $inventoryItem = InventoryItem::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:inventory_categories,id',
                'unit_id' => 'required|exists:inventory_units,id',
                'vendor_id' => 'nullable|exists:inventory_vendors,id',
                'status' => 'required|in:0,1',
            ]);

            DB::beginTransaction();

            $inventoryItem->update([
                'name' => $request->name,
                'item_code' => $request->item_code,
                'category_id' => $request->category_id,
                'unit_id' => $request->unit_id,
                'vendor_id' => $request->vendor_id,
                'barcode' => $request->barcode,
                'description' => $request->description,
                'opening_stock' => $request->opening_stock ?? 0,
                'current_stock' => $request->opening_stock ?? $inventoryItem->current_stock,
                'minimum_stock' => $request->minimum_stock ?? 0,
                'maximum_stock' => $request->maximum_stock,
                'purchase_price' => $request->purchase_price ?? 0,
                'selling_price' => $request->selling_price,
                'tax_percentage' => $request->tax_percentage,
                'status' => $request->status,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Item updated successfully',
                'data' => $inventoryItem
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Item Update Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }


    public function destroy(InventoryItem $inventoryItem)
    {
        //
    }

    public function getItemsByCategory($id)
    {
        return InventoryItem::with('unit')->where('hotel_id', Auth::user()->hotel_id)
            ->where('category_id', $id)
            ->where('status', 1)
            ->get();
    }
}
