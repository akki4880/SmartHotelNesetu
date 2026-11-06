@extends('layouts.master')

@section('title', 'Departments')
@section('mainbreadcrumb', 'Employee Management')
@section('subbreadcrumb', 'Departments')
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

                        <!-- Category Name -->
                        <div class="col-4">
                            <div class="form-group">
                                <label>Category Name</label>
                                <input type="text" name="name" class="form-control"
                                    value="{{ request('name') }}" placeholder="Category Name">
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
                        <div class="col-4">
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
                <h3 class="mb-0 fw-bolder">Employee Departments</h3>
                <div class="float-right">
                    @if(auth()->user()->hasPermission('departments', 'create'))
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
                    @if(!$departments->isEmpty())
                    <table class="table m-0 table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                @if(auth()->user()->hasPermission('departments', 'view') || auth()->user()->hasPermission('departments', 'edit'))
                                <th>Action</th>@endif
                                <th>Sr</th>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Description</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($departments as $department)
                            <tr>
                                @if(auth()->user()->hasPermission('departments', 'view') || auth()->user()->hasPermission('departments', 'edit'))
                                <td class="w-auto">
                                    <div class="d-flex">
                                        @if(auth()->user()->hasPermission('departments', 'view'))
                                        <button
                                            class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                            data-url="{{ route('departments.show', $department->id) }}" title="View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('departments', 'edit'))
                                        <a href="javascript:void(0);"
                                            class="btn one-btn-outline-success btn-icon btn-sm editDepartmentBtn btn-loader mx-1"
                                            data-id="{{ $department->id }}" data-name="{{ $department->name }}"
                                            data-description="{{ $department->description }}" data-status="{{ $department->status }}"
                                            title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <div class="custom-control custom-switch mt-1 ml-1">
                                            <input type="checkbox"
                                                class="custom-control-input statusToggle globalStatusToggle"
                                                id="switch{{ $department->id }}" data-id="{{ $department->id }}"
                                                data-model="Department" {{
                                            $department->status ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="switch{{ $department->id }}"></label>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                                @endif
                                <td>{{ $departments->firstItem() + $loop->index }}</td>
                                <td>{{ $department->name }}</td>
                                <td>{{ $department->code }}</td>
                                <td>{{ $department->description ?? '-' }}</td>
                                <td>
                                    @if($department->status)
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
                        {{ $departments->links() }}
                    </div>
                    @else
                    No Employee departments found
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
                    Create Employee Departments
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="CreateForm" action="{{ route('departments.store') }}" method="POST" class="ajax-form"
                data-reset="1" data-modal="#createModal" novalidate>
                @csrf
                <div class="modal-body">

                    <!-- Inventory Name -->
                    <div class="form-group">
                        <label>Department Name</label>
                        <div class="input-group">
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <small class="text-danger error-text" data-error="name"></small>
                    </div>

                    <!-- Inventory Description -->
                    <div class="form-group">
                        <label>Description</label>
                        <div class="input-group">
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <small class="text-danger error-text" data-error="description"></small>
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
                    Edit Employee Departments
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
                            <label>Department Name</label>
                            <div class="input-group">
                                <input type="text" name="name" id="edit_department_name" class="form-control" required>
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
<script>
    // When edit button is clicked
    $(document).on('click', '.editDepartmentBtn', function() {

        let departmentId = $(this).data('id');
        let departmentName = $(this).data('name');
        let description = $(this).data('description');
        let status = $(this).data('status');

        let form = $('#EditForm');

        form.attr('action', '/employee-management/departments/' + departmentId);

        $('#edit_department_name').val(departmentName);
        $('#edit_description').val(description);
        $('#edit_status').val(status);

        $('#editModal').modal('show');
    });
</script>


@endsection