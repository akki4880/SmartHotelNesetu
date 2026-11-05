@extends('layouts.master')

@section('title', 'Vendor Master')
@section('mainbreadcrumb', 'Inventory Management')
@section('subbreadcrumb', 'Vendor Master')
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

                        <!-- Vendor Name -->
                        <div class="col-3">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control"
                                value="{{ request('name') }}" placeholder="Vendor Name">
                        </div>

                        <!-- Contact Person -->
                        <div class="col-3">
                            <label>Contact Person</label>
                            <input type="text" name="contact_person" class="form-control"
                                value="{{ request('contact_person') }}" placeholder="Contact Person">
                        </div>

                        <!-- Phone -->
                        <div class="col-3">
                            <label>Phone</label>
                            <input type="text" name="phone" class="form-control"
                                value="{{ request('phone') }}" placeholder="Phone">
                        </div>

                        <!-- Email -->
                        <div class="col-3">
                            <label>Email</label>
                            <input type="text" name="email" class="form-control"
                                value="{{ request('email') }}" placeholder="Email">
                        </div>

                        <!-- GST -->
                        <div class="col-3 mt-2">
                            <label>GST Number</label>
                            <input type="text" name="gst_number" class="form-control"
                                value="{{ request('gst_number') }}" placeholder="GST Number">
                        </div>

                        <!-- PAN -->
                        <div class="col-3 mt-2">
                            <label>PAN Number</label>
                            <input type="text" name="pan_number" class="form-control"
                                value="{{ request('pan_number') }}" placeholder="PAN Number">
                        </div>

                        <!-- Status -->
                        <div class="col-3 mt-2">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">Select Status</option>
                                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="col-3 mt-4">
                            <div class="d-flex pt-2">
                                <a href="{{ url()->current() }}" class="btn btn-secondary mx-2">Reset</a>
                                <button type="submit" class="btn btn-info">Apply</button>
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
                <h3 class="mb-0 fw-bolder">Vendor Master</h3>
                <div class="float-right">
                    @if(auth()->user()->hasPermission('vendor_master', 'create'))
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
                    @if(!$vendors->isEmpty())
                    <table class="table m-0 table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                @if(auth()->user()->hasPermission('vendor_master', 'view') || auth()->user()->hasPermission('vendor_master', 'edit'))
                                <th>Action</th>
                                @endif
                                <th>Sr</th>
                                <th>Vendor</th>
                                <th>Contact Person</th>
                                <th>Phone No</th>
                                <th>Email</th>
                                <th>GST No</th>
                                <th>Pan No</th>

                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vendors as $vendor)
                            <tr>
                                @if(auth()->user()->hasPermission('vendor_master', 'view') || auth()->user()->hasPermission('vendor_master', 'edit'))
                                <td>
                                    <div class="d-flex">
                                        @if(auth()->user()->hasPermission('vendor_master', 'view'))
                                        <button
                                            class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                            data-url="{{ route('vendors.show', $vendor->id) }}" title="View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('vendor_master', 'edit'))
                                        <a href="javascript:void(0);"
                                            class="btn one-btn-outline-success btn-icon btn-sm editVendorBtn btn-loader mx-1"
                                            data-id="{{ $vendor->id }}"
                                            data-name="{{ $vendor->name }}"
                                            data-status="{{ $vendor->status }}"
                                            data-contact_person="{{ $vendor->contact_person }}"
                                            data-phone="{{ $vendor->phone }}"
                                            data-email="{{ $vendor->email }}"
                                            data-address="{{ $vendor->address }}"
                                            data-country="{{ $vendor->country }}"
                                            data-state="{{ $vendor->state }}"
                                            data-city="{{ $vendor->city }}"
                                            data-pincode="{{ $vendor->pincode }}"
                                            data-gst_number="{{ $vendor->gst_number }}"
                                            data-pan_number="{{ $vendor->pan_number }}"
                                            data-bank_name="{{ $vendor->bank_name }}"
                                            data-account_number="{{ $vendor->account_number }}"
                                            data-ifsc_code="{{ $vendor->ifsc_code }}"
                                            title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>

                                        <div class="custom-control custom-switch mt-1 ml-1">
                                            <input type="checkbox"
                                                class="custom-control-input statusToggle globalStatusToggle"
                                                id="switch{{ $vendor->id }}"
                                                data-id="{{ $vendor->id }}"
                                                data-model="InventoryVendor"
                                                {{ $vendor->status ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="switch{{ $vendor->id }}"></label>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                                @endif
                                <td>{{ $vendors->firstItem() + $loop->index }}</td>
                                <td>{{ $vendor->name }}</td>
                                <td>{{ $vendor->contact_person}}</td>
                                <td>{{ $vendor->phone }}</td>
                                <td>{{ $vendor->email}}</td>
                                <td>{{ $vendor->gst_number}}</td>
                                <td>{{ $vendor->pan_number}}</td>


                                <td>
                                    @if($vendor->status)
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
                        {{ $vendors->links() }}
                    </div>
                    @else
                    No Inventory vendors found
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
                    Create Inventory Vendor
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="CreateForm" action="{{ route('vendors.store') }}" method="POST"
                class="ajax-form" data-reset="1" data-modal="#createModal" novalidate>

                @csrf

                <div class="modal-body row">

                    <div class="row g-3 p-3">

                        {{-- ================= HOTEL ================= --}}
                        <div class="col-12">
                            <h6 class="text-danger fw-bold">Vendor Information</h6>
                            <hr>
                        </div>

                        <div class="col-md-4">
                            <label>Vendor Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Contact Person</label>
                            <input type="text" name="contact_person" class="form-control">
                        </div>

                        <div class="col-md-4 mt-2">
                            <label>Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>

                        <div class="col-md-4 mt-2">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>

                        {{-- ================= LOCATION ================= --}}
                        <div class="col-12 mt-3">
                            <h6 class="text-danger fw-bold">Location</h6>
                            <hr>
                        </div>

                        <div class="col-md-12 mb-2">
                            <label>Address</label>
                            <textarea name="address" class="form-control"></textarea>
                        </div>
                        {{-- Country --}}
                        <div class="col-md-3 mb-3">
                            <label>Country</label>
                            <select name="country" class="select2 form-control country-dropdown" data-target="#add_state" onchange="loadStatesByCountry(this)">
                                <option value="">Select Country</option>
                                @foreach($countries as $country)
                                <option value="{{ $country->id }}" {{ isset($hotel) && $hotel->country == $country->id ? 'selected' : '' }}>
                                    {{ $country->name }}
                                </option>
                                @endforeach
                            </select>
                            <small class="text-danger error-text" data-error="country"></small>
                        </div>

                        {{-- State --}}
                        <div class="col-md-3 mb-3">
                            <label>State</label>
                            <select name="state" id="add_state" class="select2 form-control state-dropdown">
                                <option value="">Select State</option>
                            </select>
                            <small class="text-danger error-text" data-error="state"></small>
                        </div>

                        {{-- City --}}
                        <div class="col-md-3 mb-3">
                            <label>City</label>
                            <input type="text"
                                name="city"
                                class="form-control"
                                value="{{ $hotel->city ?? '' }}">
                            <small class="text-danger error-text" data-error="city"></small>
                        </div>
                        <div class="col-md-3">
                            <label>Pincode</label>
                            <input type="text" name="pincode" class="form-control">
                        </div>
                        {{-- ================= BUSINESS / BILLING ================= --}}
                        <div class="col-12 mt-3">
                            <h6 class="text-danger fw-bold">Business / Billing Details</h6>
                            <hr>
                        </div>

                        <div class="col-md-3 mb-2">
                            <label>GST Number</label>
                            <input type="text" name="gst_number" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>PAN Number</label>
                            <input type="text" name="pan_number" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>Bank Name</label>
                            <input type="text" name="bank_name" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>Account Number</label>
                            <input type="text" name="account_number" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>IFSC Code</label>
                            <input type="text" name="ifsc_code" class="form-control">
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
                <h4 class="modal-title"> Edit Inventory Vendor </h4> <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true"><i class="fal fa-times"></i></span> </button>
            </div>
            <form id="EditForm" method="POST" class="ajax-form" data-reset="0" data-modal="#editModal">
                @csrf @method('PUT')
                <div class="modal-body row">
                    <div class="row g-3 p-3"> {{-- ================= Vendor Info ================= --}}
                        <div class="col-12">
                            <h6 class="text-success fw-bold">Vendor Information</h6>
                            <hr>
                        </div>
                        <div class="col-md-4"> <label>Vendor Name</label> <input type="text" name="name" id="edit_name" class="form-control" required> </div>
                        <div class="col-md-4"> <label>Status</label> <select name="status" id="edit_status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select> </div>
                        <div class="col-md-4">
                            <label>Contact Person</label> <input type="text" name="contact_person" id="edit_contact_person" class="form-control">
                        </div>
                        <div class="col-md-4 mt-2">
                            <label>Phone</label> <input type="text" name="phone" id="edit_phone" class="form-control">
                        </div>
                        <div class="col-md-4 mt-2">
                            <label>Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control">
                        </div>
                        {{-- ================= Location ================= --}}
                        <div class="col-12 mt-3">
                            <h6 class="text-success fw-bold">Location</h6>
                            <hr>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label>Address</label>
                            <textarea name="address" id="edit_address" class="form-control"></textarea>
                        </div>
                        {{-- Country --}}
                        <div class="col-md-3 mb-3"> <label>Country</label>
                            <select name="country" id="edit_country" class="select2 form-control country-dropdown" data-target="#edit_state" onchange="loadStatesByCountry(this)">
                                <option value="">Select Country</option> @foreach($countries as $country) <option value="{{ $country->id }}" {{ isset($hotel) && $hotel->country == $country->id ? 'selected' : '' }}> {{ $country->name }} </option>
                                @endforeach
                            </select> <small class="text-danger error-text" data-error="country"></small>
                        </div>
                        {{-- State --}}
                        <div class="col-md-3 mb-3">
                            <label>State</label>
                            <select name="state" id="edit_state" class="select2 form-control state-dropdown">
                                <option value="">Select State</option>
                            </select>
                            <small class="text-danger error-text" data-error="state"></small>
                        </div>
                        <div class="col-md-3 mb-3"> <label>City</label> <input type="text" name="city" id="edit_city" class="form-control"> </div>
                        <div class="col-md-3"> <label>Pincode</label> <input type="text" name="pincode" id="edit_pincode" class="form-control"> </div> {{-- ================= Billing ================= --}}
                        <div class="col-12 mt-3">
                            <h6 class="text-success fw-bold">Business / Billing Details</h6>
                            <hr>
                        </div>
                        <div class="col-md-3"> <label>GST Number</label> <input type="text" name="gst_number" id="edit_gst_number" class="form-control"> </div>
                        <div class="col-md-3"> <label>PAN Number</label> <input type="text" name="pan_number" id="edit_pan_number" class="form-control"> </div>
                        <div class="col-md-3"> <label>Bank Name</label> <input type="text" name="bank_name" id="edit_bank_name" class="form-control"> </div>
                        <div class="col-md-3"> <label>Account Number</label> <input type="text" name="account_number" id="edit_account_number" class="form-control"> </div>
                        <div class="col-md-3 mt-2"> <label>IFSC Code</label> <input type="text" name="ifsc_code" id="edit_ifsc_code" class="form-control"> </div>
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
    $(document).on('click', '.editVendorBtn', function() {
        let data = $(this).data(); // 🔥 get all data at once 
        let form = $('#EditForm');
        let modal = $('#editModal');
        form.attr('action', '/inventory-management/vendors/' + data.id);
        $('#edit_name').val(data.name);
        $('#edit_status').val(data.status);
        $('#edit_contact_person').val(data.contact_person);
        $('#edit_phone').val(data.phone);
        $('#edit_email').val(data.email);
        $('#edit_address').val(data.address);
        modal.find('#edit_country').val(data.country).trigger('change');
        $('#edit_city').val(data.city);
        $('#edit_pincode').val(data.pincode);
        $('#edit_gst_number').val(data.gst_number);
        $('#edit_pan_number').val(data.pan_number);
        $('#edit_bank_name').val(data.bank_name);
        $('#edit_account_number').val(data.account_number);
        $('#edit_ifsc_code').val(data.ifsc_code);
        console.log(data.state);
        setTimeout(() => {
            modal.find('#edit_state').val(data.state).trigger('change');
        }, 1000);
        $('#editModal').modal('show');
    });
</script>




@endsection