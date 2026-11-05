<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryStockLedger;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class InventoryStockLedgerController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:stock_ledger,view', only: ['index', 'show']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $hotelId = auth()->user()->hotel_id;

            $query = InventoryStockLedger::with(['item'])
                ->where('hotel_id', $hotelId);

            // =========================
            // 🔍 FILTER: Date Range
            // =========================
            if ($request->filled('from_date') && $request->filled('to_date')) {
                $query->whereBetween('created_at', [
                    $request->from_date . " 00:00:00",
                    $request->to_date . " 23:59:59"
                ]);
            }

            // =========================
            // 🔍 FILTER: Item Name (relation)
            // =========================
            if ($request->filled('item_name')) {
                $query->whereHas('item', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->item_name . '%');
                });
            }

            // =========================
            // 🔍 FILTER: Type
            // =========================
            if ($request->filled('type')) {
                $query->where('type', 'like', '%' . $request->type . '%');
            }

            // =========================
            // 🔍 FILTER: Reference Number
            // =========================
            if ($request->filled('reference_number')) {
                $query->where('reference_number', 'like', '%' . $request->reference_number . '%');
            }

            // =========================
            // 🔍 FILTER: Flow (IN / OUT)
            // =========================
            if ($request->filled('flow')) {
                $query->where('flow', $request->flow);
            }

            // =========================
            // 🔍 FILTER: Quantity
            // =========================
            if ($request->filled('quantity')) {
                $query->where('quantity', $request->quantity);
            }

            // =========================
            // 📦 PAGINATION
            // =========================
            $ledgers = $query->orderByDesc('id')
                ->paginate(perPage())
                ->withQueryString();

            return view('pages.inventoryManagement.stock_ledger.index', compact('ledgers'));

        } catch (\Exception $e) {

            return back()->with('error', $e->getMessage());
        }
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryStockLedger $StockLedger)
    {
 
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryStockLedger $inventoryStockLedger)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InventoryStockLedger $inventoryStockLedger)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryStockLedger $inventoryStockLedger)
    {
        //
    }
}
