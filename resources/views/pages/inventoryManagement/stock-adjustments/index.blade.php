@extends('layouts.master')

@section('title', 'Stock Adjustments')
@section('mainbreadcrumb', 'Inventory Management')
@section('subbreadcrumb', 'Stock Adjustments')
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
                <!-- FILTER FORM (HIDDEN BY DEFAULT) -->
                <form method="GET" action="{{ url()->current() }}">
                    <div class="row">

                        <!-- Adjustment Number -->
                        <div class="col-3">
                            <label>Adjustment No</label>
                            <input type="text" name="adjustment_number" class="form-control"
                                value="{{ request('adjustment_number') }}" placeholder="Enter Number">
                        </div>

                        <!-- Date -->
                        <div class="col-3">
                            <label>Date</label>
                            <input type="date" name="adjustment_date" class="form-control"
                                value="{{ request('adjustment_date') }}">
                        </div>

                        <!-- Adjustment Type -->
                        <div class="col-3">
                            <label>Adjustment Type</label>
                            <select name="adjustment_type" class="form-control">
                                <option value="">Select Type</option>
                                <option value="excess" {{ request('adjustment_type') == 'excess' ? 'selected' : '' }}>Excess</option>
                                <option value="short" {{ request('adjustment_type') == 'short' ? 'selected' : '' }}>Short</option>
                            </select>
                        </div>

                        <!-- User -->
                        <div class="col-3 ">
                            <label>User</label>
                            <select name="user_id" class="form-control select2">
                                <option value="">Select User</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Date Range (Optional 🔥) -->
                        <div class="col-3 mt-2">
                            <label>From Date</label>
                            <input type="date" name="from_date" class="form-control"
                                value="{{ request('from_date') }}">
                        </div>

                        <div class="col-3 mt-2">
                            <label>To Date</label>
                            <input type="date" name="to_date" class="form-control"
                                value="{{ request('to_date') }}">
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
                <h3 class="mb-0 fw-bolder">Stock Adjustments</h3>
                <div class="float-right">
                    @if(auth()->user()->hasPermission('stock_adjustments', 'create'))
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
                    @if(!$adjustments->isEmpty())

                    <table class="table table-bordered table-sm" id="table-example">

                        <thead class="bg-primary text-white">
                            <tr>
                                @if(auth()->user()->hasPermission('stock_adjustments', 'view') || auth()->user()->hasPermission('stock_adjustments', 'delete'))
                                <th>Action</th>@endif
                                <th>Sr</th>
                                <th>Adjustment No</th>
                                <th>Date</th>
                                <th>Total Items</th>
                                <th>Adjusted Qty</th>
                                <th>Created By</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($adjustments as $adjustment)

                            @php
                            $totalQty = $adjustment->items->sum(function($item){
                            return abs($item->difference);
                            });
                            @endphp

                            <tr>

                                {{-- ACTION --}}
                                @if(auth()->user()->hasPermission('stock_adjustments', 'view') || auth()->user()->hasPermission('stock_adjustments', 'delete'))
                                <td>
                                    <div class="d-flex">
                                        @if(auth()->user()->hasPermission('stock_adjustments', 'view'))
                                        {{-- VIEW --}}
                                        <button class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                            data-url="{{ route('stock-adjustments.show', $adjustment->id) }}"
                                            title="View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @endif

                                        @if(auth()->user()->hasPermission('stock_adjustments', 'delete'))
                                        {{-- DELETE --}}
                                        <button class="btn btn-outline-danger btn-icon btn-sm delete-btn mx-1"
                                            data-id="{{ $adjustment->id }}"
                                            data-url="{{ route('stock-adjustments.destroy', $adjustment->id) }}"
                                            title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                                @endif

                                {{-- SR --}}
                                <td>{{ $adjustments->firstItem() + $loop->index }}</td>

                                {{-- NUMBER --}}
                                <td>
                                    <strong class="text-primary">
                                        {{ $adjustment->adjustment_number }}
                                    </strong>
                                </td>

                                {{-- DATE --}}
                                <td>{{ formatDate($adjustment->adjustment_date) }}</td>

                                {{-- TOTAL ITEMS --}}
                                <td>{{ $adjustment->items->count() }}</td>

                                {{-- TOTAL ADJUSTMENT --}}
                                <td>
                                    @foreach($adjustment->items as $item)

                                    @php
                                    $diff = $item->difference; // physical - system
                                    @endphp

                                    <span class="badge {{ $diff > 0 ? 'badge-success' : 'badge-danger' }}">
                                        {{ $item->item->name }}
                                        ({{ $diff > 0 ? 'Excess +' : 'Short -' }}{{ number_format(abs($diff), 2) }} {{ $item->unit->short_name }})
                                    </span>

                                    @endforeach
                                </td>

                                {{-- USER --}}
                                <td>{{ $adjustment->user->name ?? '-' }}</td>

                            </tr>

                            @endforeach
                        </tbody>

                    </table>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $adjustments->links() }}
                    </div>

                    @else
                    No Stock Adjustments Found
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Create MOdal -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h4 class="modal-title">
                    Create Stock Adjustments
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="CreateForm" action="{{ route('stock-adjustments.store') }}" method="POST" class="ajax-form"
                data-reset="1" data-modal="#createModal" novalidate>
                @csrf
                <div class="modal-body">

                    {{-- HEADER --}}
                    <div class="row">
                        <div class="col-md-4">
                            <label>Date</label>
                            <input type="date" name="adjustment_date" class="form-control" required>
                            <small class="text-danger error-text" data-error="adjustment_date"></small>
                        </div>
                    </div>

                    {{-- ITEMS --}}
                    <div class="mt-4">
                        <div class="d-flex justify-content-between">
                            <h5>Items</h5>
                            <button type="button" id="addItem" class="btn btn-success btn-sm">+ Add</button>
                        </div>

                        <div id="itemsWrapper">

                            <div class="item-card">
                                <div class="row">

                                    <div class="col-md-3">
                                        <label>Item</label>
                                        <select name="items[0][item_id]" class="form-control itemSelect select2" required>
                                            <option value="">Select Item</option>
                                            @foreach($items as $item)
                                            <option value="{{ $item->id }}"
                                                data-stock="{{ $item->current_stock }}"
                                                data-unit="{{ $item->unit->short_name }}"
                                                data-unit-id="{{ $item->unit_id }}">
                                                {{ $item->name }} (Stock: {{ $item->current_stock }})
                                            </option>
                                            @endforeach
                                        </select>
                                        <small class="text-danger error-text" data-error="items[0][item_id]"></small>
                                    </div>

                                    <div class="col-md-2">
                                        <label>System Stock</label>
                                        <input type="text" class="form-control system_stock" readonly>
                                    </div>

                                    <div class="col-md-2">
                                        <label>Physical Stock</label>
                                        <input type="number" step="0.01" name="items[0][physical_stock]" class="form-control physical_stock" required>
                                        <small class="text-danger error-text" data-error="items[0][physical_stock]"></small>
                                    </div>

                                    <div class="col-md-2">
                                        <label>Difference</label>
                                        <input type="text" class="form-control difference" readonly>
                                    </div>

                                    <div class="col-md-2">
                                        <label>Unit</label>
                                        <input type="text" class="form-control unit_text" readonly>
                                        <input type="hidden" name="items[0][unit_id]" class="unit_id">
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- NOTES --}}
                    <div class="mt-3">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control"></textarea>
                        <small class="text-danger error-text" data-error="notes"></small>
                    </div>

                </div>

                <div class="modal-footer border-top">
                    <button type="reset" class="btn btn-secondary" id="clearaddBtn">Clear</button>
                    <button type="submit" class="btn btn-danger" id="saveRoleBtn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title">
                    Edit Stock Adjustments
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="EditForm" method="POST" class="ajax-form" data-reset="0" data-modal="#editModal" novalidate>

                @csrf
                @method('PUT')

                <div class="modal-body">

                    <div class="row">

                        <!-- Category Name -->
                        <div class="form-group col-6">
                            <label>Name</label>
                            <div class="input-group">
                                <input type="text" name="name" id="edit_category_name" class="form-control" required>
                            </div>
                            <small class="text-danger error-text" data-error="name"></small>
                        </div>

                        <!-- status  -->
                        <div class="form-group col-6">
                            <label>Status</label>
                            <select name="status" id="edit_status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <small class="text-danger error-text" data-error="status"></small>
                        </div>

                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label>Description</label>
                        <div class="input-group">
                            <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                        </div>
                        <small class="text-danger error-text" data-error="description"></small>
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
@include('layouts.common-view')
@include('layouts.confirmationModal')
<script>
    $(document).ready(function() {
        let index = 1;

        $('#addItem').click(function() {

            let options = $('select[name="items[0][item_id]"]').html();

            let html = `
                <div class="item-card mt-2">
                    <div class="row">

                        <div class="col-md-3">
                            <select name="items[${index}][item_id]" class="form-control itemSelect select2" required>
                                ${options}
                            </select>
                        </div>

                        <div class="col-md-2">
                            <input type="text" class="form-control system_stock" readonly>
                        </div>

                        <div class="col-md-2">
                            <input type="number" step="0.01" name="items[${index}][physical_stock]" class="form-control physical_stock" required>
                        </div>

                        <div class="col-md-2">
                            <input type="text" class="form-control difference" readonly>
                        </div>

                        <div class="col-md-2">
                            <input type="text" class="form-control unit_text" readonly>
                            <input type="hidden" name="items[${index}][unit_id]" class="unit_id">
                        </div>

                        <div class="col-md-1 d-flex align-items-center">
                            <button type="button" class="btn btn-danger removeItem">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>

                    </div>
                </div>
                `;

            $('#itemsWrapper').append(html);

            // ✅ IMPORTANT: reinitialize select2
            $('#itemsWrapper .itemSelect').last().select2({
                dropdownParent: $('#createModal')
            });

            index++;
        });

        $(document).on('click', '.removeItem', function() {
            $(this).closest('.item-card').remove();
            updateItemOptions(); // 🔥 ADD THIS

        });

        $(document).on('change', '.itemSelect', function() {

            let selected = $(this).find(':selected');

            let stock = selected.data('stock');
            let unit = selected.data('unit');
            let unitId = selected.data('unit-id');

            let card = $(this).closest('.item-card');

            card.find('.system_stock').val(stock);
            card.find('.unit_text').val(unit);
            card.find('.unit_id').val(unitId);
            updateItemOptions(); // 🔥 ADD THIS
            calculateDifference(card);

        });

        function calculateDifference(card) {

            let physicalVal = card.find('.physical_stock').val();

            // ❌ If empty → clear
            if (physicalVal === '' || isNaN(physicalVal)) {
                card.find('.difference').val('');
                return;
            }

            let system = parseFloat(card.find('.system_stock').val()) || 0;
            let physical = parseFloat(physicalVal);

            let diff = physical - system;

            card.find('.difference').val(diff.toFixed(2));
        }

        $(document).on('input', '.physical_stock', function() {
            let card = $(this).closest('.item-card');
            calculateDifference(card);
        });

        function updateItemOptions() {

            let selectedValues = [];

            // collect all selected values
            $('.itemSelect').each(function() {
                let val = $(this).val();
                if (val) selectedValues.push(val);
            });

            // loop all dropdowns
            $('.itemSelect').each(function() {

                let current = $(this);

                current.find('option').each(function() {

                    let optionVal = $(this).attr('value');

                    // skip empty
                    if (!optionVal) return;

                    // disable if selected elsewhere
                    if (selectedValues.includes(optionVal) && current.val() != optionVal) {
                        $(this).prop('disabled', true);
                    } else {
                        $(this).prop('disabled', false);
                    }

                });

                // refresh select2
                current.trigger('change.select2');
            });
        }
    });

    $(document).on('click', '.delete-btn', function() {

        deleteUrl = $(this).data('url'); // store URL
        deleteRow = $(this).closest('tr'); // store row

        // Optional: change message
        $('#confirmationModalBody').html('<p>Are you sure you want to delete this record?</p>');
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
                toastr.success('Record deleted successfully!');

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


@endsection