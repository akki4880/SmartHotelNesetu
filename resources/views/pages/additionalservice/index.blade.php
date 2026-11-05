@extends('layouts.master')

@section('title', 'Additional Service')
@section('mainbreadcrumb', 'Additional Service')

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
                <form method="GET" action="{{ url()->current() }}" class="mb-3">
                    <div class="row">

                        <!-- Service Name -->
                        <div class="col-md-3">
                            <label>Service Name</label>
                            <input type="text" name="service_name" class="form-control"
                                value="{{ request('service_name') }}" placeholder="Search service">
                        </div>

                        <!-- Price Min -->
                        <div class="col-md-2">
                            <label>Min Price</label>
                            <input type="number" name="min_price" class="form-control"
                                value="{{ request('min_price') }}" placeholder="Min">
                        </div>

                        <!-- Price Max -->
                        <div class="col-md-2">
                            <label>Max Price</label>
                            <input type="number" name="max_price" class="form-control"
                                value="{{ request('max_price') }}" placeholder="Max">
                        </div>

                        <!-- Status -->
                        <div class="col-md-2">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">All</option>
                                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="col-md-3 d-flex align-items-end">
                            <a href="{{ url()->current() }}" class="btn btn-secondary mx-2">Reset</a>
                            <button class="btn btn-info ">Filter</button>
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
                <h3 class="mb-0 fw-bolder">Additional Service</h3>
                <div class="float-right">
                    @if(auth()->user()->hasPermission('additional_service', 'create'))
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
                    @if($additionalServices->count() > 0)
                    <table class="table m-0 table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                @if(auth()->user()->hasPermission('additional_service', 'edit') || auth()->user()->hasPermission('additional_service', 'view'))
                                <th>Action</th>@endif
                                <th>Sr</th>
                                <th>Service Name</th>
                                <th>Service Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($additionalServices as $index => $additionalService)
                            <tr>
                                @if(auth()->user()->hasPermission('additional_service', 'edit') || auth()->user()->hasPermission('additional_service', 'view'))
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if(auth()->user()->hasPermission('additional_service', 'edit'))
                                        <button
                                            class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                            data-url="{{ route('additional_services.show', $additionalService->id) }}" title="View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('additional_service', 'edit'))
                                        <a href="javascript:void(0);"
                                            class="btn one-btn-outline-success btn-icon btn-sm editBtn btn-loader mx-1"
                                            data-id="{{ $additionalService->id }}"
                                            data-service_name="{{ $additionalService->service_name }}"
                                            data-cost="{{ $additionalService->cost }}"
                                            data-status="{{ $additionalService->status }}" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>

                                        <div class="custom-control custom-switch ">
                                            <input type="checkbox" class="custom-control-input StatusToggle globalStatusToggle"
                                                id="Switch{{ $additionalService->id }}"
                                                data-id="{{ $additionalService->id }}" data-model="AdditionalService"
                                                {{ $additionalService->status ? 'checked' : '' }}>

                                            <label class="custom-control-label"
                                                for="Switch{{ $additionalService->id }}"></label>

                                        </div>
                                        @endif
                                    </div>
                                </td>
                                @endif
                                <td>{{ ($additionalServices->currentPage() - 1) * $additionalServices->perPage() + $index + 1 }}
                                </td>
                                <td>{{ $additionalService->service_name }}</td>
                                <td>{{ $additionalService->cost }}</td>
                                <td>
                                    @if($additionalService->status)
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
                        {{ $additionalServices->links() }}
                    </div>
                    @else
                    <p>No Additional Service found.</p>
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
                <h4 class="modal-title">Create Additional Service</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="CreateForm" action="{{ route('additional_services.store') }}" method="POST" class="ajax-form" data-reset="1" data-modal="#createModal" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-4">
                            <label>Service Name</label>
                            <input type="text" name="service_name" class="form-control" required>
                            <small class="text-danger error-text" data-error="service_name"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Service Price</label>
                            <input type="number" name="cost" class="form-control" step="0.01" required>
                            <small class="text-danger error-text" data-error="cost"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Status</label>
                            <select name="status" class="form-control" required>
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <small class="text-danger error-text" data-error="status"></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="reset" class="btn btn-secondary" id="clearAddBtn">Clear</button>
                    <button type="submit" class="btn btn-danger" id="saveBtn">Save</button>
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
                <h4 class="modal-title">Edit Additional Service</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="EditForm" method="POST" class="ajax-form" data-reset="0" data-modal="#editModal" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <!-- Hidden ID -->
                    <input type="hidden" name="id" id="edit_service_id">

                    <div class="row">
                        <div class="form-group col-4">
                            <label>Service Name</label>
                            <input type="text" name="service_name" id="edit_service_name"
                                class="form-control" required>
                            <small class="text-danger error-text" data-error="service_name"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Service Price</label>
                            <input type="number" name="cost" id="edit_cost"
                                class="form-control" step="0.01" required>
                            <small class="text-danger error-text" data-error="cost"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Status</label>
                            <select name="status" id="edit_status"
                                class="form-control" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <small class="text-danger error-text" data-error="status"></small>
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
    // When edit button is clicked
    $(document).on('click', '.editBtn', function() {
        let serviceId = $(this).data('id');
        let serviceName = $(this).data('service_name');
        let cost = $(this).data('cost');
        let status = $(this).data('status');

        // Fill the edit form fields
        $('#edit_service_id').val(serviceId);
        $('#edit_service_name').val(serviceName);
        $('#edit_cost').val(cost);
        $('#edit_status').val(status);

        $('#EditForm').attr('action', `/additional_services/${serviceId}`);
        // Show the edit modal
        $('#editModal').modal('show');
    });
</script>

@endsection