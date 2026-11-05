@extends('layouts.master')

@section('title', 'Permissions')
@section('mainbreadcrumb', 'Permissions')

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

                        <!-- Plan Name -->
                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label>Module</label>
                                <input type="text" name="module_name" class="form-control"
                                    value="{{ request('module_name') }}" placeholder="Module Name">
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All</option>
                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="col-md-4">
                            <div class="d-flex mt-4">
                                <a href="{{ url()->current() }}" class="btn btn-secondary mr-2">
                                    Reset
                                </a>
                                <button type="submit" class="btn btn-info">
                                    Apply
                                </button>
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
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0 fw-bolder">Permissions</h3>
                <div class="float-right">
                    @if(auth()->user()->hasPermission('permissions', 'create'))
                    <button class="btn btn-danger btn-sm btn-loader" data-toggle="modal" data-target="#createModal">
                        <i class="fa-solid fa-plus"></i> Create
                    </button>
                    @endif
                    <button id="filterBtn" class="btn btn-info btn-sm" data-toggle="modal" onclick="toggleFilter()"></i>
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="frame-wrap p-0 border-0 m-0" id="table-container">
                    @if($permissions->count() > 0)
                    <table class="table table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                @if(auth()->user()->hasPermission('permissions', 'view') || auth()->user()->hasPermission('permissions', 'edit'))
                                <th width="120">Action</th>
                                @endif
                                <th>Module</th>
                                <th>Actions</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($permissions as $permission)
                            <tr>
                                @if(auth()->user()->hasPermission('permissions', 'view') || auth()->user()->hasPermission('permissions', 'edit'))
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if(auth()->user()->hasPermission('permissions', 'view'))
                                        <button
                                            class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                            data-url="{{ route('permissions.show', $permission->id) }}" title="View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('permissions', 'edit'))
                                        <a class="btn one-btn-outline-success btn-icon btn-sm editBtn btn-loader mx-1"
                                            data-id="{{ $permission->id }}">
                                            <i class="fa fa-edit"></i>
                                        </a>

                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input statusToggle globalStatusToggle"
                                                id="switch{{ $permission->id }}" data-id="{{ $permission->id }}"
                                                data-model="Permission"
                                                {{ $permission->status ? 'checked' : '' }}>
                                            <label class="custom-control-label"
                                                for="switch{{ $permission->id }}"></label>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                                @endif

                                <td>{{ $permission->module_name }}</td>

                                <td>
                                    @foreach($permission->actions as $action)
                                    <span class="badge badge-info">{{ ucfirst($action) }}</span>
                                    @endforeach
                                </td>

                                <td>
                                    <span class="badge {{ $permission->status ? 'badge-success' : 'badge-danger' }}">
                                        {{ $permission->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-3">
                        {{ $permissions->withQueryString()->links() }}
                    </div>

                    @else
                    <p class="text-muted">No permissions found.</p>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Create Modal --}}
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header bg-danger text-white">
                <h4 class="modal-title">Create Permissions</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="CreateForm" action="{{ route('permissions.store') }}" method="POST" class="ajax-form" data-reset="1" data-modal="#createModal" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="row">
                        {{-- Module Name --}}
                        <div class="form-group col-6">
                            <label>Module Name <span class="text-danger">*</span></label>
                            <input type="text" name="module_name" class="form-control"
                                placeholder="e.g. User Role, Invoice, Reports">
                            <small class="text-danger error-text" data-error="module_name"></small>
                        </div>
                        {{-- Status --}}
                        <div class="form-group col-6">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                        <div class="form-group col-6">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input"
                                    name="is_visible_to_hotel_owner" value="1" checked>
                                <span class="custom-control-label">Visible to Hotel Owner</span>
                            </label>
                        </div>

                        <div class="form-group col-6">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input"
                                    name="is_visible_to_super_admin" value="1" checked>
                                <span class="custom-control-label">Visible to Super Admin</span>
                            </label>
                        </div>

                        {{-- Actions --}}
                        <div class="form-group col-12">
                            <label>Allowed Actions</label>

                            <div class="row">
                                @foreach($actions as $action)
                                <div class="col-6">
                                    <label class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" name="actions[]"
                                            value="{{$action->action_name}}">
                                        <span class="custom-control-label">{{$action->action_name}}</span>
                                    </label>
                                </div>
                                @endforeach
                            </div>

                            <small class="text-danger error-text" data-error="actions"></small>
                        </div>


                    </div>

                </div>

                <div class="modal-footer border-top">
                    <button type="reset" class="btn btn-secondary" id="clearaddBtn">Clear</button>
                    <button type="submit" class="btn btn-danger" id="saveBtn">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title">Edit Permission</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="EditForm" method="POST" class="ajax-form" data-reset="0" data-modal="#editModal" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-body">

                    <input type="hidden" name="permission_id" id="edit_permission_id">

                    <div class="row">

                        {{-- Module Name --}}
                        <div class="form-group col-md-6">
                            <label>Module Name <span class="text-danger">*</span></label>
                            <input type="text" name="module_name" id="edit_module_name" class="form-control">
                            <small class="text-danger error-text" data-error="module_name"></small>
                        </div>

                        {{-- Status --}}
                        <div class="form-group col-md-6">
                            <label>Status</label>
                            <select name="status" id="edit_status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <small class="text-danger error-text" data-error="status"></small>
                        </div>

                        <div class="form-group col-md-6">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="edit_is_visible_to_hotel_owner"
                                    name="is_visible_to_hotel_owner" value="1">
                                <span class="custom-control-label">Visible to Hotel Owner</span>
                            </label>
                        </div>

                        <div class="form-group col-md-6">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="edit_is_visible_to_super_admin"
                                    name="is_visible_to_super_admin" value="1">
                                <span class="custom-control-label">Visible to Super Admin</span>
                            </label>
                        </div>

                        {{-- Permissions --}}
                        <div class="form-group col-md-12 mt-2">
                            <label class="fw-bold">Allowed Actions</label>

                            <div class="row">
                                @foreach($actions as $action)
                                <div class="col-6">
                                    <label class="custom-control custom-checkbox edit-action">
                                        <input type="checkbox" class="custom-control-input" name="actions[]" id="edit_{{$action->action_name}}"
                                            value="{{$action->action_name}}">
                                        <span class="custom-control-label">{{$action->action_name}}</span>
                                    </label>
                                </div>
                                @endforeach
                            </div>

                            <small class="text-danger error-text" data-error="actions"></small>
                        </div>

                    </div>

                </div>

                <div class="modal-footer border-top">
                    <button type="reset" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success" id="updateBtn">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@include('layouts.common-view')
<script>
    $(document).on('click', '.editBtn', function() {

        let id = $(this).data('id');

        $.get(`/permissions/${id}/edit`, function(res) {

            $('#edit_permission_id').val(res.id);
            $('#edit_module_name').val(res.module_name);
            $('#edit_status').val(res.status);

            // reset checkboxes
            $('.edit-action').prop('checked', false);

            // tick existing actions
            res.actions.forEach(action => {
                $('#edit_' + action).prop('checked', true);
            });

            // Set is_visible_to_hotel_owner
            $('#edit_is_visible_to_hotel_owner').prop('checked', res.is_visible_to_hotel_owner == 1);
            $('#edit_is_visible_to_super_admin').prop('checked', res.is_visible_to_super_admin == 1);

            $('#EditForm').attr('action', `/permissions/${res.id}`);
            $('#editModal').modal('show');
        });

    });
</script>

@endsection