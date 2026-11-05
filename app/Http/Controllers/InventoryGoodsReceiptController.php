<?php

namespace App\Http\Controllers;

use App\Models\InventoryCategory;
use App\Models\InventoryGoodsReceipt;
use App\Models\InventoryItem;
use App\Models\InventoryPurchaseOrder;
use App\Models\InventoryStockLedger;
use App\Models\InventoryPurchaseOrderItem;
use App\Models\InventoryVendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class InventoryGoodsReceiptController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:goods_receipt,view', only: ['index', 'show', 'getPOByVendor']),
            new Middleware('permission:goods_receipt,create', only: ['create', 'store']),
            new Middleware('permission:goods_receipt,edit', only: ['edit', 'update']),
            new Middleware('permission:goods_receipt,delete', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $request = request();

        $vendors = InventoryVendor::where('hotel_id', auth()->user()->hotel_id)
            ->where('status', 1)
            ->get();

        $purchaseOrders = InventoryPurchaseOrder::whereIn('status', ['approved', 'sent', 'partially_received'])
            ->with('vendor')
            ->get();

        $query = InventoryGoodsReceipt::with(['vendor', 'purchaseOrder', 'user'])
            ->where('hotel_id', auth()->user()->hotel_id);

        // Filters
        if ($request->filled('grn_number')) {
            $query->where('grn_number', 'like', '%' . $request->grn_number . '%');
        }

        if ($request->filled('grn_date')) {
            $query->whereDate('grn_date', $request->grn_date);
        }

        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }

        if ($request->filled('grand_total')) {
            $query->where('grand_total', $request->grand_total);
        }

        if ($request->filled('vendor_name')) {
            $query->whereHas('vendor', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->vendor_name . '%');
            });
        }

        if ($request->filled('po_number')) {
            $query->whereHas('purchaseOrder', function ($q) use ($request) {
                $q->where('po_number', 'like', '%' . $request->po_number . '%');
            });
        }

        if ($request->filled('user_name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->user_name . '%');
            });
        }

        $grns = $query->orderByDesc('id')
            ->paginate(perPage())
            ->withQueryString();

        return view('pages.inventoryManagement.grn.index', compact(
            'vendors',
            'purchaseOrders',
            'grns'
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
        DB::beginTransaction();
        // dd($request->all());
        try {

            $request->validate([
                'vendor_id' => 'required|exists:inventory_vendors,id',
                'purchase_order_id' => 'nullable|exists:inventory_purchase_orders,id',
                'grn_date' => 'required|date',
                'invoice_number' => 'nullable|string',
                'invoice_date' => 'nullable|date',

                'discount_amount' => 'nullable|numeric|min:0',

                'items' => 'required|array',

                'items.*.item_id' => 'required|exists:inventory_items,id',
                'items.*.unit_id' => 'required|exists:inventory_units,id',
                'items.*.received_quantity' => 'required|numeric|min:0.01',
                'items.*.rate' => 'required|numeric|min:0',
                'items.*.tax_percentage' => 'nullable|numeric|min:0',
            ]);

            $hotelId = auth()->user()->hotel_id;
            $userId  = auth()->id();

            // ✅ SAFE GRN NUMBER (USE YOUR HELPER)
            $grnNumber = CodeGenerator(
                'inventory_goods_receipts',
                'grn_number',
                'GRN-',
                5,
                $hotelId
            );

            $subTotal = 0;
            $taxTotal = 0;

            // 🧾 CREATE GRN MASTER
            $grn = InventoryGoodsReceipt::create([
                'hotel_id' => $hotelId,
                'user_id' => $userId,
                'purchase_order_id' => $request->purchase_order_id,
                'vendor_id' => $request->vendor_id,

                'grn_number' => $grnNumber,
                'grn_date' => $request->grn_date,

                'invoice_number' => $request->invoice_number,
                'invoice_date' => $request->invoice_date,

                'notes' => $request->notes ?? null,

                'sub_total' => 0,
                'tax_amount' => 0,
                'discount_amount' => $request->discount_amount ?? 0,
                'grand_total' => 0,
            ]);

            foreach ($request->items as $item) {

                $inventoryItem = InventoryItem::lockForUpdate()->find($item['item_id']);

                $poItem = InventoryPurchaseOrderItem::find($item['purchase_order_item_id']);

                if ($poItem) {

                    $alreadyReceived = $poItem->received_quantity ?? 0;
                    $remainingQty = $poItem->quantity - $alreadyReceived;

                    if ($item['received_quantity'] > $remainingQty) {
                        throw ValidationException::withMessages([
                            'items' => [
                                "Only {$remainingQty} quantity remaining for item ID {$item['item_id']}"
                            ]
                        ]);
                    }
                }

                $qty = (float) $item['received_quantity'];
                $rate = (float) $item['rate'];
                $taxPercent = (float) ($item['tax_percentage'] ?? 0);

                $base = $qty * $rate;
                $taxAmount = ($base * $taxPercent) / 100;
                $total = $base + $taxAmount;

                $subTotal += $base;
                $taxTotal += $taxAmount;

                // 📦 GRN ITEM
                $grn->items()->create([
                    'purchase_order_item_id' => $item['purchase_order_item_id'] ?? null,
                    'item_id' => $item['item_id'],
                    'unit_id' => $item['unit_id'],
                    'received_quantity' => $qty,
                    'rate' => $rate,
                    'tax_percentage' => $taxPercent,
                    'tax_amount' => $taxAmount,
                    'total' => $total,
                ]);

                // 📊 STOCK LEDGER
                $lastBalance = InventoryStockLedger::where('hotel_id', $hotelId)
                    ->where('item_id', $item['item_id'])
                    ->latest('id')
                    ->value('balance_after') ?? 0;

                $newBalance = $lastBalance + $qty;

                InventoryStockLedger::create([
                    'hotel_id' => $hotelId,
                    'item_id' => $item['item_id'],
                    'type' => 'GRN',
                    'reference_number' => $grnNumber,
                    'quantity' => $qty,
                    'flow' => 'in',
                    'balance_after' => $newBalance,
                ]);

                // 🔁 UPDATE PO ITEM RECEIVED QTY
                if (!empty($item['purchase_order_item_id'])) {

                    $poItem = InventoryPurchaseOrderItem::find($item['purchase_order_item_id']);

                    if ($poItem) {
                        $poItem->received_quantity = ($poItem->received_quantity ?? 0) + $qty;
                        $poItem->save();
                    }
                }

                $inventoryItem->current_stock += $qty;
                $inventoryItem->save();
            }

            // 💰 GRAND TOTAL (IMPORTANT FIX)
            $grandTotal = $subTotal + $taxTotal - ($request->discount_amount ?? 0);

            $grn->update([
                'sub_total' => $subTotal,
                'tax_amount' => $taxTotal,
                'grand_total' => $grandTotal,
            ]);

            // 📦 UPDATE PO STATUS (FIXED LOGIC)
            if ($request->purchase_order_id) {

                $po = InventoryPurchaseOrder::with('items')->find($request->purchase_order_id);

                $totalOrdered = 0;
                $totalReceived = 0;

                foreach ($po->items as $poItem) {
                    $totalOrdered += $poItem->quantity;
                    $totalReceived += $poItem->received_quantity;
                }

                if ($totalReceived == 0) {
                    $status = $po->status;
                } elseif ($totalReceived < $totalOrdered) {
                    $status = 'partially_received';
                } else {
                    $status = 'completed';
                }

                $po->update([
                    'status' => $status
                ]);
            }

            DB::commit();

            auditLog(
                module: 'GRN',
                action: 'Create',
                recordId: $grn->id,
                new: [
                    'grn_number' => $grnNumber,
                    'vendor_id' => $request->vendor_id,
                    'purchase_order_id' => $request->purchase_order_id,
                    'sub_total' => $subTotal,
                    'tax_amount' => $taxTotal,
                    'discount' => $request->discount_amount ?? 0,
                    'grand_total' => $grandTotal,
                    'items_count' => count($request->items)
                ],
                description: "GRN {$grnNumber} created successfully",
                event: 'grn_created'
            );

            return response()->json([
                'status' => true,
                'message' => 'GRN created successfully',
                'grn_id' => $grn->id
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryGoodsReceipt $grn)
    { 
        try {
            $data = [
                'title' => 'View Goods Receipt Details',

                'fields' => [
                    [
                        'label' => 'GRN Number',
                        'value' => $grn->grn_number ?? '-',
                    ],
                    [
                        'label' => 'Date',
                        'value' => $grn->grn_date ? formatDate($grn->grn_date) : '-',
                    ],
                    [
                        'label' => 'Invoice Number',
                        'value' => $grn->invoice_number ?? '-',
                    ],
                    [
                        'label' => 'Invoice Date',
                        'value' => $grn->invoice_date ? formatDate($grn->invoice_date) : '-',
                    ],
                    [
                        'label' => 'Vendor',
                        'value' => optional($grn->vendor)->name ?? '-',
                    ],
                    [
                        'label' => 'Purchase Order',
                        'value' => optional($grn->purchaseOrder)->po_number ?? '-',
                    ],
                    [
                        'label' => 'Sub Total',
                        'value' => $grn->sub_total ?? 0,
                    ],
                    [
                        'label' => 'Tax Amount',
                        'value' => $grn->tax_amount ?? 0,
                    ],
                    [
                        'label' => 'Discount',
                        'value' => $grn->discount_amount ?? 0,
                    ],
                    [
                        'label' => 'Grand Total',
                        'value' => $grn->grand_total ?? 0,
                    ],
                    [
                        'label' => 'Created At',
                        'value' => $grn->created_at ? formatDateTime($grn->created_at) : '-',
                    ], 
                ],

                'table' => null,
            ];

            return view('layouts.dynamic-view-content', compact('data'))->render();

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryGoodsReceipt $inventoryGoodsReceipt)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InventoryGoodsReceipt $inventoryGoodsReceipt)
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

            $hotelId = auth()->user()->hotel_id;

            $grn = InventoryGoodsReceipt::with('items')
                ->where('hotel_id', $hotelId)
                ->findOrFail($id);

            // ❌ Prevent double delete
            if ($grn->deleted_at) {
                throw new \Exception("GRN already deleted");
            }

            foreach ($grn->items as $item) {

                $qty = $item->received_quantity;

                // 🔒 Lock item
                $inventoryItem = InventoryItem::lockForUpdate()->find($item->item_id);

                if ($inventoryItem->current_stock < $qty) {
                    throw new \Exception("Cannot delete GRN. Stock already consumed for {$inventoryItem->name}");
                }
                if (!$inventoryItem) continue;

                // =========================
                // 🔻 1. REVERSE STOCK
                // =========================
                $inventoryItem->current_stock -= $qty;

                if ($inventoryItem->current_stock < 0) {
                    throw new \Exception("Stock cannot go negative for {$inventoryItem->name}");
                }

                $inventoryItem->save();

                // =========================
                // 📊 2. LEDGER ENTRY (REVERSE)
                // =========================
                InventoryStockLedger::create([
                    'hotel_id' => $hotelId,
                    'item_id' => $item->item_id,
                    'type' => 'GRN_DELETE',
                    'reference_number' => $grn->grn_number,
                    'quantity' => $qty,
                    'flow' => 'out',
                    'balance_after' => $inventoryItem->current_stock,
                ]);

                // =========================
                // 🔁 3. UPDATE PO ITEM
                // =========================
                if ($item->purchase_order_item_id) {

                    $poItem = InventoryPurchaseOrderItem::lockForUpdate()
                        ->find($item->purchase_order_item_id);

                    if ($poItem) {
                        $poItem->received_quantity -= $qty;

                        if ($poItem->received_quantity < 0) {
                            $poItem->received_quantity = 0;
                        }

                        $poItem->save();
                    }
                }
            }

            // =========================
            // 🔄 4. UPDATE PO STATUS AGAIN
            // =========================
            if ($grn->purchase_order_id) {

                $po = InventoryPurchaseOrder::with('items')
                    ->lockForUpdate()
                    ->find($grn->purchase_order_id);

                $totalOrdered = 0;
                $totalReceived = 0;

                foreach ($po->items as $poItem) {
                    $totalOrdered += $poItem->quantity;
                    $totalReceived += $poItem->received_quantity;
                }

                if ($totalReceived == 0) {
                    $status = 'approved';
                } elseif ($totalReceived < $totalOrdered) {
                    $status = 'partially_received';
                } else {
                    $status = 'completed';
                }

                $po->update(['status' => $status]);
            }

            // =========================
            // 🗑️ 5. SOFT DELETE
            // =========================
            $grn->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'GRN deleted & stock reversed successfully'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getPOByVendor($vendorId)
    {
        $pos = InventoryPurchaseOrder::where('vendor_id', $vendorId)
            ->whereIn('status', ['approved', 'sent', 'partially_received'])
            ->select('id', 'po_number')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $pos
        ]);
    }
}
