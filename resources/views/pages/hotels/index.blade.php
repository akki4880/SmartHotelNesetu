@extends('layouts.master')

@section('title', 'Hotels')
@section('mainbreadcrumb', 'Hotels')

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
                <form method="GET" action="{{ url()->current() }}" id="filterForm">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <!-- Hotel -->
                            <div class="form-group">
                                <label>Hotel Name</label>
                                <div class="input-group">
                                    <input type="text" name="hotel" class="form-control" value="{{ request('hotel') }}"
                                        placeholder="Hotel Name">

                                </div>
                                <small class="text-danger error-text" data-error="hotel"></small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <!-- Admin -->
                            <div class="form-group">
                                <label>Hotel Admin</label>
                                <div class="input-group">
                                    <input type="text" name="admin" class="form-control" value="{{ request('admin') }}"
                                        placeholder="Hotel Admin">

                                </div>
                                <small class="text-danger error-text" data-error="admin"></small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <!-- Plan -->
                            <div class="form-group">
                                <label>Plan</label>
                                <div class="input-group">
                                    <select name="plan" class="form-control">
                                        <option value="">All</option>
                                        @foreach($plans as $plan)
                                        <option value="{{$plan->id}}" {{ request('plan') == '1' ? 'selected' : '' }}>
                                            {{$plan->name}}
                                        </option>
                                        @endforeach
                                    </select>

                                </div>
                                <small class="text-danger error-text" data-error="plan"></small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <!-- Created By -->
                            <div class="form-group">
                                <label>Created By</label>
                                <div class="input-group">
                                    <input type="text" name="created_by" class="form-control"
                                        value="{{ request('created_by') }}" placeholder="Created By">

                                </div>
                                <small class="text-danger error-text" data-error="created_by"></small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label>Subscription Period</label>
                                <div class="row">
                                    <div class="col-6 text-center">
                                        <input type="date" name="subscription_from" class="form-control"
                                            value="{{ request('subscription_from') }}">
                                        <label class="small text-muted">From</label>
                                    </div>
                                    <div class="col-6 text-center">
                                        <input type="date" name="subscription_to" class="form-control"
                                            value="{{ request('subscription_to') }}">
                                        <label class="small text-muted">To</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label>Subscription End Month</label>
                                <input type="month" name="subscription_end_month" class="form-control"
                                    value="{{ request('subscription_end_month') }}">
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <!-- Status -->
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                                        Active
                                    </option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                                        Inactive
                                    </option>
                                </select>
                            </div>
                        </div>
                        <!-- Buttons -->
                        <div class="col-md-3">
                            <div class="d-flex mt-4">
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
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0 fw-bolder">Hotels</h3>
                <div class="float-right">
                    @if(auth()->user()->hasPermission('hotels', 'create'))
                    <button class="btn btn-danger btn-sm btn-loader" data-toggle="modal" data-target="#createModal">
                        <i class="fa-solid fa-plus"></i> Create
                    </button>
                    @endif
                    <button id="filterBtn" class="btn btn-info btn-sm" onclick="toggleFilter()"></i>
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="frame-wrap p-0 border-0 m-0" id="table-container">
                    @if($hotels->count() > 0)
                    <table class="table table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                @if(auth()->user()->hasPermission('hotels', 'view') || auth()->user()->hasPermission('hotels', 'edit') || auth()->user()->hasPermission('hotels', 'changepassword') || auth()->user()->hasPermission('hotels', 'changesubscription'))
                                <th>Action</th>
                                @endif
                                <th>Sr</th>
                                <th>Hotel Name</th>
                                <th>Hotel Admin</th>
                                <th>Plan</th>
                                <th>Subscription</th>
                                <th>Expiry</th>
                                <th>Created By</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($hotels as $index => $hotel)
                            <tr>
                                @if(auth()->user()->hasPermission('hotels', 'view') || auth()->user()->hasPermission('hotels', 'edit') || auth()->user()->hasPermission('hotels', 'changepassword') || auth()->user()->hasPermission('hotels', 'changesubscription'))
                                <td>
                                    <div class="d-flex align-items-center border-0" >
                                        @if(auth()->user()->hasPermission('hotels', 'view'))
                                        <button
                                            class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                            data-url="{{ route('hotels.show', $hotel->id) }}" title="View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('hotels', 'changesubscription'))
                                        <a class="btn btn-sm one-btn-outline-danger  btn-icon editSubscriptionBtn btn-loader mx-1"
                                            data-id="{{ $hotel->subscription->id }}"
                                            data-start="{{ $hotel->subscription->start_date }}"
                                            data-end="{{ $hotel->subscription->end_date }}">
                                            <i class="fa fa-calendar"></i>
                                        </a>
                                        @endif
                                        @if(auth()->user()->hasPermission('hotels', 'changepassword'))
                                        <a href="javascript:void(0);"
                                            class="btn btn-outline-warning btn-icon btn-sm editPasswordBtn "
                                            data-id="{{ $hotel->admin->id }}" data-name="{{ $hotel->admin->name }}"
                                            title="Change Password">
                                            <i class="fa-solid fa-key"></i>
                                        </a>
                                        @endif
                                        @if(auth()->user()->hasPermission('hotels', 'edit'))
                                        <a class="btn one-btn-outline-success btn-icon btn-sm  mx-1 editHotelDetails"
                                            data-id="{{ $hotel->id }}" data-hotel_name="{{ $hotel->hotel_name }}"
                                            data-phone="{{ $hotel->phone }}" data-city="{{ $hotel->city }}"
                                            data-plan_id="{{ $hotel->subscription->plan_id ?? '' }}"
                                            data-admin_name="{{ $hotel->admin->name ?? '' }}"
                                            data-admin_email="{{ $hotel->admin->email ?? '' }}">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        @endif
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input hotelStatusToggle"
                                                id="hotelSwitch{{ $hotel->id }}" data-id="{{ $hotel->id }}"
                                                {{ $hotel->status ? 'checked' : '' }}>

                                            <label class="custom-control-label"
                                                for="hotelSwitch{{ $hotel->id }}"></label>
                                        </div>
                                    </div>
                                </td>
                                @endif

                                <td>{{ ($hotels->currentPage() - 1) * $hotels->perPage() + $index + 1 }}</td>

                                <td>
                                    <strong>{{ $hotel->hotel_name }}</strong><br>
                                    <small>{{ $hotel->city }}</small>
                                </td>

                                <td>
                                    {{ $hotel->admin->name ?? '-' }}<br>
                                    <small>{{ $hotel->admin->email ?? '-' }}</small>
                                </td>

                                <td>
                                    {{ $hotel->subscription->plan->name ?? '-' }}<br>
                                    <small>₹{{ $hotel->subscription->plan->price ?? 0 }}</small>
                                </td>

                                <td>
                                    {{ formatDate($hotel->subscription->start_date) }} →
                                    {{ formatDate($hotel->subscription->end_date) }}
                                </td>
                                <td>{{ $hotel->subscription->expiry_text }}</td>
                                <td>
                                    {{ $hotel->creator->name ?? '-' }}
                                </td>

                                <td>
                                    <span
                                        class="badge hotel-status-badge {{ $hotel->status ? 'badge-success' : 'badge-danger' }}">
                                        {{ $hotel->status ? 'Active' : 'Inactive' }}
                                    </span>

                                    @if(!$hotel->status && $hotel->blocked_reason)
                                    <div class="small text-danger mt-1">
                                        {{ $hotel->blocked_reason }}
                                    </div>
                                    @endif
                                </td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-3">
                        {{ $hotels->withQueryString()->links() }}
                    </div>

                    @else
                    <p class="text-muted">No hotels found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<!-- editSubscriptionModal -->
