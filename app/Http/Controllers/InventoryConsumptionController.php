<?php

namespace App\Http\Controllers;

use App\Models\InventoryCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\InventoryConsumption;
use App\Models\InventoryConsumptionItem;
use App\Models\InventoryItem;
use App\Models\InventoryStockLedger;
use App\Models\User;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class InventoryConsumptionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:consumption,view', only: ['index', 'show']),
            new Middleware('permission:consumption,create', only: ['create', 'store']),
            new Middleware('permission:consumption,delete', only: ['destroy']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::where('hotel_id', Auth::user()->hotel_id)->get();
        $user = Auth::user();

        $categories = InventoryCategory::where('hotel_id', $user->hotel_id)
            ->where('status', 1)
            ->get();

        $query = InventoryConsumption::with([
            'category',
            'items.item',
            'items.unit',
            'createdBy'
        ])->where('hotel_id', $user->hotel_id);

        // 🔍 Filter: Consumption Number
        if ($request->consumption_number) {
            $query->where('consumption_number', 'like', '%' . $request->consumption_number . '%');
        }

        // 🔍 Filter: Date
        if ($request->consumption_date) {
            $query->whereDate('consumption_date', $request->consumption_date);
        }

        // 🔍 Filter: Category
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // 🔍 Filter: Created By
        if ($request->created_by_name) {
            $query->whereHas('createdBy', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->created_by_name . '%');
            });
        }
 

        $consumptions = $query->latest()
            ->paginate(perPage())
            ->withQueryString();

        return view('pages.inventoryManagement.consumptions.index', compact('categories', 'consumptions', 'users'));
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
                'consumption_date' => 'required|date',
                'category_id'      => 'required|exists:inventory_categories,id',

                'items'                => 'required|array|min:1',
                'items.*.item_id'      => 'required|exists:inventory_items,id',
                'items.*.unit_id'      => 'required|exists:inventory_units,id',
                'items.*.quantity'     => 'required|numeric|min:0.01',

                'notes' => 'nullable|string'
            ]);

            DB::beginTransaction();

            $hotelId = auth()->user()->hotel_id;

            // ================= GENERATE CODE =================
            $code = CodeGenerator(
                'inventory_consumptions',
                'consumption_number',
                'CON-',
                4,
                $hotelId
            );

            // ================= CREATE MAIN =================
            $consumption = InventoryConsumption::create([
                'hotel_id'          => $hotelId,
                'user_id'           => auth()->id(),
                'category_id'       => $request->category_id,
                'consumption_date'  => $request->consumption_date,
                'consumption_number' => $code,
                'notes'             => $request->notes,
            ]);

            // ================= ITEMS LOOP =================
            foreach ($request->items as $item) {

                $inventoryItem = InventoryItem::lockForUpdate()->find($item['item_id']);

                if (!$inventoryItem) {
                    throw ValidationException::withMessages([
                        'items' => ['Invalid item selected']
                    ]);
                }

                // ================= STOCK CHECK =================
                if ($item['quantity'] > $inventoryItem->current_stock) {
                    throw ValidationException::withMessages([
                        'items' => ["{$inventoryItem->name} stock is only {$inventoryItem->current_stock}"]
                    ]);
                }

                // ================= SAVE ITEM =================
                InventoryConsumptionItem::create([
                    'consumption_id' => $consumption->id,
                    'item_id'        => $item['item_id'],
                    'unit_id'        => $item['unit_id'],
                    'quantity'       => $item['quantity'],
                ]);

                // ================= STOCK LEDGER ENTRY =================
                $before = $inventoryItem->current_stock;

                $after = $before - $item['quantity'];

                InventoryStockLedger::create([
                    'hotel_id'         => $hotelId,
                    'item_id'          => $inventoryItem->id,
                    'type'             => 'consumption',
                    'reference_number' => $consumption->consumption_number,
                    'quantity'         => $item['quantity'],
                    'flow'             => 'out',
                    'balance_after'    => $after,
                    'created_at'       => now(),
                ]);

                // ================= STOCK UPDATE =================
                $inventoryItem->current_stock = $after;
                $inventoryItem->save();

                $admin = getHotelAdmin($hotelId);

                if (
                    $inventoryItem->current_stock <= ($inventoryItem->minimum_stock * 0.3)
                ) {
                    if ($admin) {
                        sendNotification(
                            title: 'Critical Stock Alert',
                            message: "{$inventoryItem->name} is critically low ({$inventoryItem->current_stock}) after consumption",
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
                module: 'Consumption',
                action: 'Create',
                recordId: $consumption->id,
                new: [
                    'consumption_number' => $code,
                    'category_id' => $request->category_id,
                    'consumption_date' => $request->consumption_date,
                    'items_count' => count($request->items)
                ],
                description: "Consumption {$code} created successfully",
                event: 'consumption_created'
            );

            return response()->json([
                'status'  => true,
                'message' => 'Consumption saved successfully'
            ]);
        } catch (ValidationException $e) {

            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryConsumption $Consumption)
    {
        $data = [
            'title' => 'View Consumption Details',

            'fields' => [
                [
                    'label' => 'Consumption Number',
                    'value' => $Consumption->consumption_number,
                ],
                [
                    'label' => 'Consumption Date',
                    'value' => formatDateTime($Consumption->consumption_date),
                ],
                [
                    'label' => 'Category',
                    'value' => $Consumption->category->name ?? 'N/A',
                ], 
                 [
                    'label' => 'Total Items',
                    'value' => $Consumption->items->count(),
                ],
                 [
                    'label' => 'Total Quantity',
                    'value' => $Consumption->items->map(function ($item) {
                        return $item->item->name . ' (' . $item->quantity . ' ' . $item->unit->short_name . ')';
                    })->implode(', ')
                ],
                 [
                    'label' => 'Created By',
                    'value' => $Consumption->createdBy->name ?? 'N/A',
                ],
                [
                    'label' => 'Notes',
                    'value' => $Consumption->notes ?? 'N/A',
                ],
                 [
                    'label' => 'Deleted At',
                    'value' => formatDateTime($Consumption->deleted_at),
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryConsumption $inventoryConsumption)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InventoryConsumption $inventoryConsumption)
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

            $consumption = InventoryConsumption::with('items')->findOrFail($id);

            // ❌ Prevent double delete
            if ($consumption->deleted_at) {
                throw new \Exception("Already deleted");
            }

            foreach ($consumption->items as $item) {

                $inventoryItem = InventoryItem::lockForUpdate()->find($item->item_id);

                if (!$inventoryItem) continue;
                $before = $inventoryItem->current_stock;

                // ================= STOCK REVERSE =================
                $inventoryItem->current_stock += $item->quantity;
                $inventoryItem->save();
                $after = $inventoryItem->current_stock;
                // ================= LEDGER ENTRY (REVERSAL) =================
                InventoryStockLedger::create([
                    'hotel_id'         => auth()->user()->hotel_id,
                    'item_id'          => $item->item_id,
                    'type'             => 'CONSUMPTION_DELETE',
                    'reference_number' => $consumption->consumption_number,
                    'quantity'         => $item->quantity,
                    'flow'             => 'in', // 🔥 reverse = IN
                    'balance_after'    => $inventoryItem->current_stock,
                ]);

                $admin = getHotelAdmin(auth()->user()->hotel_id);

                // ✅ STOCK RECOVERED FROM LOW
                if (
                    $before <= $inventoryItem->minimum_stock &&
                    $after > $inventoryItem->minimum_stock
                ) {
                    if ($admin) {
                        sendNotification(
                            title: 'Stock Restored',
                            message: "{$inventoryItem->name} stock restored to {$after} after consumption deletion",
                            receiverId: $admin->id,
                            referenceType: 'item',
                            referenceId: $inventoryItem->id,
                            priority: 'medium'
                        );
                    }
                }
            }

            // ✅ Soft delete
            $consumption->delete();

            DB::commit();

            auditLog(
                module: 'Consumption',
                action: 'Delete',
                recordId: $consumption->id,
                old: [
                    'consumption_number' => $consumption->consumption_number,
                    'items_count' => $consumption->items->count()
                ],
                description: "Consumption {$consumption->consumption_number} deleted and stock restored",
                event: 'consumption_deleted'
            );

            return response()->json([
                'status' => true,
                'message' => 'Consumption deleted & stock restored'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ]);
        }
    }
}
