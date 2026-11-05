@extends('layouts.master')

@section('title', 'Stock Transactions')
@section('mainbreadcrumb', 'Inventory Management')
@section('subbreadcrumb', 'Stock Transactions')
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
                            <input type="text" name="name" class="form-control"
                                value="{{ request('name') }}" placeholder="Search Item">
                        </div>

                        <!-- Category -->
                        <div class="col-3">
                            <label>Category</label>
                            <select name="category_id" class="form-control select2">
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Unit -->
                        <div class="col-3">
                            <label>Unit</label>
                            <select name="unit_id" class="form-control select2">
                                <option value="">Select Unit</option>
                                @foreach($units as $unit)
                                <option value="{{ $unit->id }}"
                                    {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->short_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Stock Status -->
                        <div class="col-3">
                            <label>Status</label>
                            <select name="stock_status" class="form-control">
                                <option value="">Select Status</option>
                                <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Low Stock</option>
                                <option value="ok" {{ request('stock_status') == 'ok' ? 'selected' : '' }}>In Stock</option>
                            </select>
                        </div>

                        <!-- Min Stock Range -->
                        <div class="col-3 mt-2">
                            <label>Min Stock ≤</label>
                            <input type="number" name="min_stock" class="form-control"
                                value="{{ request('min_stock') }}" placeholder="e.g. 10">
                        </div>

                        <!-- Current Stock Range -->
                        <div class="col-3 mt-2">
                            <label>Current Stock ≥</label>
                            <input type="number" name="current_stock" class="form-control"
                                value="{{ request('current_stock') }}" placeholder="e.g. 50">
                        </div>

                        <!-- Buttons -->
                        <div class="col-3 mt-4">
                            <div class="d-flex">
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
<div class="row">
    <div class="col-sm-12">
        <div class="card mb-g">
            <div class="card-header  d-flex justify-content-between align-items-center">
                <h3 class="mb-0 fw-bolder">Stock Transactions</h3>
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
                                <th>Action</th>
                                <th>Sr</th>
                                <th>Item</th>
                                <th>Category</th>
                                <th>Current Stock</th>
                                <th>Min Stock</th>
                                <th>Unit</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($items as $item)

                            <tr>

                                {{-- ACTION --}}
                                <td>

                                    <a
                                        class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                        href="{{ route('stock-transactions.show', $item->id) }}" title="View Transactions">
                                        <i class="fa fa-eye"></i>
                                    </a>

                                </td>

                                {{-- SR --}}
                                <td>{{ $items->firstItem() + $loop->index }}</td>

                                {{-- ITEM --}}
                                <td><strong>{{ $item->name }}</strong></td>

                                {{-- CATEGORY --}}
                                <td>{{ $item->category->name ?? '-' }}</td>

                                {{-- CURRENT STOCK --}}
                                <td>
                                    <strong>{{ $item->current_stock }}</strong>
                                </td>

                                {{-- MIN STOCK (Highlight Low Stock) --}}
                                <td
                                    @if($item->current_stock <= $item->minimum_stock)
                                        style="background-color:#f8d7da;"
                                        @endif
                                        >
                                        {{ $item->minimum_stock }}
                                </td>

                                {{-- UNIT --}}
                                <td>{{ $item->unit->short_name ?? '-' }}</td>

                                <td>
                                    @if($item->current_stock <= $item->minimum_stock)
                                        <span class="badge badge-danger">LOW STOCK</span>
                                        @else
                                        <span class="badge badge-success">IN STOCK</span>
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
                    <div class="text-center p-4">
                        No Items Found
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


@endsection