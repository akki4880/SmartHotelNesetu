<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use App\Models\InventoryUnit;
use App\Models\InventoryStockLedger;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class InventoryStockController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:stock_transactions,view', only: ['index', 'stockTransactions', 'show']),
        ];
    }

    public function index(Request $request)
    {
        try {

            $hotelId = auth()->user()->hotel_id;

            // ✅ Start Query
            $query = InventoryItem::with(['category', 'unit'])
                ->where('hotel_id', $hotelId);

            // =========================
            // ✅ APPLY FILTERS
            // =========================

            if ($request->filled('item_name')) {
                $query->where('name', 'like', '%' . $request->item_name . '%');
            }

            if ($request->filled('category_name')) {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->category_name . '%');
                });
            }

            if ($request->filled('unit_name')) {
                $query->whereHas('unit', function ($q) use ($request) {
                    $q->where('short_name', 'like', '%' . $request->unit_name . '%');
                });
            }

            if ($request->filled('purchase_price')) {
                $query->where('purchase_price', $request->purchase_price);
            }

            // =========================
            // ✅ PAGINATION
            // =========================

            $items = $query->orderBy('name', 'asc')
                ->paginate(perPage())
                ->withQueryString();

            // =========================
            // ✅ STOCK CALCULATION
            // =========================

            $totalStockValue = 0;
            $lowStockCount = 0;
            $outOfStockCount = 0;

            foreach ($items as $item) {

                $stockIn = InventoryStockLedger::where('hotel_id', $hotelId)
                    ->where('item_id', $item->id)
                    ->where('flow', 'in')
                    ->sum('quantity');

                $stockOut = InventoryStockLedger::where('hotel_id', $hotelId)
                    ->where('item_id', $item->id)
                    ->where('flow', 'out')
                    ->sum('quantity');

                $currentStock = $stockIn - $stockOut;

                $item->current_stock = $currentStock;

                $stockValue = $currentStock * ($item->purchase_price ?? 0);
                $item->stock_value = $stockValue;

                $totalStockValue += $stockValue;

                // LOW STOCK
                if ($currentStock <= $item->minimum_stock && $currentStock > 0) {
                    $item->stock_status = 'LOW';
                    $lowStockCount++;
                }
                // OUT OF STOCK
                elseif ($currentStock <= 0) {
                    $item->stock_status = 'OUT';
                    $outOfStockCount++;
                }
                // OK
                else {
                    $item->stock_status = 'OK';
                }
            }

            // =========================
            // ⚠️ FILTER ON CALCULATED FIELDS
            // =========================

            $filteredItems = collect($items->items());

            if ($request->filled('stock_status')) {
                $filteredItems = $filteredItems->where('stock_status', $request->stock_status);
            }

            if ($request->filled('current_stock')) {
                $filteredItems = $filteredItems->where('current_stock', $request->current_stock);
            }

            // =========================
            // ✅ FIX PAGINATION AFTER FILTER
            // =========================

            if ($request->filled('stock_status') || $request->filled('current_stock')) {

                $items = new LengthAwarePaginator(
                    $filteredItems->values(),
                    $filteredItems->count(),
                    perPage(),
                    $request->page ?? 1,
                    [
                        'path' => $request->url(),
                        'query' => $request->query()
                    ]
                );
            }

            // =========================
            // ✅ RETURN VIEW
            // =========================

            return view('pages.inventoryManagement.stock-overview.index', compact(
                'items',
                'totalStockValue',
                'lowStockCount',
                'outOfStockCount'
            ));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function stockTransactions(Request $request)
    {

        $hotelId = auth()->user()->hotel_id;

        $userId = auth()->user()->id;


        $query = InventoryItem::with(['category', 'unit'])
            ->where('hotel_id', $hotelId);

        // 🔍 Item Name
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // 🔍 Category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 🔍 Unit
        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        // 🔥 Stock Status (LOW / IN STOCK)
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->whereColumn('current_stock', '<=', 'minimum_stock');
            } elseif ($request->stock_status === 'ok') {
                $query->whereColumn('current_stock', '>', 'minimum_stock');
            }
        }

        // 🔍 Minimum Stock Filter
        if ($request->filled('min_stock')) {
            $query->where('minimum_stock', '<=', $request->min_stock);
        }

        // 🔍 Current Stock Filter
        if ($request->filled('current_stock')) {
            $query->where('current_stock', '>=', $request->current_stock);
        }

        // 📦 Pagination
        $items = $query->orderBy('name')
            ->paginate(perPage())
            ->withQueryString();

        $user = auth()->user();

        $categories = InventoryCategory::where('hotel_id', $user->hotel_id)
            ->where('status', 1)
            ->get();
        $units = InventoryUnit::where('status', 1)
            ->get();

        return view(
            'pages.inventoryManagement.stock-transactions.index',
            compact('items', 'categories', 'units')
        );
    }

    public function show(Request $request, $id)
    {
        try {

            $hotelId = auth()->user()->hotel_id;

            $item = InventoryItem::findOrFail($id);

            $query = InventoryStockLedger::where('hotel_id', $hotelId)
                ->where('item_id', $id);

            // 🔍 FILTERS
            if ($request->filled('from_date') && $request->filled('to_date')) {
                $query->whereBetween('created_at', [
                    $request->from_date . ' 00:00:00',
                    $request->to_date . ' 23:59:59'
                ]);
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('flow')) {
                $query->where('flow', $request->flow);
            }

            $transactions = $query->orderByDesc('id')
                ->paginate(perPage())
                ->withQueryString();

            return view('pages.inventoryManagement.stock-transactions.view', compact('item', 'transactions'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
