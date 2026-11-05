@extends('layouts.master')

@section('title', 'Plans')
@section('mainbreadcrumb', 'Plans')

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
                                <label>Plan Name</label>
                                <input type="text" name="plan_name" class="form-control"
                                    value="{{ request('plan_name') }}" placeholder="Plan Name">
                            </div>
                        </div>

                        <!-- Duration -->
                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label>Duration (Months)</label>
                                <input type="number" name="duration" class="form-control"
                                    value="{{ request('duration') }}" placeholder="Duration">
                            </div>
                        </div>

                        <!-- Price -->
                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label>Price</label>
                                <div class="input-group">
                                    <input type="number" name="price_from" class="form-control"
                                        value="{{ request('price_from') }}" placeholder="Min Price">
                                    <input type="number" name="price_to" class="form-control"
                                        value="{{ request('price_to') }}" placeholder="Max Price">
                                </div>
                            </div>
                        </div>

                        <!-- Included Features -->
                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label>Included Features</label>
                                <input type="text" name="included_features" class="form-control"
                                    value="{{ request('included_features') }}" placeholder="Included Features">
                            </div>
                        </div>

                        <!-- Excluded Features -->
                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label>Excluded Features</label>
                                <input type="text" name="excluded_features" class="form-control"
                                    value="{{ request('excluded_features') }}" placeholder="Excluded Features">
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
                <h3 class="mb-0 fw-bolder">Plans</h3>
                <div class="float-right">
                    @if(auth()->user()->hasPermission('plans', 'create'))
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
                    @if($plans->count() > 0)
                    <table class="table m-0 table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                @if(auth()->user()->hasPermission('plans', 'view') || auth()->user()->hasPermission('plans', 'edit'))
                                <th>Action</th>
                                @endif
                                <th>Sr</th>
                                <th>Plan Name</th>
                                <th>Duration</th>
                                <th>Price</th>
                                <th>Included Features</th>
                                <th>Excluded Features</th>
                                <th>Permissions</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($plans as $index => $plan)
                            <tr>
                                @if(auth()->user()->hasPermission('plans', 'view') || auth()->user()->hasPermission('plans', 'edit'))
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if(auth()->user()->hasPermission('plans', 'view'))
                                        <button
                                            class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                            data-url="{{ route('plans.show', $plan->id) }}" title="View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('plans', 'edit'))
                                        <a href="javascript:void(0);"
                                            class="btn one-btn-outline-success btn-icon btn-sm editPlanBtn btn-loader mx-1"
                                            data-id="{{ $plan->id }}" data-name="{{ $plan->name }}"
                                            data-price="{{ $plan->price }}" data-duration="{{ $plan->duration }}"
                                            data-description="{{ $plan->description }}"
                                            data-included="{{ implode(', ', $plan->included_features ?? []) }}"
                                            data-excluded="{{ implode(', ', $plan->excluded_features ?? []) }}"
                                            data-status="{{ $plan->status }}" data-permissions='@json($plan->permissions)' title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>

                                        <div class="custom-control custom-switch ">
                                            <input type="checkbox" class="custom-control-input globalStatusToggle"
                                                id="planSwitch{{ $plan->id }}" data-id="{{ $plan->id }}" data-model="Plan"
                                                {{ $plan->status ? 'checked' : '' }}>

                                            <label class="custom-control-label" for="planSwitch{{ $plan->id }}"></label>

                                        </div>
                                        @endif
                                    </div>
                                </td>
                                @endif
                                <td>{{ ($plans->currentPage() - 1) * $plans->perPage() + $index + 1 }}</td>
                                <td>{{ $plan->name }}</td>
                                <td>{{ $plan->duration }} days</td>
                                <td>{{ $plan->price }}</td>
                                <td>
                                    @foreach($plan->included_features ?? [] as $feature)
                                    <span class="badge badge-success">{{ $feature }}</span>
                                    @endforeach
                                </td>

                                <td>
                                    @foreach($plan->excluded_features ?? [] as $feature)
                                    <span class="badge badge-danger">{{ $feature }}</span>
                                    @endforeach
                                </td>
                                {{-- Permissions --}}
                                <td>
                                    @if(!empty($plan->permissions))
                                    @foreach($plan->permissions as $module => $actions)
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
                                <td>
                                    @if($plan->status)
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
                        {{ $plans->links() }}
                    </div>
                    @else
                    <p>No plans found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
{{-- Create Modal --}}
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h4 class="modal-title">Create Plan</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="CreateForm" action="{{ route('plans.store') }}" method="POST" class="ajax-form" data-reset="1" data-modal="#createModal" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-4">
                            <label>Plan Name</label>
                            <input type="text" name="name" class="form-control" required>
                            <small class="text-danger error-text" data-error="name"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Price</label>
                            <input type="number" name="price" class="form-control" step="0.01" required>
                            <small class="text-danger error-text" data-error="price"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Duration (days)</label>
                            <input type="number" name="duration" class="form-control" required>
                            <small class="text-danger error-text" data-error="duration"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                            <small class="text-danger error-text" data-error="description"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Included Features (comma separated)</label>
                            <textarea name="included_features" class="form-control" rows="3"></textarea>
                            <small class="text-danger error-text" data-error="included_features"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Excluded Features (comma separated)</label>
                            <textarea name="excluded_features" class="form-control" rows="3"></textarea>
                            <small class="text-danger error-text" data-error="excluded_features"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Status</label>
                            <select name="status" class="form-control" required>
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <small class="text-danger error-text" data-error="status"></small>
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
                    <button type="reset" class="btn btn-secondary" id="clearAddBtn">Clear</button>
                    <button type="submit" class="btn btn-danger" id="savePlanBtn">Save</button>
                </div>
            </form>

        </div>
    </div>
