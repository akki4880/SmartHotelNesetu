@extends('layouts.master')

@section('title', 'Stock Ledger')
@section('mainbreadcrumb', 'Inventory Management')
@section('subbreadcrumb', 'Stock Ledger')
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

                        <!-- Date From -->
                        <div class="col-3">
                            <label>From Date</label>
                            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                        </div>

                        <!-- Date To -->
                        <div class="col-3">
                            <label>To Date</label>
                            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                        </div>

                        <!-- Item Name -->
                        <div class="col-3">
                            <label>Item Name</label>
                            <input type="text" name="item_name" class="form-control" value="{{ request('item_name') }}"
                                placeholder="Item Name">
                        </div>

                        <!-- Type -->
                        <div class="col-3">
                            <label>Type</label>
                            <input type="text" name="type" class="form-control" value="{{ request('type') }}"
                                placeholder="Type">
                        </div>

                        <!-- Reference Number -->
                        <div class="col-3 mt-2">
                            <label>Reference No</label>
                            <input type="text" name="reference_number" class="form-control"
                                value="{{ request('reference_number') }}" placeholder="Reference">
                        </div>

                        <!-- Flow -->
                        <div class="col-3 mt-2">
                            <label>Flow</label>
                            <select name="flow" class="form-control">
                                <option value="">Select Flow</option>
                                <option value="in" {{ request('flow') == 'in' ? 'selected' : '' }}>IN</option>
                                <option value="out" {{ request('flow') == 'out' ? 'selected' : '' }}>OUT</option>
                            </select>
                        </div>

                        <!-- Quantity -->
                        <div class="col-3 mt-2">
                            <label>Quantity</label>
                            <input type="text" name="quantity" class="form-control" value="{{ request('quantity') }}"
                                placeholder="Quantity">
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
<div class="row">
    <div class="col-sm-12">
        <div class="card mb-g">
            <div class="card-header  d-flex justify-content-between align-items-center">
                <h3 class="mb-0 fw-bolder">Stock Ledger</h3>
                <div class="float-right">
                    <button class="btn btn-info btn-sm" onclick="toggleFilter()">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="frame-wrap p-0 border-0 m-0" id="table-container">
                    @if(!$ledgers->isEmpty())

                    <table class="table table-bordered table-sm" id="table-example">

                        <thead class="bg-primary text-white">
                            <tr> 
                                <th>Sr</th>
                                <th>Date</th>
                                <th>Item</th>
                                <th>Type</th>
                                <th>Reference</th>
                                <th>Flow</th>
                                <th>Qty</th>
                                <th>Balance After</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($ledgers as $ledger)

                            <tr> 
                                <td>{{ $ledgers->firstItem() + $loop->index }}</td>

                                <td>{{ formatDateTime($ledger->created_at) }}</td>

                                <td><strong>{{ $ledger->item->name ?? '-' }}</strong></td>

                                <td>{{ $ledger->type }}</td>

                                <td>{{ $ledger->reference_number ?? '-' }}</td>

                                <td>
                                    @if($ledger->flow == 'in')
                                    <span class="badge badge-success">IN</span>
                                    @else
                                    <span class="badge badge-danger">OUT</span>
                                    @endif
                                </td>

                                <td>{{ $ledger->quantity }}</td>

                                <td><strong>{{ $ledger->balance_after }}</strong></td>

                            </tr>

                            @endforeach

                        </tbody>

                    </table>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $ledgers->appends(request()->query())->links() }}
                    </div>

                    @else
                    <div>
                        No ledger records found
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
 
@endsection