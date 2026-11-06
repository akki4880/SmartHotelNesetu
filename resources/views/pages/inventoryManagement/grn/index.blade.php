@extends('layouts.master')

@section('title', 'Goods Receipt(GRN)')
@section('mainbreadcrumb', 'Inventory Management')
@section('subbreadcrumb', 'Goods Receipt(GRN)')
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

                        <!-- GRN Number -->
                        <div class="col-3">
                            <label>GRN Number</label>
                            <input type="text" name="grn_number" class="form-control"
                                value="{{ request('grn_number') }}" placeholder="GRN Number">
                        </div>

                        <!-- GRN Date -->
                        <div class="col-3">
                            <label>GRN Date</label>
                            <input type="date" name="grn_date" class="form-control" value="{{ request('grn_date') }}">
                        </div>

                        <!-- Vendor -->
                        <div class="col-3">
                            <label>Vendor</label>
                            <input type="text" name="vendor_name" class="form-control"
                                value="{{ request('vendor_name') }}" placeholder="Vendor Name">
                        </div>

                        <!-- Invoice -->
                        <div class="col-3">
                            <label>Invoice Number</label>
                            <input type="text" name="invoice_number" class="form-control"
                                value="{{ request('invoice_number') }}" placeholder="Invoice Number">
                        </div>

                        <!-- Grand Total -->
                        <div class="col-3 mt-2">
                            <label>Grand Total</label>
                            <input type="text" name="grand_total" class="form-control"
                                value="{{ request('grand_total') }}" placeholder="Amount">
                        </div>

                        <!-- Created By -->
                        <div class="col-3 mt-2">
                            <label>User</label>
                            <input type="text" name="user_name" class="form-control" value="{{ request('user_name') }}"
                                placeholder="User Name">
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
                <h3 class="mb-0 fw-bolder">Goods Receipt(GRN)</h3>
                <div class="float-right">
                    @if(auth()->user()->hasPermission('goods_receipt', 'create'))
                    <button class="btn btn-danger btn-sm btn-loader" data-toggle="modal" data-target="#createModal"><i
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
                    @if(!$grns->isEmpty())

                    <table class="table table-bordered table-sm" id="table-example">

                        <thead class="bg-primary text-white">
                            <tr>
                                @if(auth()->user()->hasPermission('goods_receipt', 'view') || auth()->user()->hasPermission('goods_receipt', 'edit') || auth()->user()->hasPermission('goods_receipt', 'delete'))
                                <th>Action</th>@endif
                                <th>Sr</th>

                                <th>GRN No</th>
                                <th>Date</th>

                                <th>Vendor</th>
                                <th>PO No</th>
                                <th>Invoice No</th>

                                <th>Sub Total</th>
                                <th>Tax</th>
                                <th>Discount</th>
                                <th>Grand Total</th>

                                <th>Created By</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($grns as $grn)
                            <tr>

                                @if(auth()->user()->hasPermission('goods_receipt', 'view') || auth()->user()->hasPermission('goods_receipt', 'edit') || auth()->user()->hasPermission('goods_receipt', 'delete'))
                                {{-- ACTION --}}
                                <td>
                                    @if(auth()->user()->hasPermission('goods_receipt', 'view'))
                                    <button
                                        class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                        data-url="{{ route('grn.show', $grn->id) }}" title="View">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    @endif
                                    @if(auth()->user()->hasPermission('goods_receipt', 'edit'))
                                    <a href="javascript:void(0)" class="btn one-btn-outline-success btn-icon btn-sm editItemBtn btn-loader mx-1" title="Edit"
                                        data-id="{{ $grn->id }}"
                                        data-vendor_id="{{ $grn->vendor_id }}"
                                        data-purchase_order_id="{{ $grn->purchase_order_id }}"
                                        data-grn_date="{{ $grn->grn_date }}"
                                        data-invoice_number="{{ $grn->invoice_number }}"
                                        data-invoice_date="{{ $grn->invoice_date }}"
                                        data-notes="{{ $grn->notes }}"
                                        data-discount_amount="{{ $grn->discount_amount }}"
                                        data-sub_total="{{ $grn->sub_total }}"
                                        data-grand_total="{{ $grn->grand_total }}"
                                        data-items='@json($grn->edit_items)'>
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('goods_receipt', 'delete'))
                                    <button class="btn btn-outline-danger btn-icon btn-sm delete-btn"
                                        data-url="{{ route('grn.destroy', $grn->id) }}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                    @endif
                                </td>
                                @endif

                                {{-- SR --}}
                                <td>{{ $grns->firstItem() + $loop->index }}</td>

                                {{-- GRN INFO --}}
                                <td><strong>{{ $grn->grn_number }}</strong></td>
                                <td>{{ formatDate($grn->grn_date) }}</td>

                                <td>{{ $grn->vendor->name ?? '-' }}</td>
                                <td>{{ $grn->purchaseOrder->po_number ?? '-' }}</td>
                                <td>{{ $grn->invoice_number ?? '-' }}</td>

                                {{-- FINANCIALS --}}
                                <td>{{ number_format($grn->sub_total, 2) }}</td>
                                <td>{{ number_format($grn->tax_amount, 2) }}</td>
                                <td>{{ number_format($grn->discount_amount ?? 0, 2) }}</td>

                                <td>
                                    <strong>{{ number_format($grn->grand_total, 2) }}</strong>
                                </td>

                                {{-- USER --}}
                                <td>{{ $grn->user->name ?? '-' }}</td>

                            </tr>

                            @endforeach

                        </tbody>

                    </table>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $grns->links() }}
                    </div>

                    @else
                    <div>
                        No GRN records found
                    </div>
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
                    Create Good Receipt(GRN)
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="CreateForm" action="{{ route('grn.store') }}" method="POST" class="ajax-form" data-reset="1"
                data-modal="#createModal" novalidate>

                @csrf

                <div class="modal-body">

                    <div class="row">

                        {{-- Vendor --}}
                        <div class="col-md-4">
                            <label>Vendor</label>
                            <select name="vendor_id" class="form-control select2 vendor-select" required>
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger error-text" data-error="vendor_id"></small>
                        </div>

                        {{-- Purchase Order --}}
                        <div class="col-md-4">
                            <label>Purchase Order</label>
                            <select name="purchase_order_id" class="form-control select2 po-select" required>
                                <option value="">Select PO</option>
                            </select>
                            <small class="text-danger error-text" data-error="purchase_order_id"></small>
                        </div>

                        {{-- GRN Date --}}
                        <div class="col-md-4">
                            <label>GRN Date</label>
                            <input type="date" name="grn_date" class="form-control" required>
                            <small class="text-danger error-text" data-error="grn_date"></small>
                        </div>

                    </div>

                    <div class="row mt-3">

                        <div class="col-md-6">
                            <label>Invoice Number</label>
                            <input type="text" name="invoice_number" class="form-control">
                            <small class="text-danger error-text" data-error="invoice_number"></small>
                        </div>

                        <div class="col-md-6">
                            <label>Invoice Date</label>
                            <input type="date" name="invoice_date" class="form-control">
                            <small class="text-danger error-text" data-error="invoice_date"></small>
                        </div>

                    </div>

                    {{-- ================= ITEMS ================= --}}
                    <div class="mt-4">

                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="text-danger fw-bold">Received Items</h5>
                        </div>

                        <div class="grnItemsWrapper" id="createGrnItemsWrapper">
                        </div>
                    </div>

                    {{-- ================= SUMMARY ================= --}}
                    <div class="row mt-4">

                        <div class="col-md-6">
                            <div class="col-md-12 mb-1 px-0">
                                <label>Discount</label>
                                <input type="number" name="discount_amount" id="discount_amount"
                                    class="form-control discountAmount" value="0" readonly>
                                <small class="text-danger error-text" data-error="discount_amount"></small>
                            </div>
                            <div class="col-md-12 px-0">
                                <label>Notes</label>
                                <textarea name="notes" class="form-control" rows="4"></textarea>
                                <small class="text-danger error-text" data-error="notes"></small>
                            </div>
                        </div>

                        <div class="col-md-6 px-0">

                            <div class="p-3">

                                <h6 class="fw-bold text-danger mb-3">Summary</h6>

                                <div class="d-flex justify-content-between mb-2">
                                    <span>Sub Total</span>
                                    <strong id="subTotalText">₹ 0.00</strong>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span>Discount</span>
                                    <strong id="discount">₹ 0.00</strong>
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
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title">Edit Inventory Item</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span><i class="fal fa-times"></i></span>
                </button>
            </div>

            <form id="EditForm" method="POST" class="ajax-form" data-reset="0" data-modal="#editModal">
                @csrf
                @method('PUT')

                <div class="modal-body">
                    <div class="row">

                        {{-- Vendor --}}
                        <div class="col-md-4">
                            <label>Vendor</label>
                            <select name="vendor_id" class="form-control select2 vendor-select" required>
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger error-text" data-error="vendor_id"></small>
                        </div>

                        {{-- Purchase Order --}}
                        <div class="col-md-4">
                            <label>Purchase Order</label>
                            <select name="purchase_order_id" class="form-control select2 po-select" required>
                                <option value="">Select PO</option>
                            </select>
                            <small class="text-danger error-text" data-error="purchase_order_id"></small>
                        </div>

                        {{-- GRN Date --}}
                        <div class="col-md-4">
                            <label>GRN Date</label>
                            <input type="date" name="grn_date" class="form-control" required>
                            <small class="text-danger error-text" data-error="grn_date"></small>
                        </div>

                    </div>

                    <div class="row mt-3">

                        <div class="col-md-6">
                            <label>Invoice Number</label>
                            <input type="text" name="invoice_number" class="form-control">
                            <small class="text-danger error-text" data-error="invoice_number"></small>
                        </div>

                        <div class="col-md-6">
                            <label>Invoice Date</label>
                            <input type="date" name="invoice_date" class="form-control">
                            <small class="text-danger error-text" data-error="invoice_date"></small>
                        </div>

                    </div>

                    {{-- ================= ITEMS ================= --}}
                    <div class="mt-4">

                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="text-danger fw-bold">Received Items</h5>
                        </div>

                        <div class="grnItemsWrapper" id="editGrnItemsWrapper">
                        </div>
                    </div>

                    {{-- ================= SUMMARY ================= --}}
                    <div class="row mt-4">

                        <div class="col-md-6">
                            <div class="col-md-12 mb-1 px-0">
                                <label>Discount</label>
                                <input type="number" name="discount_amount" class="form-control discountAmount" value="0" readonly>
                                <small class="text-danger error-text" data-error="discount_amount"></small>
                            </div>
                            <div class="col-md-12 px-0">
                                <label>Notes</label>
                                <textarea name="notes" class="form-control" rows="4"></textarea>
                                <small class="text-danger error-text" data-error="notes"></small>
                            </div>
                        </div>

                        <div class="col-md-6 px-0">

                            <div class="p-3">

                                <h6 class="fw-bold text-danger mb-3">Summary</h6>

                                <div class="d-flex justify-content-between mb-2">
                                    <span>Sub Total</span>
                                    <strong class="subTotalText">₹ 0.00</strong>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span>Discount</span>
                                    <strong class="discountText">₹ 0.00</strong>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <span>Grand Total</span>
                                    <strong class="text-success grandTotalText">₹ 0.00</strong>
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
    // 🔁 Row calculation
    function calculateRow($row) {

        let qty = parseFloat($row.find('.received_qty').val()) || 0;
        let rate = parseFloat($row.find('.rate').val()) || 0;
        let taxPercent = parseFloat($row.find('.tax_percentage').val()) || 0;

        // ✅ ALWAYS use original remaining
        let originalRemaining = parseFloat($row.find('.remaining_original').val()) || 0;

        let newRemaining = originalRemaining - qty;

        // prevent negative
        if (newRemaining < 0) newRemaining = 0;

        $row.find('.remaining_qty').text(newRemaining.toFixed(2));

        let base = qty * rate;
        let tax = (base * taxPercent) / 100;

        let total = base + tax;

        $row.find('.total').val(total.toFixed(2));

        calculateSummary();
    }

    // 🔁 Summary calculation
    function calculateSummary() {

        let $scope = $('.modal.show').length ? $('.modal.show') : $('body');
        let subTotal = 0;

        $scope.find('.item-card').each(function() {
            let rowTotal = parseFloat($(this).find('.total').val()) || 0;
            subTotal += rowTotal;
        });

        let discount = parseFloat($scope.find('.discountAmount').val()) || 0;

        let grandTotal = subTotal - discount;
        if (grandTotal < 0) grandTotal = 0;

        let $subTotalText = $scope.find('#subTotalText, .subTotalText');
        let $discountText = $scope.find('#discount, .discountText');
        let $grandTotalText = $scope.find('#grandTotalText, .grandTotalText');

        $subTotalText.text('₹ ' + subTotal.toFixed(2));
        $discountText.text('₹ ' + discount.toFixed(2));
        $grandTotalText.text('₹ ' + grandTotal.toFixed(2));
    }

    // 🔁 Events
    // $(document).on('input', '.received_qty', function() {
    //     let $row = $(this).closest('.item-card');
    //     calculateRow($row);
    // });

    $(document).on('input', '.received_qty', function() {

        let $row = $(this).closest('.item-card');

        let received = parseFloat($(this).val()) || 0;

        // ✅ get ORIGINAL remaining
        let originalRemaining = parseFloat($row.find('.remaining_original').val()) || 0;

        let remainingEl = $row.find('.remaining_qty');

        // ✅ validation against remaining
        if (received > originalRemaining) {

            toastr.warning('Received quantity cannot exceed remaining quantity');

            $(this).val(originalRemaining); // force correct value
            $(this).addClass('is-invalid');

            received = originalRemaining; // update for further calc
        } else {
            $(this).removeClass('is-invalid');
        }

        // ✅ calculate new remaining (visual only)
        let newRemaining = originalRemaining - received;
        if (newRemaining < 0) newRemaining = 0;

        remainingEl.text(newRemaining.toFixed(2));

        // ✅ color logic
        if (newRemaining <= 5) {
            remainingEl.removeClass().addClass('remaining_qty text-danger fw-bold');
        } else {
            remainingEl.removeClass().addClass('remaining_qty text-success');
        }

        calculateRow($row);
    });

    $(document).on('input', '.discountAmount', function() {
        calculateSummary();
    });

    // 📌 When received qty changes
    $(document).on('input', '.received_qty', function() {
        let $row = $(this).closest('.item-card');
        calculateRow($row);
    });

    // 📌 If rate changes (in case PO edit / override later)
    $(document).on('input', '.rate', function() {
        let $row = $(this).closest('.item-card');
        calculateRow($row);
    });

    $(document).ready(function() {
        $(document).on('select2:select', '.vendor-select', function() {

            let $modal = $(this).closest('.modal');
            let $poSelect = $modal.find('.po-select');
            let $wrapper = $modal.find('.grnItemsWrapper');
            let vendorId = $(this).val();

            $poSelect.html('<option value="">Loading...</option>').trigger('change');
            $wrapper.html('');

            $.ajax({
                url: '/inventory-management/vendors/' + vendorId + '/purchase-orders',
                type: 'GET',
                success: function(res) {

                    let options = '<option value="">Select PO</option>';

                    res.data.forEach(po => {
                        options += `<option value="${po.id}">${po.po_number}</option>`;
                    });

                    $poSelect.html(options).trigger('change');

                },
                error: function() {
                    toastr.error("Failed to load purchase orders");
                }
            });

        });

        $(document).on('select2:select', '.po-select', function(e) {

            let $modal = $(this).closest('.modal');
            let poId = $(this).val();
            if (!poId) return;

            $.get('/inventory-management/purchase-orders/' + poId + '/items', function(res) {

                let wrapper = $modal.find('.grnItemsWrapper');
                wrapper.html('');

                $modal.find('.discountAmount').val(res.discount || 0);

                let index = 0;

                res.items.forEach(item => {

                    let html = `
           <div class="item-card">
                <div class="row">

                    {{-- Item --}}
                    <div class="col-md-2">
                        <label>Item</label>
                        <input type="text" class="form-control" value="${item.name}" readonly>
                        <input type="hidden" name="items[${index}][item_id]" value="${item.item_id}">
                        <input type="hidden" name="items[${index}][tax_percentage]" class="tax_percentage" value="${item.tax_percentage}">
                        <input type="hidden" name="items[${index}][purchase_order_item_id]" value="${item.purchase_order_item_id}">
                        <small class="text-danger error-text" data-error="item_id"></small>
                    </div>

                    {{-- Ordered --}}
                    <div class="col-md-2">
                        <label>Ordered</label>
                        <input type="text" class="form-control ordered_qty" value="${item.quantity}" readonly>
                        <small class="text-info d-block mt-1">
                Remaining: <span class="remaining_qty">${item.remaining_qty}</span>
            </small>
            <input type="hidden" class="remaining_original" value="${item.remaining_qty}">
                        <small class="text-danger error-text" data-error="ordered_qty"></small>
                    </div>

                    {{-- Received --}}
                    <div class="col-md-2">
                        <label>Received</label>
                        <input type="number" step="0.01"
                            name="items[${index}][received_quantity]"
                            class="form-control received_qty" max="${item.quantity}">

                        <small class="text-danger error-text" data-error="received_quantity"></small>
                    </div>

                    {{-- Unit --}}
                    <div class="col-md-1">
                        <label>Unit</label>
                        <input type="text" class="form-control" value="${item.unit_name}" readonly>
                        <input type="hidden" name="items[${index}][unit_id]" value="${item.unit_id}">
                        <small class="text-danger error-text" data-error="unit_id"></small>
                    </div>

                    {{-- Rate --}}
                    <div class="col-md-2">
                        <label>Rate</label>
                        <input type="number" step="0.01"
                            name="items[${index}][rate]"
                            class="form-control rate"
                            value="${item.rate}" readonly>

                        <small class="text-danger error-text" data-error="rate"></small>
                    </div>

                    {{-- Tax --}}
                    <div class="col-md-1">
                        <label>Tax %</label>
                        <input type="text" class="form-control tax_display"
                            value="${item.tax_percentage}" readonly>

                        <small class="text-danger error-text" data-error="tax_percentage"></small>
                    </div>

                    {{-- Total --}}
                    <div class="col-md-2">
                        <label>Total</label>
                        <input type="text" class="form-control total" readonly>

                        <small class="text-danger error-text" data-error="total"></small>
                    </div>

                </div>
                    </div>
                        `;

                    wrapper.append(html);

                    let $newRow = wrapper.find('.item-card').last();
                    calculateRow($newRow);
                    index++;
                });

                calculateSummary();
            });
        });


    });

    $(document).on('click', '.delete-btn', function() {

        deleteUrl = $(this).data('url'); // store URL
        deleteRow = $(this).closest('tr'); // store row

        // Optional: change message
        $('#confirmationModalBody').html(`
                <p>Are you sure you want to delete this GRN?</p>
                <small class="text-danger">
                    This will reverse stock and cannot be undone.
                </small>
            `);
        $('#confirmationModalTitle').html('Delete Confirmation');

        $('#confirmationModal').modal('show');
    });

    $('#confirmationModalConfirmBtn').click(function() {

        let btn = $(this);
        let originalText = btn.text();

        // 🔄 loader (optional)
        btn.text('Deleting...').prop('disabled', true);

        $.ajax({
            url: deleteUrl,
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {

                $('#confirmationModal').modal('hide');

                // ✅ remove row with animation
                // deleteRow.fadeOut(300, function() {
                //     $(this).remove();
                // });
                $('#table-container').load(location.href + ' #table-container > *');
                toastr.success('GRN deleted & stock reversed successfully');

            },
            error: function() {
                toastr.error('Something went wrong!');
            },
            complete: function() {
                btn.text(originalText).prop('disabled', false);
            }
        });

    });
