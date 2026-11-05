@extends('layouts.master')

@section('title', 'Stock Overview')
@section('mainbreadcrumb', 'Inventory Management')
@section('subbreadcrumb', 'Stock Overview')
@section('content')
@php
// Get all request parameters except _token or page
$filters = request()->except(['_token', 'page']);

// Check if any filter has a non-empty value
$hasFilter = collect($filters)->filter(fn($value) => !empty($value))->isNotEmpty();
@endphp
<div class="row" style="{{ $hasFilter ? '' : 'display:none;' }}" id="filterRow">
    <div class="col-sm-12">
        <div class="card mb-g">
            <div class="card-header bg-info d-flex justify-content-between align-items-center">
                <h3 class="mb-0 fw-bolder text-white"> <i class="fa-solid fa-filter"></i> Filter</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ url()->current() }}">
                    <div class="row">

                        <!-- Item Name -->
                        <div class="col-3">
                            <label>Item Name</label>
                            <input type="text" name="item_name" class="form-control" value="{{ request('item_name') }}"
                                placeholder="Item Name">
                        </div>

                        <!-- Category -->
                        <div class="col-3">
                            <label>Category</label>
                            <input type="text" name="category_name" class="form-control"
                                value="{{ request('category_name') }}" placeholder="Category">
                        </div>

                        <!-- Unit -->
                        <div class="col-3">
                            <label>Unit</label>
                            <input type="text" name="unit_name" class="form-control" value="{{ request('unit_name') }}"
                                placeholder="Unit">
                        </div>

                        <!-- Stock Status -->
                        <div class="col-3">
                            <label>Stock Status</label>
                            <select name="stock_status" class="form-control">
                                <option value="">Select Status</option>
                                <option value="LOW" {{ request('stock_status') == 'LOW' ? 'selected' : '' }}>Low Stock
                                </option>
                                <option value="OK" {{ request('stock_status') == 'OK' ? 'selected' : '' }}>OK</option>
                            </select>
                        </div>

                        <!-- Min Stock -->
                        <div class="col-3 mt-2">
                            <label>Minimum Stock</label>
                            <input type="text" name="minimum_stock" class="form-control"
                                value="{{ request('minimum_stock') }}" placeholder="Min Stock">
                        </div>

                        <!-- Current Stock -->
                        <div class="col-3 mt-2">
                            <label>Current Stock</label>
                            <input type="text" name="current_stock" class="form-control"
                                value="{{ request('current_stock') }}" placeholder="Current Stock">
                        </div>

                        <!-- Purchase Price -->
                        <div class="col-3 mt-2">
                            <label>Purchase Price</label>
                            <input type="text" name="purchase_price" class="form-control"
                                value="{{ request('purchase_price') }}" placeholder="Price">
                        </div>
                        <!-- Buttons -->
                        <div class="col-3 mt-4">
                            <div class="d-flex pt-2">
                                <a href="{{ url()->current() }}" class="btn btn-secondary mx-2">Reset</a>
                                <button type="submit" class="btn btn-info">Apply</button>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- <div class="row mb-4">

    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6>Total Items</h6>
                <h3>{{ $items->total() }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6>Current Stock Value</h6>
                <h3>₹ {{ number_format($totalStockValue, 2) }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6>Low Stock Items</h6>
                <h3>{{ $lowStockCount }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h6>Out of Stock</h6>
                <h3>{{ $outOfStockCount }}</h3>
            </div>
        </div>
    </div>

</div> -->
<div class="row">
    <div class="col-sm-12">
        <div class="card mb-g">
            <div class="card-header  d-flex justify-content-between align-items-center">
                <h3 class="mb-0 fw-bolder">Stock Overview</h3>
                <div class="float-right">
                    <button class="btn btn-info btn-sm" onclick="toggleFilter()">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="frame-wrap p-0 border-0 m-0" id="table-container">
                    @if(!$items->isEmpty())

                    <table class="table table-bordered table-sm" id="table-example">

                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Sr</th>
                                <th>Item</th>
                                <th>Category</th>
                                <th>Unit</th>

                                <th>Current Stock</th>
                                <th>Min Stock</th>

                                <!-- <th>Purchase Price Per Unit</th> -->
                                <!-- <th>Current Stock Value</th> -->

                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($items as $item)

                            <tr>

                                {{-- SR --}}
                                <td>{{ $items->firstItem() + $loop->index }}</td>

                                {{-- ITEM --}}
                                <td>
                                    <strong>{{ $item->name }}</strong>
                                </td>

                                {{-- CATEGORY --}}
                                <td>{{ $item->category->name ?? '-' }}</td>

                                {{-- UNIT --}}
                                <td>{{ $item->unit->short_name ?? '-' }}</td>

                                {{-- CURRENT STOCK --}}
                                <td>
                                    <strong>{{ $item->current_stock }}</strong>
                                </td>

                                {{-- MIN STOCK --}}
                                <td>{{ $item->minimum_stock }}</td>

                                {{-- PURCHASE PRICE
                                <td>{{ number_format($item->purchase_price, 2) }}</td> --}}

                                {{-- STOCK VALUE 
                                <td>
                                    {{ number_format($item->stock_value, 2) }} --}}
                                </td>

                                {{-- STATUS --}}
                                <td>
                                    @if($item->stock_status == 'LOW')
                                    <span class="badge badge-danger">LOW STOCK</span>
                                    @else
                                    <span class="badge badge-success">OK</span>
                                    @endif
                                </td>

                            </tr>

                            @endforeach

                        </tbody>

                    </table>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $items->links() }}
                    </div>

                    @else
                    <div>
                        No stock data found
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


@endsection