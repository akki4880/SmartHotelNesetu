@extends('layouts.master')

@section('title', 'Developers')
@section('mainbreadcrumb', 'Developers')

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
                <h3 class="mb-0 fw-bolder">Developers</h3>
                <div class="float-right">
                    @if(auth()->user()->hasPermission('developers', 'create'))
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
                                @if(
                                auth()->user()->hasPermission('developers', 'view') ||
                                auth()->user()->hasPermission('developers', 'edit') ||
                                auth()->user()->hasPermission('developers', 'delete') ||
                                auth()->user()->hasPermission('developers', 'changepassword')
                                )
                                <th style="width:90px;">Action</th>@endif
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
                                @if(
                                auth()->user()->hasPermission('developers', 'view') ||
                                auth()->user()->hasPermission('developers', 'edit') ||
                                auth()->user()->hasPermission('developers', 'delete') ||
                                auth()->user()->hasPermission('developers', 'changepassword')
                                )
                                <td>
                                    <div class="d-flex align-items-center border-0">
                                        @if(auth()->user()->hasPermission('developers', 'view'))
                                        <button class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                            data-url="{{ route('developers.show', $admin->id) }}" title="View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('developers', 'edit'))
                                        <a href="javascript:void(0);"
                                            class="btn btn-outline-success btn-icon btn-sm editBtn btn-loader mx-1"
                                            data-id="{{ $admin->id }}" data-name="{{ $admin->name }}"
                                            data-email="{{ $admin->email }}" 
                                            data-permissions='@json($admin->permissions ?? [])'
                                            data-status="{{ $admin->status }}" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        @endif
                                        @if(auth()->user()->hasPermission('developers', 'changepassword')) 

                                        <a href="javascript:void(0);"
                                            class="btn btn-outline-warning btn-icon btn-sm btn-loader editPasswordBtn"
                                            data-id="{{ $admin->id }}" data-name="{{ $admin->name }}"
                                            title="Change Password">
                                            <i class="fa-solid fa-key"></i>
                                        </a>
                                        @endif
                                        @if(auth()->user()->hasPermission('developers', 'delete'))
                                        {{-- Delete --}}
                                        <a href="javascript:void(0);"
                                            class="btn one-btn-outline-danger btn-icon btn-sm btn-loader deleteAdminBtn mx-1"
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
                    <p class="text-muted">No Developers found.</p>
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
                <form id="CreateForm" class="ajax-form" action="{{ route('developers.store') }}" method="POST"
                    data-modal="#createModal" data-reset="1">
                    @csrf

                    <input type="hidden" name="user_role_id" value="2">

                    <div class="row">

                        {{-- Name --}}
                        <div class="form-group col-md-6">
                            <label>Name <span class="text-danger">*</span></label>

                            <input type="text" name="name" class="form-control" placeholder="Enter Name"
                                data-rules="required|min:3|max:100">
                        </div>

                        {{-- Email --}}
                        <div class="form-group col-md-6">
                            <label>Email <span class="text-danger">*</span> </label>

                            <input type="email" name="email" class="form-control" placeholder="Enter Email"
                                data-rules="required|email">
                        </div>

                        {{-- Password --}}
                        <div class="form-group col-md-6">
                            <label> Password <span class="text-danger">*</span></label>

                            <div class="input-group">
                                <input type="password" name="password" class="form-control" id="password"
                                    data-rules="required|min:8">

                                <div class="input-group-append">
                                    <span class="input-group-text toggle-password" data-target="password">
                                        <i class="fa fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Confirm Password --}}
                        <div class="form-group col-md-6">
                            <label> Confirm Password <span class="text-danger">*</span>
                            </label>

                            <div class="input-group">
                                <input type="password" name="password_confirmation" class="form-control"
                                    id="password_confirmation" data-rules="required|same:password">

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

                            <label class="font-weight-bold"> <i class="fa fa-shield-alt mr-1"></i> Permissions </label>

                            @php
                            $allActions = collect($permissions)
                            ->pluck('actions')
                            ->flatten()
                            ->unique()
                            ->values()
                            ->toArray();
                            @endphp

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <th width="200">Module</th>

                                            @foreach($allActions as $action)
                                            <th class="text-center">
                                                {{ ucfirst($action) }}
                                            </th>
                                            @endforeach
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach($permissions as $permission)
                                        <tr>
                                            <td><strong>{{ $permission->module_name }}</strong></td>
                                            @foreach($allActions as $action)
                                            <td class="text-center">
                                                @if(in_array($action, $permission->actions ?? []))
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input"
                                                        id="perm_{{ $permission->id }}_{{ $action }}"
                                                        name="permissions[{{ $permission->module_key }}][]"
                                                        value="{{ $action }}">

                                                    <label class="custom-control-label"
                                                        for="perm_{{ $permission->id }}_{{ $action }}">
                                                    </label>
                                                </div>
                                                @endif
                                            </td>
                                            @endforeach
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="modal-footer border-top">
                        <button type="reset" class="btn btn-secondary">Clear</button>
                        <button type="submit" class="btn btn-danger">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-success text-white">
                <h4 class="modal-title">Edit Admin</h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span><i class="fal fa-times"></i></span>
                </button>
            </div>

            <form id="EditForm" class="ajax-form" method="POST" data-modal="#editModal">

                @csrf
                @method('PUT')

                <input type="hidden" name="admin_id" id="editAdminId">

                <div class="modal-body">

                    <div class="row">

                        <div class="form-group col-md-4">
                            <label>Name</label>
                            <input type="text" name="name" id="editName" class="form-control" required minlength="3"
                                maxlength="100">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Email</label>
                            <input type="email" name="email" id="editEmail" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <hr>
                        </div>

                        <div class="form-group col-12">

                            <label class="font-weight-bold">
                                <i class="fa fa-shield-alt mr-1"></i>
                                Permissions
                            </label>

                            <div class="table-responsive">

                                <table class="table table-bordered table-sm mb-0">

                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <th>Module</th>

                                            @foreach($allActions as $action)
                                            <th class="text-center">
                                                {{ ucfirst($action) }}
                                            </th>
                                            @endforeach
                                        </tr>
                                    </thead>

                                    <tbody>

                                        @foreach($permissions as $permission)

                                        <tr>

                                            <td>
                                                <strong>
                                                    {{ $permission->module_name }}
                                                </strong>
                                            </td>

                                            @foreach($allActions as $action)

                                            <td class="text-center">

                                                @if(in_array($action, $permission->actions ?? []))

                                                <div class="custom-control custom-checkbox">

                                                    <input type="checkbox"
                                                        class="custom-control-input edit-perm-checkbox"
                                                        id="edit_perm_{{ $permission->module_key }}_{{ $action }}"
                                                        name="permissions[{{ $permission->module_key }}][]"
                                                        data-module="{{ $permission->module_key }}"
                                                        value="{{ $action }}">

                                                    <label class="custom-control-label"
                                                        for="edit_perm_{{ $permission->module_key }}_{{ $action }}">
                                                    </label>

                                                </div>

                                                @endif

                                            </td>

                                            @endforeach

                                        </tr>

                                        @endforeach

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Close
                    </button>

                    <button type="submit" class="btn btn-success">
                        Update
                    </button>

                </div>

            </form>

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
                <button type="button" class="close" data-dismiss="modal">
                    &times;
                </button>
            </div>

            <form id="editPasswordForm" class="ajax-form" method="POST" action="{{ route('users.updatePassword') }}"
                data-modal="#editPasswordModal" data-reset="1">

                @csrf

                <input type="hidden" name="admin_id" id="password_admin_id">

                <div class="modal-body">

                    <p class="mb-3">
                        Super Admin:
                        <strong id="password_admin_name"></strong>
                    </p>

                    <div class="form-group">
                        <label>New Password</label>

                        <div class="input-group">

                            <input type="password" name="password" id="password" class="form-control" required
                                minlength="6">

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

                            <input type="password" name="password_confirmation" id="password_confirmation"
                                class="form-control" required minlength="6" data-rules="same:password">

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

                    <button type="submit" class="btn btn-warning text-white">
                        Update Password
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>
@include('layouts.common-view')

