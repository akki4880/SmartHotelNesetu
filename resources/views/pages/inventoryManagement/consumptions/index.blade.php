@extends('layouts.master')

@section('title', 'Consumption')
@section('mainbreadcrumb', 'Inventory Management')
@section('subbreadcrumb', 'Consumption')
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

                        <!-- Consumption Number -->
                        <div class="col-3">
                            <label>Consumption No</label>
                            <input type="text" name="consumption_number" class="form-control"
                                value="{{ request('consumption_number') }}" placeholder="Enter Number">
                        </div>

                        <!-- Date -->
                        <div class="col-3">
                            <label>Date</label>
                            <input type="date" name="consumption_date" class="form-control"
                                value="{{ request('consumption_date') }}">
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

                        <!-- Created By -->
                        <div class="col-3">
                            <label>Created By</label>
                            <select name="created_by" class="form-control select2">
                                <option value="">Select User</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ request('created_by') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="col-3 mt-2">
                            <div class="d-flex">
                                <a href="{{ url()->current() }}" class="btn btn-secondary mt-4 mx-2">Reset</a>
                                <button type="submit" class="btn btn-info mt-4">Apply</button>
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
                <h3 class="mb-0 fw-bolder">Consumption</h3>
                <div class="float-right">
                    @if(auth()->user()->hasPermission('consumption', 'create'))
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
                    @if(!$consumptions->isEmpty())
                    <table class="table m-0 table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                @if(auth()->user()->hasPermission('consumption', 'view') || auth()->user()->hasPermission('consumption', 'delete'))
                                <th>Action</th>@endif
                                <th>Sr</th>
                                <th>Cons. No</th>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Total Items</th>
                                <th>Total Qty</th>
                                <th>Created By</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($consumptions as $consumption)
                            <tr>
                                @if(auth()->user()->hasPermission('consumption', 'view') || auth()->user()->hasPermission('consumption', 'delete'))
                                <td>
                                    <div class="d-flex">
                                        @if(auth()->user()->hasPermission('consumption', 'view'))

                                        {{-- VIEW --}}
                                        <button class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader mx-1"
                                            data-url="{{ route('consumption.show', $consumption->id) }}" title="View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @endif

                                        @if(auth()->user()->hasPermission('consumption', 'delete'))
                                        {{-- DELETE --}}
                                        <button class="btn btn-outline-danger btn-icon btn-sm delete-btn btn-loader"
                                            data-id="{{ $consumption->id }}"
                                            data-url="{{ route('consumption.destroy', $consumption->id) }}"
                                            title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                                @endif

                                <td>{{ $consumptions->firstItem() + $loop->index }}</td>

                                <td>{{ formatDate($consumption->consumption_date) }}</td>

                                <td>{{ $consumption->consumption_number }}</td>

                                <td>{{ $consumption->category->name ?? '-' }}</td>

                                <td>
                                    {{ $consumption->items->count() }}
                                </td>
                                <td>
                                    @foreach($consumption->items as $item)
                                    <span class="badge badge-info mr-1 mb-1">
                                        {{ $item->item->name }}
                                        ({{ $item->quantity }} {{ $item->unit->short_name }})
                                    </span>
                                    @endforeach
                                </td>
                                <td>{{ $consumption->createdBy->name ?? '-' }}</td>
                                <td>{{ $consumption->notes ?? '-' }}</td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $consumptions->links() }}
                    </div>

                    @else
                    No Consumption Records found
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
                    Create Consumption
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="CreateForm" action="{{ route('consumption.store') }}" method="POST" class="ajax-form"
                data-reset="1" data-modal="#createModal" novalidate>
                @csrf
                <div class="modal-body">

                    <div class="row">

                        <div class="col-md-4">
                            <label>Date</label>
                            <input type="date" name="consumption_date" class="form-control" required>
                            <small class="text-danger error-text" data-error="consumption_date"></small>
                        </div>

                        <div class="col-md-4">
                            <label>Category</label>
                            <select name="category_id" class="form-control select2" id="categorySelect" required>
                                <option value="">Select Item</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                            <small class="text-danger error-text" data-error="category_id"></small>
                        </div>

                    </div>

                    {{-- ITEMS --}}
                    <div class="mt-4">
                        <div class="d-flex justify-content-between">
                            <h5>Items</h5>
                            <button type="button" id="addItem" class="btn btn-success btn-sm">+ Add</button>
                        </div>

                        <div id="itemsWrapper">

                            {{-- FIRST ITEM --}}
                            <div class="item-card">
                                <div class="row">

                                    <div class="col-md-4">
                                        <label>Item</label>
                                        <select name="items[0][item_id]" class="form-control itemSelect select2" required>
                                            <option value="">Select Item</option>

                                        </select>
                                        <small class="text-danger error-text" data-error="items.0.item_id"></small>
                                    </div>

                                    <div class="col-md-2">
                                        <label>Unit</label>
                                        <input type="text" class="form-control unit_text" readonly>
                                        <input type="hidden" name="items[0][unit_id]" class="unit_id">
                                        <small class="text-danger error-text" data-error="items.0.unit_id"></small>
                                    </div>

                                    <div class="col-md-3">
                                        <label>Quantity</label>
                                        <input type="number" step="0.01" name="items[0][quantity]" class="form-control qty" required>
                                        <small class="text-danger error-text" data-error="items.0.quantity"></small>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

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
                    Edit Consumption
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
    let index = 1;
    let currentItems = []; // ✅ store category items
    $(document).ready(function() {
        $('#addItem').click(function() {

            let options = '<option value="">Select Item</option>';

            currentItems.forEach(item => {
                options += `
            <option value="${item.id}"
                data-unit="${item.unit.short_name}"
                data-unit-id="${item.unit_id}" data-stock="${item.current_stock}">
                ${item.name} (Stock: ${item.current_stock})
            </option>
        `;
            });

            let html = `
    <div class="item-card mt-2">
        <div class="row">

            <div class="col-md-4">
                <select name="items[${index}][item_id]" class="form-control itemSelect">
                    ${options}
                </select>
                <small class="text-danger error-text" data-error="items.${index}.item_id"></small>
            </div>

            <div class="col-md-2">
                <input type="text" class="form-control unit_text" readonly>
                <input type="hidden" name="items[${index}][unit_id]" class="unit_id">
            </div>

            <div class="col-md-3">
                <input type="number" step="0.01" name="items[${index}][quantity]" class="form-control qty">
                <small class="text-danger error-text" data-error="items.${index}.quantity"></small>
            </div>

            <div class="col-md-2">
                <button type="button" class="btn btn-danger removeItem"><i class="fa fa-trash"></i></button>
            </div>

        </div>
    </div>
    `;

            $('#itemsWrapper').append(html);

            // ✅ reinitialize select2
            $('#itemsWrapper .itemSelect').last().select2();
            preventDuplicateItems();
            index++;
        });

        $(document).on('change', '.itemSelect', function() {

            let selected = $(this).find(':selected');

            let unit = selected.data('unit');
            let unitId = selected.data('unit-id');
            let stock = selected.data('stock');

            let card = $(this).closest('.item-card');

            card.find('.unit_text').val(unit);
            card.find('.unit_id').val(unitId);
            card.find('.qty').data('stock', stock);
            preventDuplicateItems();

        });

        $('#categorySelect').on('change', function() {

            let categoryId = $(this).val();

            // ❌ If no category selected → reset everything
            if (!categoryId) {
                $('#itemsWrapper').html(getDefaultItemRow()); // reset to 1 row
                currentItems = [];
                index = 1;
                return;
            }

            $.get('/inventory-management/items-by-category/' + categoryId, function(items) {

                currentItems = items;

                // 🔥 STEP 1: Remove all rows and reset to first row
                $('#itemsWrapper').html(getDefaultItemRow());
                index = 1;

                // 🔥 STEP 2: Fill items in first row
                let select = $('#itemsWrapper').find('.itemSelect');

                let options = '<option value="">Select Item</option>';

                items.forEach(item => {
                    options += `
                <option value="${item.id}"
                    data-unit="${item.unit.short_name}"
                    data-unit-id="${item.unit_id}" data-stock="${item.current_stock}">
                    ${item.name} (Stock: ${item.current_stock})
                </option>
            `;
                });

                select.html(options);

                // 🔥 STEP 3: Reset values
                select.val(null).trigger('change');
                $('#itemsWrapper').find('.unit_text').val('');
                $('#itemsWrapper').find('.unit_id').val('');

                // 🔥 STEP 4: Reinitialize select2
                select.select2({
                    dropdownParent: $('#createModal')
                });

            });

        });
        $(document).on('click', '.removeItem', function() {
            $(this).closest('.item-card').remove();
            preventDuplicateItems(); // ✅ re-enable options

        });

        $('#addItem').prop('disabled', true);

        $('#categorySelect').on('change', function() {

            let value = $(this).val();

            if (value) {
                // ✅ category selected
                $('#addItem').prop('disabled', false);
            } else {
                // ❌ no category selected
                $('#addItem').prop('disabled', true);
            }

        });

        $(document).on('input', '.qty', function() {

            let qty = parseFloat($(this).val());
            let stock = parseFloat($(this).data('stock'));

            if (qty > stock) {
                toastr.warning('Quantity cannot be more than available stock (' + stock + ')');

                $(this).val(stock); // ✅ reset to max allowed
            }

        });

        function getDefaultItemRow() {
            return `
    <div class="item-card">
        <div class="row">

            <div class="col-md-4">
                <label>Item</label>
                <select name="items[0][item_id]" class="form-control itemSelect">
                    <option value="">Select Item</option>
                </select>
                <small class="text-danger error-text" data-error="items[0][item_id]"></small>
            </div>

            <div class="col-md-2">
                <label>Unit</label>
                <input type="text" class="form-control unit_text" readonly>
                <input type="hidden" name="items[0][unit_id]" class="unit_id">
            </div>

            <div class="col-md-3">
                <label>Quantity</label>
                <input type="number" step="0.01" name="items[0][quantity]" class="form-control qty">
                <small class="text-danger error-text" data-error="items[0][quantity]"></small>
            </div>

        </div>
    </div>
    `;
        }
    });

    function preventDuplicateItems() {

        let selectedValues = [];

        // ✅ collect all selected item IDs
        $('.itemSelect').each(function() {
            let val = $(this).val();
            if (val) selectedValues.push(val);
        });

        // ✅ loop again and disable options
        $('.itemSelect').each(function() {

            let currentSelect = $(this);
            let currentValue = currentSelect.val();

            currentSelect.find('option').each(function() {

                let optionVal = $(this).attr('value');

                if (!optionVal) return;

                // ❌ disable if selected elsewhere
                if (selectedValues.includes(optionVal) && optionVal !== currentValue) {
                    $(this).prop('disabled', true);
                } else {
                    $(this).prop('disabled', false);
                }

            });

            // ✅ refresh select2 UI
            currentSelect.trigger('change.select2');

        });

    }

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