@extends('layouts.master')

@section('title', 'Admins')
@section('mainbreadcrumb', 'Admins')

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
                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" value="{{ request('name') }}"
                                    placeholder="Name">
                            </div>
                        </div>
                        <!-- Email -->
                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="text" name="email" class="form-control" value="{{ request('email') }}"
                                    placeholder="Email">
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
                <h3 class="mb-0 fw-bolder">Admins</h3>
                <div class="float-right">
                    @if(auth()->user()->hasPermission('admins', 'create'))
                    <button class="btn btn-danger btn-sm btn-loader" data-toggle="modal" data-target="#createModal">
                        <i class="fa-solid fa-plus"></i> Create
                    </button>
                    @endif
                    <button id="filterBtn" class="btn btn-info btn-sm" onclick="toggleFilter()">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="frame-wrap p-0 border-0 m-0" id="table-container">
                    @if($admins->count() > 0)
                    <table class="table m-0 table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                @if(auth()->user()->hasPermission('admins', 'view') || auth()->user()->hasPermission('admins', 'edit') || auth()->user()->hasPermission('admins', 'changepassword') || auth()->user()->hasPermission('admins', 'delete'))
                                <th style="width:90px;">Action</th>
                                @endif
                                <th>Sr</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Permissions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($admins as $index => $admin)
                            <tr>

                                {{-- Action --}}
                                @if(auth()->user()->hasPermission('admins', 'view') || auth()->user()->hasPermission('admins', 'edit') || auth()->user()->hasPermission('admins', 'changepassword') || auth()->user()->hasPermission('admins', 'delete'))
                                <td>
                                    <div class="d-flex align-items-center border-0">
                                        @if(auth()->user()->hasPermission('admins', 'view'))
                                        <button
                                            class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                            data-url="{{ route('admins.show', $admin->id) }}" title="View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('admins', 'edit'))
                                        <a href="javascript:void(0);"
                                            class="btn one-btn-outline-success btn-icon btn-sm editBtn btn-loader mx-1"
                                            data-id="{{ $admin->id }}" data-name="{{ $admin->name }}"
                                            data-email="{{ $admin->email }}"
                                            data-permissions='@json($admin->permissions ?? [])'
                                            data-status="{{ $admin->status }}" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        @endif

                                        @if(auth()->user()->hasPermission('admins', 'changepassword'))
                                        <a href="javascript:void(0);"
                                            class="btn btn-outline-warning btn-icon btn-sm editPasswordBtn"
                                            data-id="{{ $admin->id }}" data-name="{{ $admin->name }}"
                                            title="Change Password">
                                            <i class="fa-solid fa-key"></i>
                                        </a>
                                        @endif

                                        {{-- Delete --}}
                                        @if(auth()->user()->hasPermission('admins', 'delete'))
                                        <a href="javascript:void(0);"
                                            class="btn one-btn-outline-danger btn-icon btn-sm deleteAdminBtn mx-1"
                                            data-id="{{ $admin->id }}" data-name="{{ $admin->name }}" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                                @endif

                                {{-- Sr --}}
                                <td>{{ $index + 1 }}</td>

                                {{-- Name --}}
                                <td>{{ $admin->name }}</td>

                                {{-- Email --}}
                                <td>{{ $admin->email }}</td>


                                {{-- Permissions --}}
                                <td>
                                    @if(!empty($admin->permissions))
                                    @foreach($admin->permissions as $module => $actions)
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
                    <div class="mt-3">
                        {{ $admins->withQueryString()->links() }}
                    </div>

                    @else
                    <p class="text-muted">No admins found.</p>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Create Admin Modal --}}
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">

            {{-- Header --}}
            <div class="modal-header bg-danger text-white">
                <h4 class="modal-title">
                    Create Admin
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span><i class="fal fa-times"></i></span>
                </button>
            </div>

            {{-- Body --}}
            <div class="modal-body">
                <form id="CreateForm">
                    @csrf

                    <div class="row">

                        {{-- Name --}}
                        <div class="form-group col-md-6">
                            <label>Name </label>
                            <input type="text" name="name" class="form-control" placeholder="Enter name" required>
                            <small class="text-danger error-text" data-error="name"></small>
                        </div>

                        {{-- Email --}}
                        <div class="form-group col-md-6">
                            <label>Email </label>
                            <input type="email" name="email" class="form-control" placeholder="Enter email" required>
                            <small class="text-danger error-text" data-error="email"></small>
                        </div>

                        {{-- Hidden Role --}}
                        <input type="hidden" name="user_role_id" value="1">

                        {{-- Password --}}
                        <div class="form-group col-md-6">
                            <label>Password </label>
                            <div class="input-group">
                                <input type="password" name="password" class="form-control" id="password" required>
                                <div class="input-group-append">
                                    <span class="input-group-text toggle-password" data-target="password">
                                        <i class="fa fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                            <small class="text-danger error-text" data-error="password"></small>
                        </div>

                        {{-- Confirm Password --}}
                        <div class="form-group col-md-6">
                            <label>Confirm Password </label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" class="form-control"
                                    id="password_confirmation" required>
                                <div class="input-group-append">
                                    <span class="input-group-text toggle-password" data-target="password_confirmation">
                                        <i class="fa fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Divider --}}
                        <div class="col-12">
                            <hr>
                        </div>

                        {{-- Permissions --}}
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
                                                    <input type="checkbox" class="custom-control-input"
                                                        id="perm_{{ $permission->id }}_{{ $action }}"
                                                        name="permissions[{{ $permission->module_key }}][]"
                                                        value="{{ $action }}"
                                                        >
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
                </form>
            </div>

            {{-- Footer --}}
            <div class="modal-footer border-top">
                <button type="reset" class="btn btn-secondary" id="clearaddBtn">
                    Clear
                </button>
                <button type="button" class="btn btn-danger" id="saveBtn">
                    Save
                </button>
            </div>

        </div>
    </div>
