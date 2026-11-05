@extends('layouts.master')

@section('title', 'Purchase Order')
@section('mainbreadcrumb', 'Purchase Order')

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

                        <!-- PO Number -->
                        <div class="col-3">
                            <label>PO Number</label>
                            <input type="text" name="po_number" class="form-control"
                                value="{{ request('po_number') }}" placeholder="PO Number">
                        </div>

                        <!-- Vendor Name -->
                        <div class="col-3">
                            <label>Vendor Name</label>
                            <input type="text" name="vendor_name" class="form-control"
                                value="{{ request('vendor_name') }}" placeholder="Vendor Name">
                        </div>

                        <!-- PO Date -->
                        <div class="col-3">
                            <label>PO Date</label>
                            <input type="date" name="po_date" class="form-control"
                                value="{{ request('po_date') }}">
                        </div>

                        <!-- Expected Delivery Date -->
                        <div class="col-3">
                            <label>Delivery Date</label>
                            <input type="date" name="expected_delivery_date" class="form-control"
                                value="{{ request('expected_delivery_date') }}">
                        </div>

                        <!-- Amount -->
                        <div class="col-3 mt-2">
                            <label>Amount</label>
                            <input type="text" name="amount" class="form-control"
                                value="{{ request('amount') }}" placeholder="Amount">
                        </div>

                        <!-- Total Items -->
                        <div class="col-3 mt-2">
                            <label>Total Items</label>
                            <input type="text" name="items_count" class="form-control"
                                value="{{ request('items_count') }}" placeholder="Items Count">
                        </div>

                        <!-- Created By -->
                        <div class="col-3 mt-2">
                            <label>Created By</label>
                            <input type="text" name="created_by" class="form-control"
                                value="{{ request('created_by') }}" placeholder="User Name">
                        </div>

                        <!-- Status -->
                        <div class="col-3 mt-2">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">Select Status</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                                <option value="partially_received" {{ request('status') == 'partially_received' ? 'selected' : '' }}>Partial</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
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
                <h3 class="mb-0 fw-bolder">Purchase Order</h3>
                <div class="float-right">
                    @if(auth()->user()->hasPermission('purchase_orders', 'create'))
                    <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#createModal"><i
                            class="fa-solid fa-plus"></i>
                        Create</button>
                    @endif
                    <button class="btn btn-info btn-sm" onclick="toggleFilter()">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>

                </div>
            </div>

            <div class="card-body">
                <div class="frame-wrap p-0 border-0 m-0" id="table-container">
                    @if(!$purchaseOrders->isEmpty())
                    <table class="table table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                @if(auth()->user()->hasPermission('purchase_orders', 'view') || auth()->user()->hasPermission('purchase_orders', 'edit') || auth()->user()->hasPermission('purchase_orders', 'approve') || auth()->user()->hasPermission('purchase_orders', 'sendmail') || auth()->user()->hasPermission('purchase_orders', 'cancel') || auth()->user()->hasPermission('purchase_orders', 'download'))
                                <th>Action</th>@endif
                                <th>Sr</th>
                                <th>PO Number</th>
                                <th>Vendor</th>
                                <th>PO Date</th>
                                <th>Delivery Date</th>
                                <th>Amount</th>
                                <th>Items</th>
                                <th>Created By</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($purchaseOrders as $po)
                            <tr>

                                {{-- ACTION --}}
                                @if(auth()->user()->hasPermission('purchase_orders', 'view') || auth()->user()->hasPermission('purchase_orders', 'edit') || auth()->user()->hasPermission('purchase_orders', 'approve') || auth()->user()->hasPermission('purchase_orders', 'sendmail') || auth()->user()->hasPermission('purchase_orders', 'cancel') || auth()->user()->hasPermission('purchase_orders', 'download'))
                                <td>
                                    <div class="d-flex">
                                        @if(auth()->user()->hasPermission('purchase_orders', 'view'))
                                        <button
                                            class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                            data-url="{{ route('purchase-orders.show', $po->id) }}" title="View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @endif
                                        <!-- <button data-toggle="tooltip"
                                                class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                                data-url="{{ route('purchase-orders.show', $po->id) }}" title="View">
                                                <i class="fa fa-eye"></i>
                                        </button> -->
                                        {{-- EDIT --}}
                                        @if(auth()->user()->hasPermission('purchase_orders', 'edit'))
                                        <div class="ml-1"><button class="btn one-btn-outline-success btn-icon btn-sm editPOBtn btn-loader"
                                                title="{{ $po->status == 'draft' ? 'Edit PO' : 'Only Draft can be edited' }}"
                                                data-id="{{ $po->id }}"
                                                data-vendor_id="{{ $po->vendor_id }}"
                                                data-po_date="{{ $po->po_date }}"
                                                data-expected_delivery_date="{{ $po->expected_delivery_date }}"
                                                data-discount="{{ $po->discount_amount }}"
                                                data-notes="{{ $po->notes }}"
                                                data-items='@json($po->items)' {{ $po->status != 'draft' ? 'disabled' : '' }}>
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button></div>
                                        @endif
                                        @if(auth()->user()->hasPermission('purchase_orders', 'approve'))
                                        {{-- APPROVE --}}
                                        <div class="ml-1"><button class="btn btn-outline-primary btn-sm btn-icon changeStatusBtn"
                                                data-id="{{ $po->id }}"
                                                data-status="approved"
                                                {{ $po->status != 'draft' ? 'disabled' : '' }}
                                                title="Approve PO">
                                                <i class="fa fa-check"></i>
                                            </button></div>
                                        @endif

                                        @if(auth()->user()->hasPermission('purchase_orders', 'sendmail'))
                                        {{-- SEND --}}
                                        <div class="ml-1"><button class="btn btn-outline-info btn-sm btn-icon changeStatusBtn"
                                                data-id="{{ $po->id }}"
                                                data-status="sent"
                                                {{ $po->status != 'approved' ? 'disabled' : '' }}
                                                title="Send PO">
                                                <i class="fa fa-paper-plane"></i>
                                            </button></div>
                                        @endif

                                        @if(auth()->user()->hasPermission('purchase_orders', 'cancel'))
                                        {{-- CANCEL --}}
                                        <div class="ml-1"><button class="btn one-btn-outline-danger btn-sm btn-icon changeStatusBtn"
                                                data-id="{{ $po->id }}"
                                                data-status="cancelled"
                                                {{ !in_array($po->status, ['draft','approved']) ? 'disabled' : '' }}
                                                title="Cancel PO">
                                                <i class="fa fa-times"></i>
                                            </button></div>
                                        @endif

                                        @if(auth()->user()->hasPermission('purchase_orders', 'download'))
                                        {{-- DOWNLOAD --}}
                                        <a href="{{ route('purchase-orders.download', $po->id) }}"
                                            class="btn btn-outline-dark btn-sm btn-icon ml-1"
                                            title="Download PDF">
                                            <i class="fa fa-download"></i>
                                        </a>
                                        @endif

                                    </div>
                                </td>
                                @endif

                                {{-- SR --}}
                                <td>{{ $purchaseOrders->firstItem() + $loop->index }}</td>

                                {{-- PO NUMBER --}}
                                <td><strong>{{ $po->po_number }}</strong></td>

                                {{-- VENDOR --}}
                                <td>{{ $po->vendor->name ?? '-' }}</td>

                                {{-- DATES --}}
                                <td>{{ formatDate($po->po_date) }}</td>
                                <td>
                                    {{ formatDate($po->expected_delivery_date) }}
                                </td>

                                {{-- AMOUNT --}}
                                <td>
                                    ₹ {{ number_format($po->grand_total, 2) }}
                                </td>
                                <td>{{ $po->items->count() }}</td>
                                <td>{{ $po->user->name ?? '-' }}</td>
                                {{-- STATUS --}}
                                <td>
                                    @if($po->status == 'draft')
                                    <span class="badge badge-secondary">Draft</span>
                                    @elseif($po->status == 'approved')
                                    <span class="badge badge-primary">Approved</span>
                                    @elseif($po->status == 'sent')
                                    <span class="badge badge-info">Sent</span>
                                    @elseif($po->status == 'partially_received')
                                    <span class="badge badge-warning">Partial</span>
                                    @elseif($po->status == 'completed')
                                    <span class="badge badge-success">Completed</span>
                                    @else
                                    <span class="badge badge-danger">Cancelled</span>
                                    @endif
                                </td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $purchaseOrders->links() }}
                    </div>

                    @else
                    No Purchase Orders found
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Create MOdal -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h4 class="modal-title">
                    Create Inventory Vendor
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="CreateForm" action="{{ route('purchase-orders.store') }}" method="POST"
                class="ajax-form" data-reset="1" data-modal="#createModal" novalidate>

                @csrf

                <div class="modal-body">

                    <div class="row">

                        {{-- Vendor --}}
                        <div class="col-md-4">
                            <label>Vendor</label>
                            <select name="vendor_id" class="form-control select2" required>
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger error-text" data-error="vendor_id"></small>
                        </div>

                        {{-- PO Date --}}
                        <div class="col-md-4">
                            <label>PO Date</label>
                            <input type="date" name="po_date" class="form-control" required>
                            <small class="text-danger error-text" data-error="po_date"></small>
                        </div>

                        {{-- Expected Delivery --}}
                        <div class="col-md-4">
                            <label>Expected Delivery</label>
                            <input type="date" name="expected_delivery_date" class="form-control">
                            <small class="text-danger error-text" data-error="expected_delivery_date"></small>
                        </div>

                    </div>

                    {{-- ================= ITEMS ================= --}}
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="text-danger fw-bold">Items</h5>
                            <button type="button" class="btn btn-success btn-sm addItem">
                                + Add Item
                            </button>
                        </div>

                        <div class="itemsWrapper">

                            {{-- FIRST ITEM --}}
                            <div class="item-card">
                                <div class="row">

                                    {{-- Item --}}
                                    <div class="col-md-2">
                                        <label>Item</label>
                                        <select name="items[0][item_id]" class="form-control select2 itemSelect" required>
                                            <option value="">Select Item</option>
                                            @foreach($items as $item)
                                            <option value="{{ $item->id }}"
                                                data-price="{{ $item->purchase_price }}"
                                                data-tax="{{ $item->tax_percentage }}"
                                                data-unit-id="{{ $item->unit_id }}"
                                                data-unit-name="{{ $item->unit->short_name }}">
                                                {{ $item->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <small class="text-danger error-text" data-error="items.0.item_id"></small>
                                    </div>

                                    {{-- Qty --}}
                                    <div class="col-md-2">
                                        <label>Qty</label>
                                        <input type="number" step="0.01" name="items[0][quantity]" class="form-control qty" required>
                                        <small class="text-danger error-text" data-error="items.0.quantity"></small>
                                    </div>

                                    {{-- Unit --}}
                                    <div class="col-md-1">
                                        <label>Unit</label>
                                        <input type="text" class="form-control unit_text" readonly>
                                        <input type="hidden" name="items[0][unit_id]" class="unit_id">
                                        <small class="text-danger error-text" data-error="items.0.unit_id"></small>
                                    </div>

                                    {{-- Rate --}}
                                    <div class="col-md-2">
                                        <label>Rate</label>
                                        <input type="number" step="0.01" name="items[0][rate]" class="form-control rate" required>
                                        <small class="text-danger error-text" data-error="items.0.rate"></small>
                                    </div>

                                    {{-- Tax --}}
                                    <div class="col-md-2">
                                        <label>Tax %</label>
                                        <input type="number" step="0.01" name="items[0][tax_percentage]" class="form-control tax">
                                        <small class="text-danger error-text" data-error="items.0.tax_percentage"></small>
                                    </div>

                                    {{-- Total --}}
                                    <div class="col-md-2">
                                        <label>Total</label>
                                        <input type="text" class="form-control total" readonly>
                                    </div>

                                    <div class="col-md-1"></div>

                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- ================= FOOTER ================= --}}
                    <div class="row mt-4">

                        {{-- LEFT --}}
                        <div class="col-md-6">

                            <div class="form-group">
                                <label>Discount</label>
                                <input type="number" name="discount_amount" class="form-control discountAmount" value="0">
                                <small class="text-danger error-text" data-error="discount_amount"></small>
                            </div>

                            <div class="form-group mt-3">
                                <label>Notes</label>
                                <textarea name="notes" class="form-control" rows="4"></textarea>
                                <small class="text-danger error-text" data-error="notes"></small>
                            </div>

                        </div>

                        {{-- RIGHT --}}
                        <div class="col-md-6">
                            <div class="p-3">

                                <h6 class="fw-bold text-danger mb-3">Summary</h6>

                                <div class="d-flex justify-content-between mb-2">
                                    <span>Sub Total</span>
                                    <strong id="subTotalText">₹ 0.00</strong>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tax</span>
                                    <strong id="taxAmountText">₹ 0.00</strong>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span>Discount</span>
                                    <strong id="discountText">₹ 0.00</strong>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <span>Grand Total</span>
                                    <strong class="text-success" id="grandTotalText">₹ 0.00</strong>
                                </div>

                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal-footer border-top">
                    <button type="reset" class="btn btn-secondary">Clear</button>
                    <button type="submit" class="btn btn-danger">Save</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title"> Edit Inventory Purchase Order </h4> <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true"><i class="fal fa-times"></i></span> </button>
            </div>
            <form id="EditForm" method="POST" class="ajax-form" data-reset="0" data-modal="#editModal">
                @csrf @method('PUT')
                <div class="modal-body">

                    <div class="row">

                        {{-- Vendor --}}
                        <div class="col-md-4">
                            <label>Vendor</label>
                            <select name="vendor_id" id="edit_vendor_id" class="form-control select2" required>
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger error-text" data-error="vendor_id"></small>
                        </div>

                        {{-- PO Date --}}
                        <div class="col-md-4">
                            <label>PO Date</label>
                            <input type="date" name="po_date" id="edit_po_date" class="form-control" required>
                            <small class="text-danger error-text" data-error="po_date"></small>
                        </div>

                        {{-- Expected Delivery --}}
                        <div class="col-md-4">
                            <label>Expected Delivery</label>
                            <input type="date" name="expected_delivery_date" id="edit_expected_delivery_date" class="form-control">
                            <small class="text-danger error-text" data-error="expected_delivery_date"></small>
                        </div>

                    </div>

                    {{-- ITEMS --}}
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="text-danger fw-bold">Items</h5>
                            <button type="button" class="btn btn-success btn-sm addItem">+ Add Item</button>
                        </div>

                        <div class="itemsWrapper" id="itemsWrapper"></div>
                    </div>

                    {{-- FOOTER --}}
                    <div class="row mt-4">

                        <div class="col-md-6">

                            <div class="form-group">
                                <label>Discount</label>
                                <input type="number" name="discount_amount" id="edit_discount" class="form-control discountAmount">
                            </div>

                            <div class="form-group mt-3">
                                <label>Notes</label>
                                <textarea name="notes" id="edit_notes" class="form-control" rows="4"></textarea>
                            </div>

                        </div>

                        <div class="col-md-6">
                            <div class="p-3">

                                <h6 class="fw-bold text-danger mb-3">Summary</h6>

                                <div class="d-flex justify-content-between mb-2">
                                    <span>Sub Total</span>
                                    <strong id="subTotalText">₹ 0.00</strong>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tax</span>
                                    <strong id="taxAmountText">₹ 0.00</strong>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span>Discount</span>
                                    <strong id="discountText">₹ 0.00</strong>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <span>Grand Total</span>
                                    <strong class="text-success" id="grandTotalText">₹ 0.00</strong>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@include('layouts.confirmationModal')