</div>


{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title">Edit Plan</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="EditForm" data-modal="#editModal" method="POST" class="ajax-form" data-reset="0" novalidate>
                @csrf

                @method('PUT')

                <div class="modal-body">

                    <input type="hidden" id="edit_plan_id" name="plan_id">

                    <div class="row">
                        <div class="form-group col-4">
                            <label>Plan Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                            <small class="text-danger error-text" data-error="name"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Price</label>
                            <input type="number" name="price" id="edit_price" class="form-control" step="0.01" required>
                            <small class="text-danger error-text" data-error="price"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Duration (days)</label>
                            <input type="number" name="duration" id="edit_duration" class="form-control" required>
                            <small class="text-danger error-text" data-error="duration"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Description</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                            <small class="text-danger error-text" data-error="description"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Included Features (comma separated)</label>
                            <textarea name="included_features" id="edit_included_features" class="form-control"
                                rows="3"></textarea>
                            <small class="text-danger error-text" data-error="included_features"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Excluded Features (comma separated)</label>
                            <textarea name="excluded_features" id="edit_excluded_features" class="form-control"
                                rows="3"></textarea>
                            <small class="text-danger error-text" data-error="excluded_features"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Status</label>
                            <select name="status" id="edit_status" class="form-control" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <small class="text-danger error-text" data-error="status"></small>
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
                                                    <input type="checkbox" class="custom-control-input edit-perm-checkbox"
                                                        id="edit_perm_{{ $permission->module_key  }}_{{ $action }}"
                                                        name="permissions[{{ $permission->module_key }}][]"
                                                        value="{{ $action }}">
                                                    <label class="custom-control-label"
                                                        for="edit_perm_{{ $permission->module_key  }}_{{ $action }}"></label>
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
                    <button type="submit" class="btn btn-success" id="updatePlanBtn">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@include('layouts.common-view')

<script>
    // Edit Plan button click
    $(document).on('click', '.editPlanBtn', function() {
        let planId = $(this).data('id');

        $('#edit_plan_id').val($(this).data('id'));
        $('#edit_name').val($(this).data('name'));
        $('#edit_price').val($(this).data('price'));
        $('#edit_duration').val($(this).data('duration'));
        $('#edit_description').val($(this).data('description'));
        $('#edit_included_features').val($(this).data('included'));
        $('#edit_excluded_features').val($(this).data('excluded'));
        $('#edit_status').val($(this).data('status'));
        let permissions = $(this).data('permissions') || {};

        $('.edit-perm-checkbox').prop('checked', false);

        // ✅ Set checked based on saved permissions
        Object.keys(permissions).forEach(module => {
            permissions[module].forEach(action => {
                $('#edit_perm_' + module + '_' + action).prop('checked', true);
            });
        });
        $('#EditForm').attr('action', `/plans/${planId}`);

        $('#editModal').modal('show');
    });
</script>

@endsection