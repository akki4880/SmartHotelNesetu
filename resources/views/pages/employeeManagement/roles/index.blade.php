@extends('layouts.master')

@section('title', 'Roles')
@section('mainbreadcrumb', 'Employee Management')
@section('subbreadcrumb', 'Roles')
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
                        <div class="col-3">
                            <!-- Role -->
                            <div class="form-group">
                                <label>Role</label>
                                <div class="input-group">
                                    <input type="text" name="role_name" class="form-control"
                                        value="{{ request('role_name') }}" placeholder="Role">

                                </div>
                                <small class="text-danger error-text" data-error="role_name"></small>
                            </div>
                        </div>
                        <div class="col-3">
                            <!-- Description -->
                            <div class="form-group">
                                <label>Description</label>
                                <div class="input-group">
                                    <input type="text" name="description" class="form-control"
                                        value="{{ request('description') }}" placeholder="Description">

                                </div>
                                <small class="text-danger error-text" data-error="description"></small>
                            </div>
                        </div>
                        <div class="col-4">
                            <!-- Buttons -->
                            <div class="d-flex">
                                <div class="mx-2">
                                    <a href="{{ url()->current() }}" class="btn  btn-secondary mt-4">
                                        Reset
                                    </a>
                                </div>
                                <div class="">
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
                <h3 class="mb-0 fw-bolder">Roles</h3>
                <div class="float-right">
                    <button class="btn btn-danger btn-sm btn-loader" data-toggle="modal" data-target="#createModal"><i
                            class="fa-solid fa-plus"></i>
                        Create</button>
                    <button class="btn btn-info btn-sm" onclick="toggleFilter()">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>

                </div>
            </div>

            <div class="card-body">
                <div class="frame-wrap p-0 border-0 m-0" id="table-container">
                    @if($userRoles->count() > 0)
                    <table class="table m-0 table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Action</th>
                                <th>Sr</th>
                                <th>Role</th>
                                <th>Description</th>
                                <th>Permissions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($userRoles as $index => $role)
                            <tr>
                                <td style="width: 78px;">
                                    <!-- <a href="javascript:void(0);" class="btn one-btn-outline-danger btn-icon btn-sm" title="Delete"> <i class="fa-solid fa-trash"></i> </a> -->
                                    <button data-toggle="tooltip"
                                        class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                        data-url="{{ route('roles.show', $role->id) }}" title="View">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <a href="javascript:void(0);" data-toggle="tooltip"
                                        class="btn one-btn-outline-success btn-icon btn-sm editRoleBtn btn-loader"
                                        data-id="{{ $role->id }}" data-name="{{ $role->role_name }}"
                                        data-description="{{ $role->description }}" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                </td>
                                <td>{{ ($userRoles->currentPage() - 1) * $userRoles->perPage() + $index + 1 }}</td>
                                <td>{{ $role->role_name }}</td>
                                <td><span data-toggle="tooltip" title="{{ $role->description }}"> {{ shortText($role->description) }}</span></td>
                                <td>
                                    @if(!empty($role->permissions))
                                    @foreach($role->permissions as $module => $actions)
                                    @foreach($actions as $action)
                                    <span class="badge badge-primary mb-1">
                                        {{ ucfirst(str_replace('_',' ', $module)) }}: {{ ucfirst($action) }}
                                    </span>
                                    @endforeach
                                    @endforeach
                                    @else
                                    <span class="badge badge-light">None</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-end mt-3">
                        {{ $userRoles->links() }}
                    </div>

                    @else
                    <p>No Roles found.</p>
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
                    Create Role
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="CreateForm" action="{{ route('roles.store') }}" method="POST" class="ajax-form" data-reset="1" data-modal="#createModal" novalidate>
                @csrf
                <div class="modal-body">

                    <div class="row">
                        <!-- Role Name -->
                        <div class="form-group col-6">
                            <label>Role Name</label>
                            <div class="input-group">
                                <input type="text" name="role_name" class="form-control" required>

                            </div>
                            <small class="text-danger error-text" data-error="role_name"></small>
                        </div>

                        <!-- Description -->
                        <div class="form-group col-6">
                            <label>Description</label>
                            <div class="input-group">
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                            <small class="text-danger error-text" data-error="description"></small>
                        </div>

                        <div class="form-group col-md-12">
                            <label class="fw-bold">
                                <i class="fa fa-shield-alt mr-1"></i> Permissions
                            </label>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0">
                                    <thead class="bg-primary text-white">
                                        @php
                                        $allActions = collect($permissions)
                                        ->pluck('actions')
                                        ->flatten()
                                        ->unique()
                                        ->values()
                                        ->toArray();
                                        @endphp
                                        <tr>
                                            <th>Module</th>
                                            @foreach($allActions as $action)
                                            <th>{{ ucfirst($action) }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($permissions as $permission)
                                        <tr>
                                            <td>
                                                <strong>{{ $permission->module_name }}</strong>
                                            </td>

                                            @foreach($allActions as $action)
                                            <td>
                                                @if(in_array($action, $permission->actions ?? []))
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox"
                                                        class="custom-control-input"
                                                        id="perm_{{ $permission->id }}_{{ $action }}"
                                                        name="permissions[{{ $permission->module_key }}][]"
                                                        value="{{ $action }}">
                                                    <label class="custom-control-label"
                                                        for="perm_{{ $permission->id }}_{{ $action }}"></label>
                                                </div>
                                                @endif
                                            </td>
                                            @endforeach
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <small class="text-danger error-text" data-error="permissions"></small>
                        </div>
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
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title">
                    Edit Role
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="EditForm" method="POST" class="ajax-form" data-reset="0" data-modal="#editModal" novalidate>
                @csrf

                @method('PUT')

                <div class="modal-body">
                    <input type="hidden" id="edit_role_id" name="role_id">

                    <div class="row">
                        <!-- Role Name -->
                        <div class="form-group col-6">
                            <label>Role Name</label>
                            <div class="input-group">
                                <input type="text" name="role_name" id="edit_role_name" class="form-control" required>

                            </div>
                            <small class="text-danger error-text" data-error="role_name"></small>
                        </div>

                        <!-- Description -->
                        <div class="form-group col-6">
                            <label>Description</label>
                            <div class="input-group">
                                <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>

                            </div>
                            <small class="text-danger error-text" data-error="description"></small>
                        </div>

                        <div class="form-group col-md-12">
                            <label class="fw-bold">
                                <i class="fa fa-shield-alt mr-1"></i> Permissions
                            </label>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0">
                                    <thead class="bg-primary text-white">
                                        @php
                                        $allActions = collect($permissions)
                                        ->pluck('actions')
                                        ->flatten()
                                        ->unique()
                                        ->values()
                                        ->toArray();
                                        @endphp
                                        <tr>
                                            <th>Module</th>
                                            @foreach($allActions as $action)
                                            <th>{{ ucfirst($action) }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody id="editPermissionsTable">
                                        @foreach($permissions as $permission)
                                        <tr>
                                            <td>
                                                <strong>{{ $permission->module_name }}</strong>
                                            </td>

                                            @foreach($allActions as $action)
                                            <td>
                                                @if(in_array($action, $permission->actions ?? []))
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox"
                                                        class="custom-control-input edit-perm-checkbox"
                                                        id="edit_perm_{{ $permission->id }}_{{ $action }}"
                                                        name="permissions[{{ $permission->module_key }}][]"
                                                        value="{{ $action }}">
                                                    <label class="custom-control-label"
                                                        for="edit_perm_{{ $permission->id }}_{{ $action }}"></label>
                                                </div>
                                                @endif
                                            </td>
                                            @endforeach
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <small class="text-danger error-text" data-error="permissions"></small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top">
                    <button type="reset" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success" id="updateRoleBtn">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('layouts.common-view')

<script>
    // When edit button is clicked
    $(document).on('click', '.editRoleBtn', function() {
        let roleId = $(this).data('id');
        let roleName = $(this).data('name');
        let description = $(this).data('description');
        let form = $('#EditForm');

        // change action dynamically
        form.attr('action', '/employee-management/roles/' + roleId);

        // Fill the edit form with basic data
        $('#edit_role_id').val(roleId);
        $('#edit_role_name').val(roleName);
        $('#edit_description').val(description);

        // Clear all permission checkboxes first
        $('.edit-perm-checkbox').prop('checked', false);

        // Fetch role permissions via AJAX
        $.ajax({
            url: '/employee-management/roles/' + roleId + '/edit',
            type: 'GET',
            headers: {
                'Accept': 'application/json',
            },
            success: function(roleData) {
                console.log('Received role data:', roleData);
                if (roleData.permissions) {
                    // roleData.permissions should be an object like: { module: ['action1', 'action2'] }
                    $.each(roleData.permissions, function(module, actions) {
                        $.each(actions, function(index, action) {
                            // Find and check the checkbox
                            let selector = 'input[name="permissions[' + module + '][]"][value="' + action + '"]';
                            console.log('Checking selector:', selector);
                            $(selector).prop('checked', true);
                        });
                    });
                }
            },
            error: function(xhr, status, error) {
                console.log('Error fetching role data:', error);
            }
        });

        // Show the edit modal
        $('#editModal').modal('show');
    });
</script>


@endsection