<div class="modal fade" id="editSubscriptionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header one-bg-danger text-white">
                <h5 class="modal-title">
                    Edit Subscription
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <i class="fal fa-times"></i>
                </button>
            </div>

            <div class="modal-body">
                <form id="EditSubscriptionForm">
                    @csrf

                    <input type="hidden" name="subscription_id" id="subscriptionId">

                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" id="subStartDate" class="form-control">
                        <small class="text-danger error-text" data-error="start_date"></small>
                    </div>

                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" name="end_date" id="subEndDate" class="form-control">
                        <small class="text-danger error-text" data-error="end_date"></small>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Close
                </button>
                <button type="button" class="btn one-btn-danger" id="updateSubscriptionBtn">
                    Update
                </button>
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
                        Hotel Admin: <strong id="password_admin_name"></strong>
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
<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">

            {{-- Header --}}
            <div class="modal-header bg-danger text-white">
                <h4 class="modal-title">
                    Create Hotel
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

                        {{-- ================= HOTEL DETAILS ================= --}}
                        <div class="col-12 mb-2">
                            <h6 class="text-danger fw-bold">
                                <i class="fa fa-hotel mr-1"></i> Hotel Details
                            </h6>
                            <hr class="mt-1 mb-2">
                        </div>

                        {{-- Hotel Name --}}
                        <div class="form-group col-md-4">
                            <label>Hotel Name <span class="text-danger">*</span></label>
                            <input type="text" name="hotel_name" class="form-control">
                            <small class="text-danger error-text" data-error="hotel_name"></small>
                        </div>

                        {{-- Phone --}}
                        <div class="form-group col-md-4">
                            <label>Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control">
                            <small class="text-danger error-text" data-error="phone"></small>
                        </div>

                        {{-- City --}}
                        <div class="form-group col-md-4">
                            <label>City</label>
                            <input type="text" name="city" class="form-control">
                            <small class="text-danger error-text" data-error="city"></small>
                        </div>


                        {{-- ================= SUBSCRIPTION ================= --}}
                        <div class="col-12 mt-2 mb-2">
                            <h6 class="text-danger fw-bold">
                                <i class="fa fa-crown mr-1"></i> Subscription Plan
                            </h6>
                            <hr class="mt-1 mb-2">
                        </div>

                        {{-- Plan --}}
                        <div class="form-group col-md-4">
                            <label>Plan <span class="text-danger">*</span></label>
                            <select name="plan_id" class="form-control">
                                <option value="">Select Plan</option>
                                @foreach($plans as $plan)
                                <option value="{{ $plan->id }}">
                                    {{ $plan->name }} (₹{{ $plan->price }})
                                </option>
                                @endforeach
                            </select>
                            <small class="text-danger error-text" data-error="plan_id"></small>
                        </div>


                        {{-- ================= HOTEL ADMIN ================= --}}
                        <div class="col-12 mt-2 mb-2">
                            <h6 class="text-danger fw-bold">
                                <i class="fa fa-user-shield mr-1"></i> Hotel Admin
                            </h6>
                            <hr class="mt-1 mb-2">
                        </div>

                        {{-- Admin Name --}}
                        <div class="form-group col-md-4">
                            <label>Admin Name <span class="text-danger">*</span></label>
                            <input type="text" name="admin_name" class="form-control">
                            <small class="text-danger error-text" data-error="admin_name"></small>
                        </div>

                        {{-- Admin Email --}}
                        <div class="form-group col-md-4">
                            <label>Admin Email <span class="text-danger">*</span></label>
                            <input type="email" name="admin_email" class="form-control">
                            <small class="text-danger error-text" data-error="admin_email"></small>
                        </div>

                        {{-- Password --}}
                        <div class="form-group col-md-4">
                            <label>Password</label>
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
                        <div class="form-group col-md-4">
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
                </form>


            </div>

            {{-- Footer --}}
            <div class="modal-footer border-top">
                <button type="reset" class="btn btn-secondary" id="clearBtn">
                    Clear
                </button>
                <button type="button" class="btn btn-danger" id="saveBtn">
                    Save
                </button>
            </div>

        </div>
    </div>
