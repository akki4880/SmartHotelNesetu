<?php

namespace App\Http\Controllers;

use App\Models\InventoryStockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\InventoryItem;
use App\Models\InventoryStockAdjustmentItem;
use App\Models\InventoryStockLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class InventoryStockAdjustmentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:stock_adjustments,view', only: ['index', 'show']),
            new Middleware('permission:stock_adjustments,create', only: ['create', 'store']),
            new Middleware('permission:stock_adjustments,delete', only: ['destroy']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $hotelId = $user->hotel_id;

        $users = User::where('hotel_id', $hotelId)->get();

        $items = InventoryItem::where('hotel_id', $hotelId)
            ->where('status', 1)
            ->get();

        $query = InventoryStockAdjustment::with(['items.item', 'items.unit', 'user'])
            ->where('hotel_id', $hotelId);

        // ================= FILTERS =================

        // 🔍 Adjustment Number
        if ($request->filled('adjustment_number')) {
            $query->where('adjustment_number', 'like', '%' . $request->adjustment_number . '%');
        }

        // 🔍 Single Date
        if ($request->filled('adjustment_date')) {
            $query->whereDate('adjustment_date', $request->adjustment_date);
        }

        // 🔍 Date Range
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('adjustment_date', [
                $request->from_date,
                $request->to_date
            ]);
        }

        // 🔍 User Filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // 🔍 Item Name (relation)
        if ($request->filled('item_name')) {
            $query->whereHas('items.item', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->item_name . '%');
            });
        }

        // 🔥 Adjustment Type (Excess / Short)
        if ($request->filled('adjustment_type')) {
            $query->whereHas('items', function ($q) use ($request) {
                if ($request->adjustment_type === 'excess') {
                    $q->where('difference', '>', 0);
                } else {
                    $q->where('difference', '<', 0);
                }
            });
        }

        // ================= PAGINATION =================
        $adjustments = $query->orderByDesc('id')
            ->paginate(perPage())
            ->withQueryString();

        return view(
            'pages.inventoryManagement.stock-adjustments.index',
            compact('items', 'adjustments', 'users')
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
        try {

            // ================= VALIDATION =================
            $validated = $request->validate([
                'adjustment_date' => 'required|date',

                'items' => 'required|array|min:1',
                'items.*.item_id' => 'required|distinct|exists:inventory_items,id',
                'items.*.unit_id' => 'required|exists:inventory_units,id',
                'items.*.physical_stock' => 'required|numeric|min:0',

                'notes' => 'nullable|string'
            ]);

            DB::beginTransaction();

            $hotelId = auth()->user()->hotel_id;

            // ================= GENERATE CODE =================
            $code = CodeGenerator(
                'inventory_stock_adjustments',
                'adjustment_number',
                'ADJ-',
                4,
                $hotelId
            );

            // ================= CREATE MAIN =================
            $adjustment = InventoryStockAdjustment::create([
                'hotel_id' => $hotelId,
                'user_id' => auth()->id(),
                'adjustment_number' => $code,
                'adjustment_date' => $request->adjustment_date,
                'notes' => $request->notes,
            ]);

            // ================= LOOP ITEMS =================
            foreach ($request->items as $item) {

                $inventoryItem = InventoryItem::lockForUpdate()->find($item['item_id']);

                if (!$inventoryItem) {
                    throw ValidationException::withMessages([
                        'items' => ['Invalid item selected']
                    ]);
                }

                // ================= STOCK VALUES =================
                $systemStock = $inventoryItem->current_stock;
                $physicalStock = $item['physical_stock'];

                $difference = $physicalStock - $systemStock;

                // ================= SAVE ITEM =================
                InventoryStockAdjustmentItem::create([
                    'adjustment_id' => $adjustment->id,
                    'item_id' => $item['item_id'],
                    'unit_id' => $item['unit_id'],
                    'system_stock' => $systemStock,
                    'physical_stock' => $physicalStock,
                    'difference' => $difference,
                ]);

                // ================= UPDATE STOCK =================
                $inventoryItem->current_stock = $physicalStock;
                $inventoryItem->save();

                // ================= LEDGER ENTRY =================
                if ($difference != 0) {

                    InventoryStockLedger::create([
                        'hotel_id'         => $hotelId,
                        'item_id'          => $item['item_id'],
                        'type'             => 'ADJUSTMENT',
                        'reference_number' => $adjustment->adjustment_number,
                        'quantity'         => abs($difference),
                        'flow'             => $difference > 0 ? 'in' : 'out',
                        'balance_after'    => $physicalStock,
                    ]);
                }

                if ($inventoryItem->current_stock <= ($inventoryItem->minimum_stock * 0.3)) {

                    $admin = getHotelAdmin($hotelId);

                    if ($admin) {
                        sendNotification(
                            title: 'Critical Stock Alert',
                            message: "{$inventoryItem->name} is critically low ({$inventoryItem->current_stock})",
                            receiverId: $admin->id,
                            referenceType: 'item',
                            referenceId: $inventoryItem->id,
                            priority: 'high'
                        );
                    }
                }
            }

            DB::commit();

            auditLog(
                module: 'Stock Adjustment',
                action: 'Create',
                recordId: $adjustment->id,
                new: [
                    'adjustment_number' => $code,
                    'adjustment_date' => $request->adjustment_date,
                    'items_count' => count($request->items)
                ],
                description: "Stock Adjustment {$code} created successfully",
                event: 'stock_adjustment_created'
            );

            return response()->json([
                'status' => true,
                'message' => 'Stock adjusted successfully'
            ]);
        } catch (ValidationException $e) {

            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage() // remove in production
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryStockAdjustment $StockAdjustment)
    {
        $data = [
            'title' => 'View StockAdjustment Details',

            'fields' => [
                [
                    'label' => 'Adjustment Number',
                    'value' => $StockAdjustment->adjustment_number,
                ],
                [
                    'label' => 'Adjustment Date',
                    'value' => formatDateTime($StockAdjustment->adjustment_date),
                ],
                [
                    'label' => 'Total Items',
                    'value' => $StockAdjustment->items->count(),
                ],
                [
                    'label' => 'Total Adjusted Quantity',
                    'value' => $StockAdjustment->items->map(function ($item) {

                        $diff = $item->adjusted_qty - $item->current_qty; // adjust if your field names differ

                        return $item->item->name . ' (' .
                            ($diff > 0 ? 'Excess +' : 'Short -') .
                            number_format(abs($diff), 2) . ' ' .
                            $item->unit->short_name . ')';
                    })->implode(', ')
                ],
                [
                    'label' => 'Notes',
                    'value' => $StockAdjustment->notes,
                ],
                [
                    'label' => 'Created By',
                    'value' => $StockAdjustment->user ? $StockAdjustment->user->name : 'N/A',
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryStockAdjustment $inventoryStockAdjustment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InventoryStockAdjustment $inventoryStockAdjustment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {

            DB::beginTransaction();

            $adjustment = InventoryStockAdjustment::with('items')->findOrFail($id);
            $admin = getHotelAdmin(auth()->user()->hotel_id);

            // ❌ Prevent double delete
            if ($adjustment->deleted_at) {
                throw new \Exception("Adjustment already deleted");
            }

            foreach ($adjustment->items as $item) {

                $inventoryItem = InventoryItem::lockForUpdate()->find($item->item_id);

                if (!$inventoryItem) {
                    throw new \Exception("Item not found");
                }

                // ================= REVERSE STOCK =================
                // If adjustment added stock → now remove
                // If adjustment removed stock → now add back

                if ($item->difference > 0) {
                    // Previously added → now subtract
                    $inventoryItem->current_stock -= $item->difference;
                } else {
                    // Previously reduced → now add back
                    $inventoryItem->current_stock += abs($item->difference);
                }

                $inventoryItem->save();

                // ================= DELETE LEDGER =================
                InventoryStockLedger::where([
                    'item_id' => $item->item_id,
                    'reference_number' => $adjustment->adjustment_number,
                    'type' => 'ADJUSTMENT'
                ])->delete();


                if (
                    $inventoryItem->current_stock <= ($inventoryItem->minimum_stock * 0.3)
                ) {
                    if ($admin) {
                        sendNotification(
                            title: 'Critical Stock Alert',
                            message: "{$inventoryItem->name} is critically low after adjustment deletion ({$inventoryItem->current_stock})",
                            receiverId: $admin->id,
                            referenceType: 'item',
                            referenceId: $inventoryItem->id,
                            priority: 'high'
                        );
                    }
                }
            }

            // ================= DELETE ITEMS =================
            $adjustment->items()->delete();

            // ================= SOFT DELETE MAIN =================
            $adjustment->delete();

            DB::commit();

            auditLog(
                module: 'Stock Adjustment',
                action: 'Delete',
                recordId: $adjustment->id,
                old: [
                    'adjustment_number' => $adjustment->adjustment_number,
                    'items_count' => $adjustment->items->count()
                ],
                description: "Stock Adjustment {$adjustment->adjustment_number} deleted and stock restored",
                event: 'stock_adjustment_deleted'
            );

            $user = auth()->user();

            if ($admin && $admin->id != $user->id) {
                sendNotification(
                    title: 'Stock Adjustment Deleted',
                    message: "{$user->name} deleted stock adjustment (Ref: {$adjustment->adjustment_number})",
                    receiverId: $admin->id,
                    referenceType: 'stock_adjustment',
                    referenceId: $adjustment->id,
                    priority: 'high'
                );
            }

            return response()->json([
                'status' => true,
                'message' => 'Stock Adjustment deleted & stock restored'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage() // remove in production
            ], 500);
        }
    }
}
