@extends('layouts.master')

@section('title', 'Unit Master')
@section('mainbreadcrumb', 'Inventory Management')
@section('subbreadcrumb', 'Unit Master')
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

                        <!-- Name -->
                        <div class="col-3">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control"
                                    value="{{ request('name') }}" placeholder="Unit Name">
                            </div>
                        </div>

                        <!-- Short Name -->
                        <div class="col-3">
                            <div class="form-group">
                                <label>Short Name</label>
                                <input type="text" name="short_name" class="form-control"
                                    value="{{ request('short_name') }}" placeholder="Short Name">
                            </div>
                        </div>

                        <!-- Unit Type -->
                        <div class="col-3">
                            <div class="form-group">
                                <label>Unit Type</label>
                                <input type="text" name="unit_type" class="form-control"
                                    value="{{ request('unit_type') }}" placeholder="Unit Type">
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-3">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="">Select Status</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="col-4 mt-3">
                            <div class="d-flex">
                                <div class="mx-2">
                                    <a href="{{ url()->current() }}" class="btn btn-secondary">
                                        Reset
                                    </a>
                                </div>
                                <div>
                                    <button type="submit" class="btn btn-info">
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
                <h3 class="mb-0 fw-bolder">Unit Master</h3>
                <div class="float-right">
                    @if(auth()->user()->hasPermission('unit_master', 'create'))
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
                    @if(!$units->isEmpty())
                    <table class="table m-0 table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                @if(auth()->user()->hasPermission('unit_master', 'edit') || auth()->user()->hasPermission('unit_master', 'view'))
                                <th>Action</th>@endif
                                <th>Sr</th>
                                <th>Name</th>
                                <th>Short Name</th>
                                <th>Unit Type</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($units as $unit)
                            <tr>
                                @if(auth()->user()->hasPermission('unit_master', 'edit') || auth()->user()->hasPermission('unit_master', 'view'))
                                <td>
                                    <div class="d-flex">
                                        @if(auth()->user()->hasPermission('unit_master', 'view'))
                                        <button
                                            class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                            data-url="{{ route('units.show', $unit->id) }}" title="View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('unit_master', 'edit'))
                                        <a href="javascript:void(0);"
                                            class="btn one-btn-outline-success btn-icon btn-sm editUnitBtn btn-loader mx-1"
                                            data-id="{{ $unit->id }}"
                                            data-name="{{ $unit->name }}"
                                            data-short_name="{{ $unit->short_name }}"
                                            data-unit_type="{{ $unit->unit_type }}"
                                            data-status="{{ $unit->status }}"
                                            title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>

                                        <div class="custom-control custom-switch mt-1 ml-1">
                                            <input type="checkbox"
                                                class="custom-control-input statusToggle globalStatusToggle"
                                                id="switch{{ $unit->id }}"
                                                data-id="{{ $unit->id }}"
                                                data-model="InventoryUnit"
                                                {{ $unit->status ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="switch{{ $unit->id }}"></label>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                                @endif
                                <td>{{ $units->firstItem() + $loop->index }}</td>
                                <td>{{ $unit->name }}</td>
                                <td>{{ $unit->short_name }}</td>
                                <td>{{ $unit->unit_type ?? '-' }}</td>

                                <td>
                                    @if($unit->status)
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
                        {{ $units->links() }}
                    </div>
                    @else
                    No Inventory Units found
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Create MOdal -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h4 class="modal-title">
                    Create Inventory Units
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="CreateUnitForm" action="{{ route('units.store') }}" method="POST"
                class="ajax-form" data-reset="1" data-modal="#createUnitModal">

                @csrf

                <div class="modal-body row">

                    <div class="form-group col-6">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="form-group col-6">
                        <label>Short Name</label>
                        <input type="text" name="short_name" class="form-control" required>
                    </div>

                    <div class="form-group col-6">
                        <label>Unit Type</label>
                        <input type="text" name="unit_type" class="form-control">
                    </div>

                    <div class="form-group col-6">
                        <label>Status</label>
                        <select name="status" id="edit_unit_status" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
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
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title">
                    Edit Inventory Units
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="EditForm" method="POST"
                class="ajax-form" data-reset="0" data-modal="#editModal">

                @csrf
                @method('PUT')

                <div class="modal-body row">

                    <div class="form-group col-6">
                        <label>Name</label>
                        <input type="text" name="name" id="edit_unit_name" class="form-control">
                    </div>

                    <div class="form-group col-6">
                        <label>Short Name</label>
                        <input type="text" name="short_name" id="edit_short_name" class="form-control">
                    </div>

                    <div class="form-group col-6">
                        <label>Unit Type</label>
                        <input type="text" name="unit_type" id="edit_unit_type" class="form-control">
                    </div>

                    <div class="form-group col-6">
                        <label>Status</label>
                        <select name="status" id="edit_unit_status" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
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
    $(document).on('click', '.editUnitBtn', function() {

        let id = $(this).data('id');
        let name = $(this).data('name');
        let short_name = $(this).data('short_name');
        let unit_type = $(this).data('unit_type');
        let status = $(this).data('status');

        let form = $('#EditForm');

        form.attr('action', '/inventory-management/units/' + id);

        $('#edit_unit_name').val(name);
        $('#edit_short_name').val(short_name);
        $('#edit_unit_type').val(unit_type);
        $('#edit_unit_status').val(status);

        $('#editModal').modal('show');
    });
</script>


@endsection