@include('layouts.common-view')
<script>
    let itemOptions = $('select[name="items[0][item_id]"]').html();

    $(document).on('click', '.addItem', function() {

        let form = $(this).closest('form'); // detect form
        let wrapper = form.find('.itemsWrapper');

        let index = wrapper.find('.item-card').length;

        let html = `
    <div class="item-card mt-1">
        <div class="row">

            <div class="col-md-2">
                <label>Item</label>
                <select name="items[${index}][item_id]" 
                    class="form-control select2 itemSelect" required>
                    ${itemOptions}
                </select>
            </div>

            <div class="col-md-2">
                <label>Qty</label>
                <input type="number" step="0.01" 
                    name="items[${index}][quantity]" 
                    class="form-control qty" required>
            </div>

            <div class="col-md-1">
                <label>Unit</label>
                <input type="text" class="form-control unit_text" readonly>
                <input type="hidden" 
                    name="items[${index}][unit_id]" 
                    class="unit_id">
            </div>

            <div class="col-md-2">
                <label>Rate</label>
                <input type="number" step="0.01" 
                    name="items[${index}][rate]" 
                    class="form-control rate" required>
            </div>

            <div class="col-md-2">
                <label>Tax %</label>
                <input type="number" step="0.01" 
                    name="items[${index}][tax_percentage]" 
                    class="form-control tax">
            </div>

            <div class="col-md-2">
                <label>Total</label>
                <input type="text" class="form-control total" readonly>
            </div>

            <div class="col-md-1 d-flex align-items-start justify-content-center mt-4">
                <button type="button" class="btn btn-danger btn-sm removeItem">
                    <i class="fa fa-trash"></i>
                </button>
            </div>

        </div>
    </div>
    `;

        wrapper.append(html);

        // reinit select2 only inside this form
        wrapper.find('.select2').select2();
    });

    $(document).on('click', '.removeItem', function() {

        let form = $(this).closest('form');
        let wrapper = form.find('.item-card').parent(); // wrapper containing items

        let totalItems = wrapper.find('.item-card').length;

        // ❌ Prevent removing last item
        if (totalItems <= 1) {
            toastr.warning('At least one item is required.');
            return;
        }

        // ✅ Remove item
        $(this).closest('.item-card').remove();

        // ✅ Recalculate
        if (form.attr('id') === 'EditForm') {
            calculateTotal('#EditForm');
        } else {
            calculateTotal('#CreateForm');
        }

    });

    $(document).on('input', '.qty, .rate, .tax, .discountAmount', function() {

        let card = $(this).closest('.item-card');

        let qty = parseFloat(card.find('.qty').val()) || 0;
        let rate = parseFloat(card.find('.rate').val()) || 0;
        let tax = parseFloat(card.find('.tax').val()) || 0;

        let sub = qty * rate;
        let taxAmount = sub * tax / 100;
        let total = sub + taxAmount;

        card.find('.total').val(total.toFixed(2));

        // detect which form
        let form = $(this).closest('form');

        if (form.attr('id') === 'EditForm') {
            calculateTotal('#EditForm');
        } else {
            calculateTotal('#CreateForm');
        }

    });

    function calculateTotal(wrapper = '') {
        console.log("HELLOP");
        let subTotal = 0;
        let taxTotal = 0;

        $(wrapper + ' .item-card').each(function() {

            let qty = parseFloat($(this).find('.qty').val()) || 0;
            let rate = parseFloat($(this).find('.rate').val()) || 0;
            let tax = parseFloat($(this).find('.tax').val()) || 0;

            let st = qty * rate;
            let tx = st * tax / 100;

            subTotal += st;
            taxTotal += tx;

        });

        let discount = parseFloat($(wrapper + ' .discountAmount').val()) || 0;
        let grandTotal = subTotal + taxTotal - discount;

        // UI update
        $(wrapper + ' #subTotalText').text('₹ ' + subTotal.toFixed(2));
        $(wrapper + ' #taxAmountText').text('₹ ' + taxTotal.toFixed(2));
        $(wrapper + ' #discountText').text('₹ ' + discount.toFixed(2));
        $(wrapper + ' #grandTotalText').text('₹ ' + grandTotal.toFixed(2));
    }

    $(document).ready(function() {

        $(document).on('select2:select', '.itemSelect', function(e) {

            console.log('Select2 Triggered ✅');

            let selected = $(this).find(':selected');

            let rate = selected.data('price');
            let tax = selected.data('tax');
            let unitId = selected.data('unit-id');
            let unitName = selected.data('unit-name');

            let card = $(this).closest('.item-card');

            card.find('.rate').val(rate || 0);
            card.find('.tax').val(tax || 0);
            card.find('.unit_id').val(unitId);
            card.find('.unit_text').val(unitName);
            card.find('.rate').trigger('input');
        });

    });

    let selectedPO = {
        id: null,
        status: null
    };

    $(document).on('click', '.changeStatusBtn', function() {

        selectedPO.id = $(this).data('id');
        selectedPO.status = $(this).data('status');
        let header = $('.modal-header');

        header.removeClass('bg-danger bg-primary bg-info');

        if (selectedPO.status === 'approved') {
            header.removeClass('bg-primary bg-warning bg-danger bg-success one-bg-danger');
            header.addClass('bg-primary');
            $('#confirmationModalConfirmBtn').removeClass('btn-primary btn-warning btn-danger btn-success one-btn-danger').addClass('bg-primary text-white');
        } else if (selectedPO.status === 'sent') {
            header.removeClass('bg-primary bg-warning bg-danger bg-success one-bg-danger');
            header.addClass('bg-info');
            $('#confirmationModalConfirmBtn').removeClass('btn-primary btn-warning btn-danger btn-success one-btn-danger').addClass('bg-info text-white');
        } else if (selectedPO.status === 'cancelled') {
            header.removeClass('bg-primary bg-warning bg-danger bg-success one-bg-danger');
            header.addClass('bg-danger');
            $('#confirmationModalConfirmBtn').removeClass('btn-primary btn-warning btn-danger btn-success one-btn-danger').addClass('bg-danger text-white');
        }
        let actionText = getActionText(selectedPO.status);

        // 🎯 Dynamic message
        $('#confirmationModalBody').html(
            `<p>Are you sure you want to <strong>${actionText}</strong> this Purchase Order?</p>`
        );

        // 🎯 Change button text (optional)
        $('#confirmationModalConfirmBtn')
            .text('Yes, ' + actionText)
            .attr('data-original-text', 'Yes, ' + actionText);

        $('#confirmationModal').modal('show');
    });

    $('#confirmationModalConfirmBtn').click(function() {

        let btn = $(this);

        // 🔒 Prevent double click
        btn.prop('disabled', true).text('Processing...');

        $.ajax({
            url: '/inventory-management/purchase-orders/' + selectedPO.id + '/change-status',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                status: selectedPO.status
            },
            success: function(res) {

                if (res.status) {

                    toastr.success(res.message);

                    $('#confirmationModal').modal('hide');

                    // ✅ Reload (simple)
                    location.reload();

                    // 👉 Later we can update row dynamically
                }

            },
            error: function(xhr) {

                let msg = 'Something went wrong';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }

                toastr.error(msg);
            },
            complete: function() {
                // 🔁 Reset button
                btn.prop('disabled', false)
                    .text(btn.attr('data-original-text'));
            }
        });

    });

    function getActionText(status) {
        switch (status) {
            case 'approved':
                return 'Approve';
            case 'sent':
                return 'Send'; // 🔥 fix here
            case 'cancelled':
                return 'Cancel';
            default:
                return status;
        }
    }