<script>
    $(document).on('click', '.editBtn', function () {
        const adminId = $(this).data('id');
        const permissions = $(this).data('permissions') || {};

        $('#editAdminId').val(adminId);
        $('#editName').val($(this).data('name'));
        $('#editEmail').val($(this).data('email'));
        $('#EditForm').attr('action', `/developers/${adminId}`);
        $('.edit-perm-checkbox').prop('checked', false);

        Object.keys(permissions).forEach(module => {
            permissions[module].forEach(action => {
                $(`#edit_perm_${module}_${action}`).prop('checked', true);
            });
        });

        $('#editModal').modal('show');

    });

    let adminIdToDelete = null;
    $(document).on('click', '.deleteAdminBtn', function () {
        adminIdToDelete = $(this).data('id');
        let name = $(this).data('name');
        $('#deleteAdminName').text(name);
        $('#deleteModal').modal('show');
    });

    $(document).on('click', '#confirmDeleteBtn', function () {
        const button = this;
        showButtonLoader(button);
        if (!adminIdToDelete) return;

        $.ajax({
            url: '/developers/' + adminIdToDelete, // make sure route exists
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function (res) {
                resetButtonLoader(button);
                if (res.status) {
                    $('#deleteModal').modal('hide');
                    toastr.success(res.message); // or use toastr
                    $('#table-container').load(location.href + ' #table-container > *');
                }
            },
            error: function (xhr) {
                resetButtonLoader(button);
                toastr.error('Failed to delete admin.');
            }
        });
    });

    $(document).on('click', '.editPasswordBtn', function () {
        $('#password_admin_id').val($(this).data('id'));
        $('#password_admin_name').text($(this).data('name'));
        $('#editPasswordForm')[0].reset();
        $('#editPasswordModal').modal('show');
    });

</script>

@endsection