</div>
<!-- Edit Modal -->
<div class="modal fade" id="editHotelModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">

            {{-- Header --}}
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title">Edit Hotel Details</h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span><i class="fal fa-times"></i></span>
                </button>
            </div>

            {{-- Body --}}
            <div class="modal-body">
                <form id="edithoteldetailForm">
                    @csrf

                    {{-- Hidden hotel id for EDIT --}}
                    <input type="hidden" name="hotel_id" id="hotel_id">

                    <div class="row">

                        {{-- ================= HOTEL DETAILS ================= --}}
                        <div class="col-12 mb-2">
                            <h6 class="text-danger fw-bold">
                                <i class="fa fa-hotel mr-1"></i> Hotel Details
                            </h6>
                            <hr class="mt-1 mb-2">
                        </div>

                        {{-- Hotel Name --}}
                        <div class="form-group col-md-4">
                            <label>Hotel Name <span class="text-danger">*</span></label>
                            <input type="text" name="hotel_name" id="hotel_name" class="form-control">
                            <small class="text-danger error-text" data-error="hotel_name"></small>
                        </div>

                        {{-- Phone --}}
                        <div class="form-group col-md-4">
                            <label>Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="phone" class="form-control">
                            <small class="text-danger error-text" data-error="phone"></small>
                        </div>

                        {{-- City --}}
                        <div class="form-group col-md-4">
                            <label>City</label>
                            <input type="text" name="city" id="city" class="form-control">
                            <small class="text-danger error-text" data-error="city"></small>
                        </div>

                        {{-- ================= SUBSCRIPTION ================= --}}
                        <div class="col-12 mt-2 mb-2">
                            <h6 class="text-danger fw-bold">
                                <i class="fa fa-crown mr-1"></i> Subscription Plan
                            </h6>
                            <hr class="mt-1 mb-2">
                        </div>

                        {{-- Plan --}}
                        <div class="form-group col-md-4">
                            <label>Plan <span class="text-danger">*</span></label>
                            <select name="plan_id" id="plan_id" class="form-control">
                                <option value="">Select Plan</option>
                                @foreach($plans as $plan)
                                <option value="{{ $plan->id }}">
                                    {{ $plan->name }} (₹{{ $plan->price }})
                                </option>
                                @endforeach
                            </select>
                            <small class="text-danger error-text" data-error="plan_id"></small>
                        </div>

                        {{-- ================= HOTEL ADMIN ================= --}}
                        <div class="col-12 mt-2 mb-2">
                            <h6 class="text-danger fw-bold">
                                <i class="fa fa-user-shield mr-1"></i> Hotel Admin
                            </h6>
                            <hr class="mt-1 mb-2">
                        </div>

                        {{-- Admin Name --}}
                        <div class="form-group col-md-4">
                            <label>Admin Name <span class="text-danger">*</span></label>
                            <input type="text" name="admin_name" id="admin_name" class="form-control">
                            <small class="text-danger error-text" data-error="admin_name"></small>
                        </div>

                        {{-- Admin Email --}}
                        <div class="form-group col-md-4">
                            <label>Admin Email <span class="text-danger">*</span></label>
                            <input type="email" name="admin_email" id="admin_email" class="form-control">
                            <small class="text-danger error-text" data-error="admin_email"></small>
                        </div>

                    </div>
                </form>
            </div>

            {{-- Footer --}}
            <div class="modal-footer border-top">
                <button type="reset" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="updateHotelDetail">
                    Update
                </button>
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
<!-- blockReasonModal -->
<div class="modal fade" id="blockReasonModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header one-bg-danger text-white">
                <h5 class="modal-title">Block Hotel</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span><i class="fal fa-times"></i></span>
                </button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="blockHotelId">

                <div class="form-group">
                    <label>Reason for Blocking <span class="text-danger">*</span></label>
                    <textarea id="blockReason" class="form-control" rows="3" placeholder="Enter reason"></textarea>
                    <small class="text-danger" id="blockReasonError"></small>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button class="btn one-btn-danger" id="confirmBlockBtn">
                    Block Hotel
                </button>
            </div>

        </div>
    </div>
