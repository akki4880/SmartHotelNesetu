@extends('layouts.master')

@section('title', 'Item Master')
@section('mainbreadcrumb', 'Inventory Management')
@section('subbreadcrumb', 'Item Master')
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

                        <!-- Name -->
                        <div class="col-3">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" value="{{ request('name') }}"
                                    placeholder="Name">
                            </div>
                        </div>

                        <!-- category  -->
                        <div class="col-3">
                            <div class="form-group">
                                <label>Category</label>
                                <select name="category_id" class="form-control select2">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ request('category_id')==$cat->id ? 'selected' : ''
                                        }}>
                                        {{ $cat->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Unit -->
                        <div class="col-3 mt-1">
                            <label>Unit</label>
                            <select name="unit_id" class="form-control select2">
                                <option value="">Select Unit</option>
                                @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ request('unit_id')==$unit->id ? 'selected' : '' }}>
                                    {{ $unit->short_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Vendor -->
                        <div class="col-3 mt-1">
                            <label>Vendor</label>
                            <select name="vendor_id" class="form-control select2">
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ request('vendor_id')==$vendor->id ? 'selected' : ''
                                    }}>
                                    {{ $vendor->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="col-3">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="">Select Status</option>
                                    <option value="1" {{ request('status')==='1' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="0" {{ request('status')==='0' ? 'selected' : '' }}>Inactive
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="col-3">
                            <div class="d-flex">
                                <div class="mx-2">
                                    <a href="{{ url()->current() }}" class="btn btn-secondary mt-4">
                                        Reset
                                    </a>
                                </div>
                                <div>
                                    <button type="submit" class="btn btn-info mt-4">
                                        Apply
                                    </button>
                                </div>
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
                <h3 class="mb-0 fw-bolder">Item Master</h3>
                <div class="float-right">
                    @if(auth()->user()->hasPermission('item_master', 'create'))
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
                    @if(!$items->isEmpty())
                    <table class="table table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                 @if(auth()->user()->hasPermission('item_master', 'view') || auth()->user()->hasPermission('item_master', 'edit'))
                                <th>Action</th>@endif
                                <th>Sr</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Unit</th>
                                <th>Stock</th>
                                <th>Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($items as $item)

                            <tr>
                                @if(auth()->user()->hasPermission('item_master', 'view') || auth()->user()->hasPermission('item_master', 'edit'))
                                <td class="d-flex">
                                    @if(auth()->user()->hasPermission('item_master', 'view'))

                                    <button
                                        class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                        data-url="{{ route('items.show', $item->id) }}" title="View">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    @endif
                                    @if(auth()->user()->hasPermission('item_master', 'edit'))

                                    <a href="javascript:void(0)" class="btn one-btn-outline-success btn-icon btn-sm editItemBtn btn-loader mx-1" title="Edit"
                                        data-id="{{ $item->id }}" data-name="{{ $item->name }}"
                                        data-item_code="{{ $item->item_code }}"
                                        data-category_id="{{ $item->category_id }}" data-unit_id="{{ $item->unit_id }}"
                                        data-vendor_id="{{ $item->vendor_id }}" data-barcode="{{ $item->barcode }}"
                                        data-description="{{ $item->description }}"
                                        data-opening_stock="{{ $item->opening_stock }}"
                                        data-minimum_stock="{{ $item->minimum_stock }}"
                                        data-maximum_stock="{{ $item->maximum_stock }}"
                                        data-purchase_price="{{ $item->purchase_price }}"
                                        data-selling_price="{{ $item->selling_price }}"
                                        data-tax_percentage="{{ $item->tax_percentage }}"
                                        data-status="{{ $item->status }}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <div class="custom-control custom-switch mx-1 mt-1">
                                        <input type="checkbox"
                                            class="custom-control-input statusToggle globalStatusToggle"
                                            id="switch{{ $item->id }}" data-id="{{ $item->id }}"
                                            data-model="InventoryItem" {{ $item->status ?
                                        'checked' : '' }}>
                                        <label class="custom-control-label" for="switch{{ $item->id }}"></label>
                                    </div>
                                    @endif
                                </td>
@endif
                                <td>{{ $items->firstItem() + $loop->index }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->category->name ?? '-' }}</td>
                                <td>{{ $item->unit->short_name ?? '-' }}</td>
                                <td>{{ $item->current_stock }}</td>
                                <td>{{ $item->purchase_price }}</td>

                                <td>
                                    @if($item->status)
                                    <span class="badge badge-success">Active</span>
                                    @else
                                    <span class="badge badge-danger">Inactive</span>
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
                    No Inventory items found
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
                    Create Inventory Items
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="CreateForm" action="{{ route('items.store') }}" method="POST" class="ajax-form" data-reset="1"
                data-modal="#createModal" novalidate>

                @csrf

                <div class="modal-body">

                    <div class="row">

                        <!-- Name -->
                        <div class="col-4">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" required>
                            <small class="text-danger error-text" data-error="name"></small>
                        </div>

                        <!-- Item Code -->
                        <!-- <div class="col-4">
                            <label>Item Code</label>
                            <input type="text" name="item_code" class="form-control">
                            <small class="text-danger error-text" data-error="item_code"></small>
                        </div> -->

                        <!-- Category -->
                        <div class="col-4">
                            <label>Category</label>
                            <select name="category_id" class="form-control select2" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger error-text" data-error="category_id"></small>
                        </div>

                        <!-- Unit -->
                        <div class="col-4 mt-1">
                            <label>Unit</label>
                            <select name="unit_id" class="form-control select2" required>
                                <option value="">Select Unit</option>
                                @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->short_name }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger error-text" data-error="unit_id"></small>
                        </div>

                        <!-- Vendor -->
                        <div class="col-4 mt-1">
                            <label>Vendor</label>
                            <select name="vendor_id" class="form-control select2">
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger error-text" data-error="vendor_id"></small>
                        </div>

                        <!-- Barcode -->
                        <div class="col-4 mt-1">
                            <label>Barcode</label>
                            <input type="text" name="barcode" class="form-control">
                        </div>

                        <!-- Description -->
                        <div class="col-12 mt-1">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>

                        <!-- STOCK SECTION -->
                        <div class="col-4 mt-1">
                            <label>Opening Stock</label>
                            <input type="number" step="0.01" name="opening_stock" class="form-control" value="0">
                        </div>

                        <div class="col-4 mt-1">
                            <label>Minimum Stock</label>
                            <input type="number" step="0.01" name="minimum_stock" class="form-control" value="0">
                        </div>

                        <div class="col-4 mt-1">
                            <label>Maximum Stock</label>
                            <input type="number" step="0.01" name="maximum_stock" class="form-control">
                        </div>

                        <!-- PRICING -->
                        <div class="col-4 mt-1">
                            <label>Purchase Price<i class="fa fa-info-circle text-secondary ml-1"
                                    data-toggle="tooltip"
                                    data-placement="top"
                                    title="Enter price per unit. Example: If unit is KG, enter price per 1 KG.">
                                </i></label>
                            <input type="number" step="0.01" name="purchase_price" class="form-control" value="0">
                        </div>

                        <div class="col-4 mt-1">
                            <label>Selling Price<i class="fa fa-info-circle text-secondary ml-1"
                                    data-toggle="tooltip"
                                    data-placement="top"
                                    title="Enter price per unit. Example: If unit is KG, enter price per 1 KG.">
                                </i></label>
                            <input type="number" step="0.01" name="selling_price" class="form-control">
                        </div>

                        <div class="col-4 mt-1">
                            <label>Tax (%)</label>
                            <input type="number" step="0.01" name="tax_percentage" class="form-control">
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

                        <!-- Name -->
                        <div class="col-4">
                            <label>Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>

                        <!-- Item Code -->
                        <!-- <div class="col-4">
                            <label>Item Code</label>
                            <input type="text" name="item_code" id="edit_item_code" class="form-control">
                        </div> -->

                        <!-- Category -->
                        <div class="col-4">
                            <label>Category</label>
                            <select name="category_id" id="edit_category_id" class="form-control select2" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Unit -->
                        <div class="col-4 mt-1">
                            <label>Unit</label>
                            <select name="unit_id" id="edit_unit_id" class="form-control select2" required>
                                <option value="">Select Unit</option>
                                @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->short_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Vendor -->
                        <div class="col-4 mt-1">
                            <label>Vendor</label>
                            <select name="vendor_id" id="edit_vendor_id" class="form-control select2">
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Barcode -->
                        <div class="col-4 mt-1">
                            <label>Barcode</label>
                            <input type="text" name="barcode" id="edit_barcode" class="form-control">
                        </div>

                        <!-- Description -->
                        <div class="col-12 mt-1">
                            <label>Description</label>
                            <textarea name="description" id="edit_description" class="form-control"></textarea>
                        </div>

                        <!-- STOCK -->
                        <div class="col-4 mt-1">
                            <label>Opening Stock</label>
                            <input type="number" name="opening_stock" id="edit_opening_stock" class="form-control">
                        </div>

                        <div class="col-4 mt-1">
                            <label>Minimum Stock</label>
                            <input type="number" name="minimum_stock" id="edit_minimum_stock" class="form-control">
                        </div>

                        <div class="col-4 mt-1">
                            <label>Maximum Stock</label>
                            <input type="number" name="maximum_stock" id="edit_maximum_stock" class="form-control">
                        </div>

                        <!-- PRICE -->
                        <div class="col-4 mt-1">
                            <label>Purchase Price<i class="fa fa-info-circle text-info ml-1"
                                    data-toggle="tooltip"
                                    data-placement="top"
                                    title="Enter price per unit. Example: If unit is KG, enter price per 1 KG.">
                                </i></label>
                            <input type="number" name="purchase_price" id="edit_purchase_price" class="form-control">
                        </div>

                        <div class="col-4 mt-1">
                            <label>Selling Price<i class="fa fa-info-circle text-info ml-1"
                                    data-toggle="tooltip"
                                    data-placement="top"
                                    title="Enter price per unit. Example: If unit is KG, enter price per 1 KG.">
                                </i></label>
                            <input type="number" name="selling_price" id="edit_selling_price" class="form-control">
                        </div>

                        <div class="col-4 mt-1">
                            <label>Tax (%)</label>
                            <input type="number" name="tax_percentage" id="edit_tax_percentage" class="form-control">
                        </div>

                        <!-- STATUS -->
                        <div class="col-4 mt-1">
                            <label>Status</label>
                            <select name="status" id="edit_status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
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
@include('layouts.common-view')
<script>
    // When edit button is clicked
    $(document).on('click', '.editItemBtn', function() {

        let el = $(this);
        let itemId = el.data('id');

        let form = $('#EditForm');
        form.attr('action', "{{ url('inventory-management/items') }}/" + itemId);
        console.log('test', form.attr('action'), form);

        $('#edit_name').val(el.data('name') ?? '');
        $('#edit_item_code').val(el.data('item_code') ?? '');

        $('#edit_category_id').val(el.data('category_id') ?? '').trigger('change');
        $('#edit_unit_id').val(el.data('unit_id') ?? '').trigger('change');
        $('#edit_vendor_id').val(el.data('vendor_id') ?? '').trigger('change');

        $('#edit_barcode').val(el.data('barcode') ?? '');
        $('#edit_description').val(el.data('description') ?? '');

        $('#edit_opening_stock').val(el.data('opening_stock') ?? 0);
        $('#edit_minimum_stock').val(el.data('minimum_stock') ?? 0);
        $('#edit_maximum_stock').val(el.data('maximum_stock') ?? '');

        $('#edit_purchase_price').val(el.data('purchase_price') ?? 0);
        $('#edit_selling_price').val(el.data('selling_price') ?? '');
        $('#edit_tax_percentage').val(el.data('tax_percentage') ?? '');

        $('#edit_status').val(el.data('status') ?? 1);

        $('#editModal').modal('show');
    });
</script>

@endsection