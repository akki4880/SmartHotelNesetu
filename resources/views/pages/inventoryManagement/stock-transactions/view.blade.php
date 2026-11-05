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
                <form method="GET" class="mb-3">
                    <div class="row">

                        <div class="col-md-3">
                            <label>From Date</label>
                            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                        </div>

                        <div class="col-md-3">
                            <label>To Date</label>
                            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                        </div>

                        <div class="col-md-3">
                            <label>Reference No</label>
                            <select name="flow" class="form-control">
                                <option value="">Flow</option>
                                <option value="in" {{ request('flow')=='in'?'selected':'' }}>IN</option>
                                <option value="out" {{ request('flow')=='out'?'selected':'' }}>OUT</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>Type</label>
                            <select name="type" class="form-control">
                                <option value="">Type</option>
                                <option value="GRN" {{ request('type')=='GRN'?'selected':'' }}>GRN</option>
                                <option value="CONSUMPTION" {{ request('type')=='CONSUMPTION'?'selected':'' }}>CONSUMPTION</option>
                                <option value="ADJUSTMENT" {{ request('type')=='ADJUSTMENT'?'selected':'' }}>ADJUSTMENT</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <div class="d-flex pt-2">
                                <a href="{{ url()->current() }}" class="btn btn-secondary">Reset</a>
                                <button type="submit" class="btn btn-info  mx-2">Apply</button>
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
                <h3 class="mb-0 fw-bolder">Stock Transactions of {{ $item->name }}</h3>
                <div class="float-right">
                    <a class="btn btn-secondary btn-sm" href="{{ url('inventory-management/stock-transactions') }}">
                        <i class="fa-solid fa-arrow-left"></i> Go Back
                    </a>
                    <button class="btn btn-info btn-sm" onclick="toggleFilter()">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="frame-wrap p-0 border-0 m-0" id="table-container">
                    @if(!$transactions->isEmpty())
                    <div class="mb-3">
                        <strong>Current Stock:</strong> {{ $item->current_stock }} {{ $item->unit->short_name }}
                    </div>
                    <table class="table table-bordered table-sm">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Sr</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Reference</th>
                                <th>Flow</th>
                                <th>Qty</th>
                                <th>Balance</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($transactions as $tx)
                            <tr>

                                <td>{{ $transactions->firstItem() + $loop->index }}</td>

                                <td>{{ formatDateTime($tx->created_at) }}</td>

                                <td>
                                    <span class="badge badge-info">
                                        {{ $tx->type }}
                                    </span>
                                </td>

                                <td>{{ $tx->reference_number ?? '-' }}</td>

                                <td>
                                    @if($tx->flow == 'in')
                                    <span class="badge badge-success">IN</span>
                                    @else
                                    <span class="badge badge-danger">OUT</span>
                                    @endif
                                </td>

                                <td>{{ $tx->quantity }}</td>

                                <td>
                                    <strong>{{ $tx->balance_after }}</strong>
                                </td>

                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No transactions found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-end">
                        {{ $transactions->links() }}
                    </div>

                    @else
                    <div class="text-center p-4">
                        No Transactions Found
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.common-view')

@endsection