</script>


<script>
    $(document).on('click', '.editItemBtn', function() {

        let el = $(this);
        let id = el.data('id');

        let form = $('#EditForm');
        let $editModal = $('#editModal');

        form.attr('action', "{{ url('inventory-management/grn') }}/" + id);

        let $vendor = $editModal.find('.vendor-select');
        let $po = $editModal.find('.po-select');

        let vendorId = el.attr('data-vendor_id');
        let poId = el.attr('data-purchase_order_id');

        let grnDate = el.attr('data-grn_date');
        let invoiceDate = el.attr('data-invoice_date');

        // BASIC FIELDS
        $editModal.find('input[name="grn_date"]').val(
            grnDate ? grnDate.substring(0, 10) : ''
        );

        $editModal.find('input[name="invoice_number"]').val(
            el.attr('data-invoice_number')
        );

        $editModal.find('input[name="invoice_date"]').val(
            invoiceDate ? invoiceDate.substring(0, 10) : ''
        );

        $editModal.find('textarea[name="notes"]').val(
            el.attr('data-notes')
        );

        $editModal.find('.discountAmount').val(
            el.attr('data-discount_amount')
        );

        // SUMMARY
        $editModal.find('.subTotalText').text(
            '₹ ' + (el.attr('data-sub_total') || '0.00')
        );

        $editModal.find('.discountText').text(
            '₹ ' + (el.attr('data-discount_amount') || '0.00')
        );

        $editModal.find('.grandTotalText').text(
            '₹ ' + (el.attr('data-grand_total') || '0.00')
        );

        // VENDOR
        $vendor.val(vendorId).trigger('change');

        // PO
        $po.html('<option value="">Loading...</option>');

        // LOAD PURCHASE ORDERS
        $.ajax({
            url: '/inventory-management/vendors/' + vendorId + '/purchase-orders?formaction=edit',
            type: 'GET',

            success: function(res) {

                let options = '<option value="">Select PO</option>';

                res.data.forEach(function(po) {
                    options += `
                    <option value="${po.id}">
                        ${po.po_number}
                    </option>
                `;
                });

                $po.html(options);

                // Select existing PO
                $po.val(poId).trigger('change');

                // GET ITEMS DIRECTLY FROM EDIT BUTTON
                let items = $(this).attr('data-items');

                // Render items
                renderGrnItems(
                    $editModal.find('.grnItemsWrapper'),
                    items
                );

                calculateSummary();

                // Show modal
                $editModal.modal('show');

            },

            error: function() {
                toastr.error('Failed to load purchase orders');
            }

        });

    });

    function renderGrnItems($wrapper, items) {
        $wrapper.html('');
        console.log('Raw items:', items);
        items = JSON.parse(items || '[]');
        if (!Array.isArray(items) || items.length === 0) {
            $('.grnItemsWrapper').html(
                '<p class="text-center text-muted">No items found.</p>'
            );
            return;
        }
        console.log('Parsed items:', items);
        console.log('Items count:', items.length);
        items.forEach(function(item, index) {

            let received = parseFloat(item.received_quantity) || 0;
            let remaining = parseFloat(item.remaining_quantity) || 0;
            let rate = parseFloat(item.rate) || 0;
            let tax = parseFloat(item.tax_percentage) || 0;

            let html = `
            <div class="item-card mb-3">
                <div class="row">

                    <div class="col-md-2">
                        <label>Item</label>

                        <input type="text"
                            class="form-control"
                            value="${item.item_name || ''}"
                            readonly>

                        <input type="hidden"
                            name="items[${index}][item_id]"
                            value="${item.item_id}">

                        <input type="hidden"
                            name="items[${index}][purchase_order_item_id]"
                            value="${item.purchase_order_item_id}">

                        <input type="hidden"
                            name="items[${index}][tax_percentage]"
                            class="tax_percentage"
                            value="${tax}">
                    </div>

                    <div class="col-md-2">
                        <label>Ordered</label>

                        <input type="text"
                            class="form-control ordered_qty"
                            value="${item.ordered_quantity || 0}"
                            readonly>

                        <small class="text-info d-block mt-1">
                            Remaining:
                            <span class="remaining_qty">${remaining.toFixed(2)}</span>
                        </small>

                        <input type="hidden"
                            class="remaining_original"
                            value="${remaining}">
                    </div>

                    <div class="col-md-2">
                        <label>Received</label>

                        <input type="number"
                            step="0.01"
                            min="0"
                            max="${remaining}"
                            name="items[${index}][received_quantity]"
                            class="form-control received_qty"
                            value="${received}">

                        <small class="text-danger error-text"
                            data-error="received_quantity"></small>
                    </div>

                    <div class="col-md-1">
                        <label>Unit</label>

                        <input type="text"
                            class="form-control"
                            value="${item.unit_name || ''}"
                            readonly>

                        <input type="hidden"
                            name="items[${index}][unit_id]"
                            value="${item.unit_id}">
                    </div>

                    <div class="col-md-2">
                        <label>Rate</label>

                        <input type="number"
                            step="0.01"
                            name="items[${index}][rate]"
                            class="form-control rate"
                            value="${rate}"
                            readonly>
                    </div>

                    <div class="col-md-1">
                        <label>Tax %</label>

                        <input type="text"
                            class="form-control tax_display"
                            value="${tax}"
                            readonly>
                    </div>

                    <div class="col-md-2">
                        <label>Total</label>

                        <input type="text"
                            name="items[${index}][total]"
                            class="form-control total"
                            value="${item.total || 0}"
                            readonly>
                    </div>

                </div>
            </div>
        `;

            $wrapper.append(html);

        });

    }
</script>
@endsection