</div>


{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title">Edit Admin</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>

            <div class="modal-body">
                <form id="EditForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="admin_id" id="editAdminId">

                    <div class="row">
                        {{-- Name --}}
                        <div class="form-group col-6">
                            <label>Name</label>
                            <input type="text" name="name" id="editName" class="form-control" required>
                            <small class="text-danger error-text" data-error="name"></small>
                        </div>

                        {{-- Email --}}
                        <div class="form-group col-6">
                            <label>Email</label>
                            <input type="email" name="email" id="editEmail" class="form-control" required>
                            <small class="text-danger error-text" data-error="email"></small>
                        </div>

                        {{-- Permissions --}}
                        <div class="form-group col-md-12">
                            <label class="fw-bold">
                                <i class="fa fa-shield-alt mr-1"></i> Permissions
                            </label>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0">
                                    <thead class="bg-primary text-white">
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
                                                        class="custom-control-input edit-perm-checkbox"
                                                        id="edit_perm_{{ $permission->module_key }}_{{ $action }}"
                                                        data-module="{{ $permission->module_key }}"
                                                        value="{{ $action }}"
                                                        {{ in_array($action, $permission->actions ?? []) ? '' : 'disabled' }}>
                                                    <label class="custom-control-label"
                                                        for="edit_perm_{{ $permission->module_key }}_{{ $action }}"></label>
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
                </form>
            </div>

            <div class="modal-footer border-top">
                <button type="reset" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="updateBtn">Update</button>
            </div>
        </div>
    </div>
</div>
<!-- deleteModal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header one-bg-danger text-white">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span><i class="fal fa-times"></i></span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteAdminName"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn one-btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>
<!-- editPasswordModal -->
<div class="modal fade" id="editPasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">Change Password</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <form id="editPasswordForm">
                @csrf
                <input type="hidden" name="admin_id" id="password_admin_id">

                <div class="modal-body">
                    <p class="mb-2">
                        Super Admin: <strong id="password_admin_name"></strong>
                    </p>

                    <div class="form-group">
                        <label>New Password</label>
                        <div class="input-group">
                            <input type="password" name="password" class="form-control" id="password" required>
                            <div class="input-group-append">
                                <span class="input-group-text toggle-password" data-target="password">
                                    <i class="fa fa-eye"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" class="form-control"
                                id="password_confirmation" required>
                            <div class="input-group-append">
                                <span class="input-group-text toggle-password" data-target="password_confirmation">
                                    <i class="fa fa-eye"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Close
                    </button>
                    <button type="submit" class="btn btn-warning text-white" id="updatepasswordbtn">
                        Update Password
                    </button>

                </div>
            </form>

        </div>
    </div>
</div>
@include('layouts.common-view')


<script>
    $('.editBtn').click(function() {
        let adminId = $(this).data('id');
        let name = $(this).data('name');
        let email = $(this).data('email');
        // let hotel = $(this).data('hotel');
        let permissions = $(this).data('permissions');

        $('#editAdminId').val(adminId);
        $('#editName').val(name);
        $('#editEmail').val(email);
        // $('#editHotel').val(hotel);

        // Clear all checkboxes first
        $('.edit-perm-checkbox').prop('checked', false);

        // Tick checkboxes based on permissions JSON
        Object.keys(permissions).forEach(module => {
            permissions[module].forEach(action => {
                $('#edit_perm_' + module + '_' + action).prop('checked', true);
            });
        });

        $('#editModal').modal('show');
    });

    $('#updateBtn').click(function() {
        const button = this;
        showButtonLoader(button);
        let adminId = $('#editAdminId').val();
        let formData = new FormData(document.getElementById('EditForm'));

        for (let key of formData.keys()) {
            if (key.startsWith('permissions[')) formData.delete(key);
        }

        // Gather selected permissions dynamically
        $('.edit-perm-checkbox:checked').each(function() {
            let module = $(this).data('module');
            let action = $(this).val();
            formData.append(`permissions[${module}][]`, action);
        });

        // Clear previous errors
        $('.error-text').text('');

        $.ajax({
            url: '/admins/' + adminId,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                resetButtonLoader(button);

                if (res.status) {
                    $('#editModal').modal('hide');
                    toastr.success(res.message);
                    location.reload();
                }
            },
            error: function(xhr) {
                resetButtonLoader(button);

                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, value) {
                        $(`[data-error="${key}"]`).text(value[0]);
                    });
                } else {
                    toastr.error('Something went wrong. Please try again.');
                }
            }
        });
    });

    let adminIdToDelete = null;

    $('.deleteAdminBtn').click(function() {
        adminIdToDelete = $(this).data('id');
        let name = $(this).data('name');
        $('#deleteAdminName').text(name);
        $('#deleteModal').modal('show');
    });

    $('#confirmDeleteBtn').click(function() {
        const button = this;
        showButtonLoader(button);
        if (!adminIdToDelete) return;

        $.ajax({
            url: '/admins/' + adminIdToDelete, // make sure route exists
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                resetButtonLoader(button);
                if (res.status) {
                    $('#deleteModal').modal('hide');
                    toastr.success(res.message); // or use toastr
                    location.reload(); // refresh table
                }
            },
            error: function(xhr) {
                resetButtonLoader(button);
                toastr.error('Failed to delete admin.');
            }
        });
    });


    $('#saveBtn').click(function() {
        const button = this;
        showButtonLoader(button);
        let form = document.getElementById('CreateForm');
        let formData = new FormData(form);

        // Clear previous errors
        $('.error-text').text('');

        $.ajax({
            url: "{{ route('admins.store') }}", // your store route
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,

            success: function(res) {
                resetButtonLoader(button);

                if (res.status) {
                    $('#createModal').modal('hide');
                    form.reset();
                    toastr.success(res.message); // or use toastr
                    location.reload(); // reload to show new admin
                }
            },

            error: function(xhr) {
                resetButtonLoader(button);

                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, value) {
                        $(`[data-error="${key}"]`).text(value[0]);
                    });
                } else {
                    toastr.error('Something went wrong. Please try again.');
                }
            }
        });
    });

    $(document).on('click', '.editPasswordBtn', function() {
        let adminId = $(this).data('id');
        let adminName = $(this).data('name');

        $('#password_admin_id').val(adminId);
        $('#password_admin_name').text(adminName);

        $('#editPasswordForm')[0].reset();
        $('#editPasswordModal').modal('show');
    });

    $('#editPasswordForm').on('submit', function(e) {
        e.preventDefault();
        const button = $('#updatepasswordbtn')[0];
        showButtonLoader(button);

        let formData = new FormData(this);

        $.ajax({
            url: "{{ route('users.updatePassword') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,

            success: function(res) {
                resetButtonLoader(button);

                if (res.status) {
                    $('#editPasswordModal').modal('hide');
                    toastr.success(res.message);
                }
            },

            error: function(xhr) {
                resetButtonLoader(button);

                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    Object.values(errors).forEach(err => {
                        toastr.error(err[0]);
                    });
                } else {
                    toastr.error('Server error occurred');
                }
            }
        });
    });
</script>

@endsection