</div>
@include('layouts.common-view')
<script>
    $('#clearBtn').click(() => $('#CreateForm')[0].reset());

    $('#saveBtn').click(function() {
        const button = this;
        showButtonLoader(button);

        let form = document.getElementById('CreateForm');
        let formData = new FormData(form);

        // Clear previous errors
        $('.error-text').text('');

        $.ajax({
            url: "{{ route('hotels.store') }}", // Hotel store route
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,

            success: function(res) {
                resetButtonLoader(button);

                if (res.status) {
                    $('#createModal').modal('hide');
                    form.reset();
                    toastr.success(res.message);
                    location.reload(); // refresh hotel list
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

    let pendingCheckbox = null;

    /* Toggle Change */
    $(document).on('change', '.hotelStatusToggle', function() {

        let checkbox = $(this);
        let hotelId = checkbox.data('id');

        // 🔴 If trying to deactivate → show modal
        if (!checkbox.is(':checked')) {
            pendingCheckbox = checkbox;

            // revert toggle till confirmation
            checkbox.prop('checked', true);

            $('#blockHotelId').val(hotelId);
            $('#blockReason').val('');
            $('#blockReasonError').text('');
            $('#blockReasonModal').modal('show');
            return;
        }

        // 🟢 If activating → direct update
        toggleHotelStatus(hotelId, null, checkbox);
    });

    /* Confirm Block */
    $('#confirmBlockBtn').on('click', function() {
        const button = this;
        showButtonLoader(button);
        let hotelId = $('#blockHotelId').val();
        let reason = $('#blockReason').val().trim();

        if (!reason) {
            $('#blockReasonError').text('Blocking reason is required');
            return;
        }
        resetButtonLoader(button);
        $('#blockReasonModal').modal('hide');
        toggleHotelStatus(hotelId, reason, pendingCheckbox);
    });

    /* Common AJAX */
    function toggleHotelStatus(hotelId, reason = null, checkbox = null) {

        $.ajax({
            url: '/hotels/' + hotelId + '/toggle-status',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                reason: reason
            },

            success: function(res) {

                if (!res.status) {
                    toastr.error(res.message ?? 'Failed');
                    return;
                }

                // Sync checkbox
                if (checkbox) {
                    checkbox.prop('checked', res.is_active);
                }

                let row = checkbox.closest('tr');
                let badge = row.find('.hotel-status-badge');
                let td = badge.closest('td');

                if (res.is_active) {
                    // 🔹 ACTIVE
                    badge
                        .removeClass('badge-danger')
                        .addClass('badge-success')
                        .text('Active');

                    // 🔹 REMOVE blocked reason
                    td.find('.text-danger').remove();

                } else {
                    // 🔹 INACTIVE
                    badge
                        .removeClass('badge-success')
                        .addClass('badge-danger')
                        .text('Inactive');

                    // 🔹 ADD / UPDATE blocked reason
                    if (reason) {
                        td.find('.text-danger').remove(); // prevent duplicates

                        badge.after(`
                        <div class="small text-danger mt-1">
                            ${reason}
                        </div>
                    `);
                    }
                }

                toastr.success(res.message ?? 'Status updated');
            },

            error: function() {
                toastr.error('Something went wrong');
            }
        });
    }

    $('.editSubscriptionBtn').click(function() {

        $('#subscriptionId').val($(this).data('id'));
        $('#subStartDate').val($(this).data('start'));
        $('#subEndDate').val($(this).data('end'));

        $('.error-text').text('');

        $('#editSubscriptionModal').modal('show');
    });
    $('#updateSubscriptionBtn').click(function() {

        const button = this;
        showButtonLoader(button);

        let formData = new FormData(document.getElementById('EditSubscriptionForm'));

        $.ajax({
            url: '/subscriptions/update-dates',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,

            success: function(res) {
                resetButtonLoader(button);

                if (res.status) {
                    $('#editSubscriptionModal').modal('hide');
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
                    toastr.error('Something went wrong');
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

    // editHotelModal 
    $(document).on('click', '.editHotelDetails', function() {

        let btn = $(this); // 👈 THIS WAS MISSING

        $('#hotel_name').val(btn.data('hotel_name'));
        $('#phone').val(btn.data('phone'));
        $('#city').val(btn.data('city'));
        $('#plan_id').val(btn.data('plan_id'));
        $('#admin_name').val(btn.data('admin_name'));
        $('#admin_email').val(btn.data('admin_email'));
        $('#hotel_id').val(btn.data('id'));

        $('#editHotelModal').modal('show');
    });

    // Update Edited Hotel Details
    $('#updateHotelDetail').on('click', function() {
        const button = this;
        showButtonLoader(button);
        $('.form-control').removeClass('is-invalid');
        $('.error-text').text('');

        let hotelId = $('#hotel_id').val();
        let formData = $('#edithoteldetailForm').serializeArray();
        formData.push({
            name: '_method',
            value: 'PUT'
        });

        $.ajax({
            url: `/hotels/${hotelId}`,
            type: 'POST',
            data: $.param(formData),
            success: function(response) {
                resetButtonLoader(button);
                $('#editHotelModal').modal('hide');
                toastr.success(response.message ?? 'Hotel Details updated successfully');
                location.reload();
            },
            error: function(xhr) {
                resetButtonLoader(button);
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    toastr.error('Please fix the highlighted errors');
                    $.each(errors, function(key, value) {
                        let input = $('[name="' + key + '"]');
                        input.addClass('is-invalid');
                        $('[data-error="' + key + '"]').text(value[0]);
                    });
                } else {
                    toastr.error(xhr.responseJSON?.message ?? 'Something went wrong.');
                }
            }
        });
    });
</script>
@endsection