</script>


<script>
    $(document).on('click', '.editPOBtn', function() {

        let poId = $(this).data('id');

        // Set form action
        $('#EditForm').attr('action', '/inventory-management/purchase-orders/' + poId);

        // Fill basic fields
        $('#edit_vendor_id').val($(this).data('vendor_id')).trigger('change');
        $('#edit_po_date').val($(this).data('po_date'));
        $('#edit_expected_delivery_date').val($(this).data('expected_delivery_date'));
        $('#edit_discount').val($(this).data('discount'));
        $('#edit_notes').val($(this).data('notes'));

        let items = $(this).data('items');
        let wrapper = $('#itemsWrapper');
        // let wrapper = $('.itemsWrapper');
        wrapper.html('');

        $.each(items, function(index, item) {

            let row = `
        <div class="item-card mb-2">
            <div class="row">

                <div class="col-md-2">
                    <label>Item</label>
                    <select name="items[${index}][item_id]" class="form-control select2 itemSelect">
                        <option value="">Select Item</option>
                        @foreach($items as $i)
                        <option value="{{ $i->id }}"
                            data-price="{{ $i->purchase_price }}"
                            data-tax="{{ $i->tax_percentage }}"
                            data-unit-id="{{ $i->unit_id }}"
                            data-unit-name="{{ $i->unit->short_name }}">
                            {{ $i->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label>Qty</label>
                    <input type="number" name="items[${index}][quantity]" value="${item.quantity}" class="form-control qty">
                </div>

                <div class="col-md-1">
                    <label>Unit</label>
                    <input type="text" class="form-control unit_text" value="${item.unit?.short_name ?? ''}" readonly>
                    <input type="hidden" name="items[${index}][unit_id]" value="${item.unit_id}" class="unit_id">
                </div>

                <div class="col-md-2">
                    <label>Rate</label>
                    <input type="number" name="items[${index}][rate]" value="${item.rate}" class="form-control rate">
                </div>

                <div class="col-md-2">
                    <label>Tax %</label>
                    <input type="number" name="items[${index}][tax_percentage]" value="${item.tax_percentage}" class="form-control tax">
                </div>

                <div class="col-md-2">
                    <label>Total</label>
                    <input type="text" class="form-control total" readonly>
                </div>

                <div class="col-md-1 d-flex align-items-start justify-content-center mt-4">
                    <button type="button" class="btn btn-danger btn-sm removeItem"><i class="fa fa-trash"></i></button>
                </div>

            </div>
        </div>`;

            wrapper.append(row);

            // Set selected item AFTER append
            wrapper.find(`select[name="items[${index}][item_id]"]`)
                .val(item.item_id)
                .trigger('change');
        });

        calculateTotal('#EditForm');
        $('#EditForm .item-card').each(function() {

            let qty = parseFloat($(this).find('.qty').val()) || 0;
            let rate = parseFloat($(this).find('.rate').val()) || 0;
            let tax = parseFloat($(this).find('.tax').val()) || 0;

            let sub = qty * rate;
            let taxAmount = sub * tax / 100;
            let total = sub + taxAmount;

            $(this).find('.total').val(total.toFixed(2));
        });
        $('#editModal').modal('show');


    });
</script>

@endsection