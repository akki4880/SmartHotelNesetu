<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryPurchaseOrder;
use App\Models\InventoryVendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\GlobalMail;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class InventoryPurchaseOrderController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:purchase_orders,view', only: ['index', 'show', 'getItems']),
            new Middleware('permission:purchase_orders,create', only: ['create', 'store']),
            new Middleware('permission:purchase_orders,edit', only: ['edit', 'update']),
            new Middleware('permission:purchase_orders,cancel', only: ['destroy', 'changeStatus']),
            new Middleware('permission:purchase_orders,approve', only: ['changeStatus']),
            new Middleware('permission:purchase_orders,sendmail', only: ['changeStatus']),
            new Middleware('permission:purchase_orders,download', only: ['download']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $vendors = InventoryVendor::where('hotel_id', auth()->user()->hotel_id)
            ->where('status', 1)
            ->get();

        $items = InventoryItem::where('hotel_id', auth()->user()->hotel_id)
            ->where('status', 1)
            ->get();

        $query = InventoryPurchaseOrder::with(['vendor', 'user', 'items.unit'])
            ->where('hotel_id', auth()->user()->hotel_id);

        // 🔍 PO Number
        if ($request->po_number) {
            $query->where('po_number', 'like', '%' . $request->po_number . '%');
        }

        // 🔍 Vendor Name
        if ($request->vendor_name) {
            $query->whereHas('vendor', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->vendor_name . '%');
            });
        }

        // 🔍 PO Date
        if ($request->po_date) {
            $query->whereDate('po_date', $request->po_date);
        }

        // 🔍 Delivery Date
        if ($request->expected_delivery_date) {
            $query->whereDate('expected_delivery_date', $request->expected_delivery_date);
        }

        // 🔍 Amount
        if ($request->amount) {
            $query->where('grand_total', '<=', $request->amount);
        }

        // 🔍 Total Items
        if ($request->items_count) {
            $query->whereHas('items', function ($q) use ($request) {
                $q->selectRaw('purchase_order_id, COUNT(*) as total_items')
                    ->groupBy('purchase_order_id')
                    ->havingRaw('COUNT(*) = ?', [$request->items_count]);
            });
        }

        // 🔍 Created By
        if ($request->created_by) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->created_by . '%');
            });
        }

        // 🔍 Status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $purchaseOrders = $query->latest()->paginate(perPage())->withQueryString();

        return view('pages.inventoryManagement.purchaseOrders.index', compact('vendors', 'items', 'purchaseOrders'));
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

        try {

            // ================= VALIDATION =================
            $validated = $request->validate([
                'vendor_id' => 'required|exists:inventory_vendors,id',
                'po_date' => 'required|date',
                'expected_delivery_date' => 'nullable|date|after_or_equal:po_date',

                'discount_amount' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string',

                'items' => 'required|array|min:1',

                'items.*.item_id' => 'required|exists:inventory_items,id',
                'items.*.unit_id' => 'required|exists:inventory_units,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.rate' => 'required|numeric|min:0',
                'items.*.tax_percentage' => 'nullable|numeric|min:0',
            ]);

            DB::beginTransaction();

            // ================= GENERATE PO NUMBER =================
            $hotelId = $user->hotel_id;

            $lastPO = DB::table('inventory_purchase_orders')
                ->where('hotel_id', $hotelId)
                ->latest('id')
                ->first();

            if ($lastPO && $lastPO->po_number) {
                $lastNumber = (int) substr($lastPO->po_number, -4);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $poNumber = 'PO-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            // ================= CALCULATE TOTALS =================
            $subTotal = 0;
            $taxTotal = 0;

            foreach ($validated['items'] as $item) {

                $qty = $item['quantity'];
                $rate = $item['rate'];
                $taxPercent = $item['tax_percentage'] ?? 0;

                $lineSubTotal = $qty * $rate;
                $taxAmount = ($lineSubTotal * $taxPercent) / 100;

                $subTotal += $lineSubTotal;
                $taxTotal += $taxAmount;
            }

            $discount = $validated['discount_amount'] ?? 0;

            $grandTotal = ($subTotal + $taxTotal) - $discount;

            // ================= CREATE PO =================
            $po = DB::table('inventory_purchase_orders')->insertGetId([
                'hotel_id' => $hotelId,
                'user_id' => $user->id,
                'vendor_id' => $validated['vendor_id'],

                'po_number' => $poNumber,
                'po_date' => $validated['po_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,

                'sub_total' => $subTotal,
                'tax_amount' => $taxTotal,
                'discount_amount' => $discount,
                'grand_total' => $grandTotal,

                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,

                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // ================= SAVE ITEMS =================
            foreach ($validated['items'] as $item) {

                $qty = $item['quantity'];
                $rate = $item['rate'];
                $taxPercent = $item['tax_percentage'] ?? 0;

                $lineSubTotal = $qty * $rate;
                $taxAmount = ($lineSubTotal * $taxPercent) / 100;

                $total = $lineSubTotal + $taxAmount;

                DB::table('inventory_purchase_order_items')->insert([
                    'purchase_order_id' => $po,
                    'item_id' => $item['item_id'],
                    'unit_id' => $item['unit_id'],

                    'quantity' => $qty,
                    'rate' => $rate,

                    'tax_percentage' => $taxPercent,
                    'tax_amount' => $taxAmount,

                    'discount_amount' => 0,
                    'total' => $total,

                    'received_quantity' => 0,

                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // ================= AUDIT =================
            auditLog(
                module: 'Purchase Order',
                action: 'Create',
                recordId: $po,
                new: [
                    'po_number' => $poNumber,
                    'total' => $grandTotal
                ],
                description: 'Purchase Order created successfully'
            );

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Purchase Order created successfully'
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryPurchaseOrder $PurchaseOrder)
    {
        $data = [
            'title' => 'View PurchaseOrder Details',

            'fields' => [
                [
                    'label' => 'PO Number',
                    'value' => $PurchaseOrder->po_number,
                ],
                [
                    'label' => 'PO Date',
                    'value' => formatDateTime($PurchaseOrder->po_date),
                ],
                [
                    'label' => 'Expected Delivery Date',
                    'value' => formatDateTime($PurchaseOrder->expected_delivery_date),
                ],
                [
                    'label' => 'Vendor',
                    'value' => $PurchaseOrder->vendor->name ?? '-',
                ],
                [
                    'label' => 'Sub Total',
                    'value' => $PurchaseOrder->sub_total,
                ],
                [
                    'label' => 'Tax Amount',
                    'value' => $PurchaseOrder->tax_amount,
                ],
                [
                    'label' => 'Discount',
                    'value' => $PurchaseOrder->discount_amount,
                ],
                [
                    'label' => 'Grand Total',
                    'value' => $PurchaseOrder->grand_total,
                ],
                [
                    'label' => 'Status',
                    'value' => $PurchaseOrder->status,
                ],
                [
                    'label' => 'Notes',
                    'value' => $PurchaseOrder->notes ?? '-',
                ],
                
                [
                    'label' => 'Created At',
                    'value' => formatDateTime($PurchaseOrder->created_at),
                ],
                [
                    'label' => 'Delete At',
                    'value' => formatDateTime($PurchaseOrder->deleted_at),
                ],
                
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryPurchaseOrder $inventoryPurchaseOrder)
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

            // ================= VALIDATION =================
            $validated = $request->validate([
                'vendor_id' => 'required|exists:inventory_vendors,id',
                'po_date' => 'required|date',
                'expected_delivery_date' => 'nullable|date|after_or_equal:po_date',

                'discount_amount' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string',

                'items' => 'required|array|min:1',

                'items.*.item_id' => 'required|exists:inventory_items,id',
                'items.*.unit_id' => 'required|exists:inventory_units,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.rate' => 'required|numeric|min:0',
                'items.*.tax_percentage' => 'nullable|numeric|min:0',
            ]);

            DB::beginTransaction();

            // ================= GET PO =================
            $po = DB::table('inventory_purchase_orders')
                ->where('id', $id)
                ->where('hotel_id', $user->hotel_id)
                ->first();

            if (!$po) {
                return response()->json([
                    'status' => false,
                    'message' => 'Purchase Order not found'
                ], 404);
            }

            // ❌ Prevent editing if not draft
            if ($po->status !== 'draft') {
                return response()->json([
                    'status' => false,
                    'message' => 'Only draft Purchase Orders can be updated'
                ], 400);
            }

            // ================= CALCULATE TOTALS =================
            $subTotal = 0;
            $taxTotal = 0;

            foreach ($validated['items'] as $item) {

                $qty = $item['quantity'];
                $rate = $item['rate'];
                $taxPercent = $item['tax_percentage'] ?? 0;

                $lineSubTotal = $qty * $rate;
                $taxAmount = ($lineSubTotal * $taxPercent) / 100;

                $subTotal += $lineSubTotal;
                $taxTotal += $taxAmount;
            }

            $discount = $validated['discount_amount'] ?? 0;
            $grandTotal = ($subTotal + $taxTotal) - $discount;

            // ================= UPDATE PO =================
            DB::table('inventory_purchase_orders')
                ->where('id', $id)
                ->update([
                    'vendor_id' => $validated['vendor_id'],
                    'po_date' => $validated['po_date'],
                    'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,

                    'sub_total' => $subTotal,
                    'tax_amount' => $taxTotal,
                    'discount_amount' => $discount,
                    'grand_total' => $grandTotal,

                    'notes' => $validated['notes'] ?? null,

                    'updated_at' => now(),
                ]);

            // ================= DELETE OLD ITEMS =================
            DB::table('inventory_purchase_order_items')
                ->where('purchase_order_id', $id)
                ->delete();

            // ================= INSERT NEW ITEMS =================
            foreach ($validated['items'] as $item) {

                $qty = $item['quantity'];
                $rate = $item['rate'];
                $taxPercent = $item['tax_percentage'] ?? 0;

                $lineSubTotal = $qty * $rate;
                $taxAmount = ($lineSubTotal * $taxPercent) / 100;

                $total = $lineSubTotal + $taxAmount;

                DB::table('inventory_purchase_order_items')->insert([
                    'purchase_order_id' => $id,
                    'item_id' => $item['item_id'],
                    'unit_id' => $item['unit_id'],

                    'quantity' => $qty,
                    'rate' => $rate,

                    'tax_percentage' => $taxPercent,
                    'tax_amount' => $taxAmount,

                    'discount_amount' => 0,
                    'total' => $total,

                    'received_quantity' => 0, // ⚠️ keep or adjust if GRN exists

                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // ================= AUDIT =================
            auditLog(
                module: 'Purchase Order',
                action: 'Update',
                recordId: $id,
                new: [
                    'po_number' => $po->po_number,
                    'total' => $grandTotal
                ],
                description: 'Purchase Order updated successfully'
            );

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Purchase Order updated successfully'
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryPurchaseOrder $inventoryPurchaseOrder)
    {
        //
    }

    public function changeStatus(Request $request, $id)
    {
        $user = Auth::user();

        try {

            $request->validate([
                'status' => 'required|in:approved,sent,cancelled,completed'
            ]);

            $po = InventoryPurchaseOrder::with(['items.item', 'items.unit', 'vendor', 'hotel'])
                ->where('hotel_id', $user->hotel_id)
                ->findOrFail($id);

            $currentStatus = $po->status;
            $newStatus = $request->status;

            $allowedTransitions = [
                'draft' => ['approved', 'cancelled'],
                'approved' => ['sent', 'cancelled'],
                'sent' => ['completed'],
            ];

            if (
                !isset($allowedTransitions[$currentStatus]) ||
                !in_array($newStatus, $allowedTransitions[$currentStatus])
            ) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid status transition'
                ], 400);
            }

            DB::beginTransaction();

            $oldStatus = $po->status;

            $po->update([
                'status' => $newStatus
            ]);

            // ================= SEND EMAIL WHEN SENT =================
            if ($newStatus === 'sent') {

                // 🔥 Generate PDF (RAW)
                $pdf = generatePdfRaw(
                    'pdf.inventoryManagement.purchase-order',
                    compact('po')
                );

                // 🔥 Vendor Email
                $vendorEmail = $po->vendor->email ?? null;

                if (!$vendorEmail) {
                    throw new \Exception('Vendor email not found');
                }

                // 🔥 Hotel Admin Emails (BCC)
                $bccEmails = \App\Models\User::where('hotel_id', $po->hotel_id)
                    ->where('user_role_id', 3)
                    ->pluck('email')
                    ->toArray();

                // 🔥 Mail content
                $subject = "Purchase Order - {$po->po_number}";
                $message = view('emails.inventoryManagement.purchase-order', compact('po'))->render();

                Mail::to($vendorEmail)
                    ->bcc($bccEmails)
                    ->send(new GlobalMail(
                        $subject,
                        $message,
                        [
                            [
                                'data' => $pdf,
                                'name' => $po->po_number . '.pdf',
                                'mime' => 'application/pdf'
                            ]
                        ]
                    ));
            }

            // ================= AUDIT =================
            auditLog(
                module: 'Inventory Purchase Order',
                action: 'Status Change',
                recordId: $po->id,
                old: ['status' => $oldStatus],
                new: ['status' => $newStatus],
                description: 'PO status updated'
            );

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => $newStatus === 'sent'
                    ? 'PO sent to vendor successfully'
                    : 'Status updated successfully'
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            \Log::error('PO Status Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function download($id)
    {
        $po = InventoryPurchaseOrder::with(['items.item', 'items.unit', 'vendor', 'hotel'])
            ->findOrFail($id);

        return downloadPdf(
            'pdf.inventoryManagement.purchase-order',
            compact('po'),
            'PO-' . $po->po_number . '.pdf'
        );
    }

    public function getItems($id)
    {
        $po = InventoryPurchaseOrder::with(['items.item', 'items.unit'])->whereIn('status', ['approved', 'sent', 'partially_received'])
            ->findOrFail($id);

        $data = $po->items->map(function ($item) {
            $received = $item->received_quantity ?? 0;
            $remaining = $item->quantity - $received;
            return [
                'purchase_order_item_id' => $item->id,
                'item_id' => $item->item_id,
                'name' => $item->item->name,
                'unit_id' => $item->unit_id,
                'unit_name' => $item->unit->short_name,
                'quantity' => $item->quantity,
                'received_quantity' => $item->received_quantity,
                'rate' => $item->rate,
                'remaining_qty' => $remaining,
                'tax_percentage' => $item->tax_percentage,
            ];
        });

        return response()->json([
            'vendor_id' => $po->vendor_id,
            'items' => $data,
            'discount' => $po->discount_amount
        ]);
    }
}
