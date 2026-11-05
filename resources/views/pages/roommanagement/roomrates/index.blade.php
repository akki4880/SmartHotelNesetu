@extends('layouts.master')

@section('title', 'Room Rates')
@section('mainbreadcrumb', 'Room Management')
@section('subbreadcrumb', 'Room Rates')

@section('content')
<!-- Filter Start -->
@php
// Get all request parameters except _token or page
$filters = request()->except(['_token', 'page']);

// Check if any filter has a non-empty value
$hasFilter = collect($filters)->filter(fn($value) => $value !== null && $value !== '')->isNotEmpty();
@endphp

<div class="row" style="{{ $hasFilter ? '' : 'display:none;' }}" id="filterRow">
    <div class="col-sm-12">
        <div class="card mb-g">
            <div class="card-header bg-info d-flex justify-content-between align-items-center">
                <h3 class="mb-0 fw-bolder text-white">
                    <i class="fa-solid fa-filter"></i> Filter
                </h3>
            </div>

            <div class="card-body">
                <form method="GET" action="{{ url()->current() }}">
                    <div class="row">

                        <!-- Room Type -->
                        <div class="col-2">
                            <div class="form-group">
                                <label>Room Type</label>
                                <select name="room_type_id" class="form-control">
                                    <option value="">All</option>
                                    @foreach($roomTypes as $roomType)
                                    <option value="{{ $roomType->id }}"
                                        {{ request('room_type_id') == $roomType->id ? 'selected' : '' }}>
                                        {{ $roomType->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Base Price Min -->
                        <div class="col-2">
                            <label>Base Price Min (₹)</label>
                            <input type="number" name="base_min" class="form-control" value="{{ request('base_min') }}"
                                placeholder="Min">
                        </div>

                        <!-- Base Price Max -->
                        <div class="col-2">
                            <label>Base Price Max (₹)</label>
                            <input type="number" name="base_max" class="form-control" value="{{ request('base_max') }}"
                                placeholder="Max">
                        </div>

                        <!-- Weekend Price Min -->
                        <div class="col-2">
                            <label>Weekend Price Min (₹)</label>
                            <input type="number" name="weekend_min" class="form-control"
                                value="{{ request('weekend_min') }}" placeholder="Min">
                        </div>

                        <!-- Weekend Price Max -->
                        <div class="col-2">
                            <label>Weekend Price Max (₹)</label>
                            <input type="number" name="weekend_max" class="form-control"
                                value="{{ request('weekend_max') }}" placeholder="Max">
                        </div>


                        <!-- Status -->
                        <div class="col-2">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="col-3 d-flex align-items-end mt-2">
                            <div>
                                <a href="{{ url()->current() }}" class="btn btn-secondary">
                                    Reset
                                </a>
                            </div>
                            <button type="submit" class="btn btn-info mx-2">
                                Apply
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Filter end -->
<!-- Room Rate Row Start -->
<div class="row">
    <div class="col-sm-12">
        <div class="card mb-g">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0 fw-bolder">Room Rates</h3>
                <div class="float-right">
                    @if(auth()->user()->hasPermission('room_rate_setup', 'create'))
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
                    @if($roomRates->count() > 0)
                    <table class="table table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                @if(auth()->user()->hasPermission('room_rate_setup', 'view') || auth()->user()->hasPermission('room_rate_setup', 'edit'))
                                <th width="120">Action</th>
                                @endif
                                <th>Room Type</th>
                                <th>Base Price</th>
                                <th>Weekend Price</th>
                                <th>Extra Guest Price</th>
                                <th>Base Guests</th>
                                <th>Max Extra Guests</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($roomRates as $rate)
                            <tr>
                                 @if(auth()->user()->hasPermission('room_rate_setup', 'view') || auth()->user()->hasPermission('room_rate_setup', 'edit'))
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if(auth()->user()->hasPermission('room_rate_setup', 'view'))
                                        <button
                                            class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                            data-url="{{ route('room-rates.show', $rate->id) }}" title="View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('room_rate_setup', 'edit'))
                                        <a class="btn one-btn-outline-success btn-icon btn-sm editRateBtn btn-loader mx-1"
                                            data-id="{{ $rate->id }}" data-room-type-id="{{ $rate->roomType->id  }}"
                                            data-base-price="{{ $rate->base_price }}"
                                            data-weekend-price="{{ $rate->weekend_price }}"
                                            data-extra-guest-price="{{ $rate->extra_guest_price }}"
                                            data-base-guest-count="{{ $rate->base_guest_count }}"
                                            data-max-extra-guests="{{ $rate->max_extra_guests }}"
                                            data-status="{{ $rate->status }}">
                                            <i class="fa fa-edit"></i>
                                        </a>

                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input statusToggle globalStatusToggle"
                                                id="rateSwitch{{ $rate->id }}" data-id="{{ $rate->id }}" data-model="RoomRate"
                                                {{ $rate->status ? 'checked' : '' }}>

                                            <label class="custom-control-label" for="rateSwitch{{ $rate->id }}"></label>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                                @endif

                                <td>{{ $rate->roomType->name ?? '-' }}</td>

                                <td>
                                    ₹{{ number_format($rate->base_price, 2) }}
                                </td>

                                <td>
                                    {{ $rate->weekend_price ? '₹'.number_format($rate->weekend_price, 2) : '-' }}
                                </td>

                                <td>
                                    {{ $rate->extra_guest_price ? '₹'.number_format($rate->extra_guest_price, 2) : '-' }}
                                </td>

                                <td>{{ $rate->base_guest_count }}</td>

                                <td>{{ $rate->max_extra_guests }}</td>

                                <td>
                                    <span class="badge {{ $rate->status ? 'badge-success' : 'badge-danger' }}">
                                        {{ $rate->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $roomRates->withQueryString()->links() }}
                    </div>

                    @else
                    <p class="text-muted">No room rates found.</p>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
<!-- Room Rate Row End -->
<!-- Create MOdal -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h4 class="modal-title">
                    Create Room Rate
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="CreateForm" action="{{ route('room-rates.store') }}" method="POST" class="ajax-form" data-reset="1" data-modal="#createModal" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label>Room Type <span class="text-danger">*</span></label>
                            <select name="room_type_id" id="room_type_id" class="select2 form-control">
                                <option value="">Select Room Type</option>
                                @foreach($roomTypes as $roomType)
                                <option value="{{ $roomType->id }}">{{ $roomType->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger error-text" data-error="room_type_id"></small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Base Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="base_price" id="base_price" class="form-control">
                            <small class="text-danger error-text" data-error="base_price"></small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Weekend Price</label>
                            <input type="number" step="0.01" name="weekend_price" id="weekend_price"
                                class="form-control">
                            <small class="text-danger error-text" data-error="weekend_price"></small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Extra Guest Price</label>
                            <input type="number" step="0.01" name="extra_guest_price" id="extra_guest_price"
                                class="form-control">
                            <small class="text-danger error-text" data-error="extra_guest_price"></small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Base Guest Count <span class="text-danger">*</span></label>
                            <input type="number" name="base_guest_count" id="base_guest_count" class="form-control">
                            <small class="text-danger error-text" data-error="base_guest_count"></small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Max Extra Guests <span class="text-danger">*</span></label>
                            <input type="number" name="max_extra_guests" id="max_extra_guests" class="form-control">
                            <small class="text-danger error-text" data-error="max_extra_guests"></small>
                        </div>

                        <div class="col-md-6">
                            <label>Status</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <small class="text-danger error-text" data-error="status"></small>
                        </div>

                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="reset" class="btn btn-secondary" id="clearaddBtn">Clear</button>
                    <button type="submit" class="btn btn-danger" id="saveRoomRateBtn">Save</button>
                </div>
            </form>

        </div>
    </div>
</div>
<!-- editModal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title">
                    Edit Room Rate
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

                        <input type="hidden" name="id" id="room_rate_id">

                        <!-- Room Type -->
                        <div class="col-md-6 mb-3">
                            <label>Room Type <span class="text-danger">*</span></label>
                            <select name="room_type_id" id="room_type_id" class="select2 form-control">
                                <option value="">Select Room Type</option>
                                @foreach($roomTypes as $roomType)
                                <option value="{{ $roomType->id }}">{{ $roomType->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger error-text" data-error="room_type_id"></small>
                        </div>

                        <!-- Base Price -->
                        <div class="col-md-6 mb-3">
                            <label>Base Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="base_price" id="base_price" class="form-control">
                            <small class="text-danger error-text" data-error="base_price"></small>
                        </div>

                        <!-- Weekend Price -->
                        <div class="col-md-6 mb-3">
                            <label>Weekend Price</label>
                            <input type="number" step="0.01" name="weekend_price" id="weekend_price"
                                class="form-control">
                            <small class="text-danger error-text" data-error="weekend_price"></small>
                        </div>

                        <!-- Extra Guest Price -->
                        <div class="col-md-6 mb-3">
                            <label>Extra Guest Price</label>
                            <input type="number" step="0.01" name="extra_guest_price" id="extra_guest_price"
                                class="form-control">
                            <small class="text-danger error-text" data-error="extra_guest_price"></small>
                        </div>

                        <!-- Base Guest Count -->
                        <div class="col-md-6 mb-3">
                            <label>Base Guest Count <span class="text-danger">*</span></label>
                            <input type="number" name="base_guest_count" id="base_guest_count" class="form-control">
                            <small class="text-danger error-text" data-error="base_guest_count"></small>
                        </div>

                        <!-- Max Extra Guests -->
                        <div class="col-md-6 mb-3">
                            <label>Max Extra Guests <span class="text-danger">*</span></label>
                            <input type="number" name="max_extra_guests" id="max_extra_guests" class="form-control">
                            <small class="text-danger error-text" data-error="max_extra_guests"></small>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <label>Status</label>
                            <select name="status" id="status" class="form-control" required>
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
<!-- Js -->
<script>
    // roomRateEditDetailModal
    $(document).on('click', '.editRateBtn', function() {

        let el = this; // native <a> element
        let form = $('#EditForm');
        console.log(el.dataset);

        form.find('[id="room_rate_id"]').val(el.dataset.id);

        form.find('[name="room_type_id"]')
            .val(el.dataset.roomTypeId)
            .trigger('change');

        form.find('[name="base_price"]').val(el.dataset.basePrice);
        form.find('[name="weekend_price"]').val(el.dataset.weekendPrice);
        form.find('[name="extra_guest_price"]').val(el.dataset.extraGuestPrice);
        form.find('[name="base_guest_count"]').val(el.dataset.baseGuestCount);
        console.log(el.dataset.maxExtraGuests);
        // 🔥 THIS ONE NOW WORKS
        form.find('[name="max_extra_guests"]').val(el.dataset.maxExtraGuests);

        form.find('[name="status"]').val(el.dataset.status);
        $('#EditForm').attr('action', `/room-management/room-rates/${el.dataset.id}`);
        $('#editModal').modal('show');
    });
</script>
@endsection