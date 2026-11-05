@extends('layouts.master')

@section('title', 'Seasonal Room Rates')
@section('mainbreadcrumb', 'Room Management')
@section('subbreadcrumb', 'Seasonal Room Rates')

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

                        <!-- Room Type -->
                        <div class="col-md-2">
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

                        <!-- Season Name -->
                        <div class="col-md-2">
                            <label>Season</label>
                            <input type="text" name="season_name" class="form-control"
                                value="{{ request('season_name') }}" placeholder="Season">
                        </div>

                        <!-- Start Date -->
                        <div class="col-md-2">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control"
                                value="{{ request('start_date') }}">
                        </div>

                        <!-- End Date -->
                        <div class="col-md-2">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>

                        <!-- Price Range -->
                        <div class="col-md-2">
                            <label>Min Price</label>
                            <input type="number" name="min_price" class="form-control"
                                value="{{ request('min_price') }}" placeholder="Min ₹">
                        </div>

                        <div class="col-md-2">
                            <label>Max Price</label>
                            <input type="number" name="max_price" class="form-control"
                                value="{{ request('max_price') }}" placeholder="Max ₹">
                        </div>

                        <!-- Status -->
                        <div class="col-md-2 mt-2">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">All</option>
                                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="col-md-3 mt-4">
                            <button class="btn btn-primary me-2">Filter</button>
                            <a href="{{ url()->current() }}" class="btn btn-secondary">Reset</a>
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
                <h3 class="mb-0 fw-bolder">Seasonal Room Rates</h3>
                <div class="float-right">
                    @if(auth()->user()->hasPermission('seasonal_room_rate_setup', 'create'))
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
                    @if($seasonalRates->count() > 0)
                    <table class="table table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                @if(auth()->user()->hasPermission('seasonal_room_rate_setup', 'edit') || auth()->user()->hasPermission('seasonal_room_rate_setup', 'view'))
                                <th width="120">Action</th>@endif
                                <th>Room Type</th>
                                <th>Season</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Seasonal Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($seasonalRates as $season)
                            <tr>
                                @if(auth()->user()->hasPermission('seasonal_room_rate_setup', 'edit') || auth()->user()->hasPermission('seasonal_room_rate_setup', 'view'))
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if(auth()->user()->hasPermission('seasonal_room_rate_setup', 'view'))
                                        <button
                                            class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                            data-url="{{ route('seasonal-rates.show', $season->id) }}" title="View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('seasonal_room_rate_setup', 'edit'))

                                        <a class="btn one-btn-outline-success btn-icon btn-sm editSeasonBtn btn-loader mx-1"
                                            data-id="{{ $season->id }}"
                                            data-room-type-id="{{ $season->room_type_id }}"
                                            data-season-name="{{ $season->season_name }}"
                                            data-start-date="{{ \Carbon\Carbon::parse($season->start_date)->format('Y-m-d') }}"
                                            data-end-date="{{ \Carbon\Carbon::parse($season->end_date)->format('Y-m-d') }}"
                                            data-seasonal-price="{{ $season->seasonal_price }}"
                                            data-status="{{ $season->status }}">
                                            <i class="fa fa-edit"></i>
                                        </a>

                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input statusToggle globalStatusToggle"
                                                id="seasonSwitch{{ $season->id }}" data-id="{{ $season->id }}" data-model="SeasonalRate"
                                                {{ $season->status ? 'checked' : '' }}>

                                            <label class="custom-control-label"
                                                for="seasonSwitch{{ $season->id }}"></label>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                                @endif
                                <td>{{ $season->roomType->name ?? '-' }}</td>

                                <td>
                                    {{ $season->season_name ?? '-' }}
                                </td>

                                <td>
                                    {{ formatDate($season->start_date) }}
                                </td>

                                <td>
                                    {{ formatDate($season->end_date) }}
                                </td>

                                <td>
                                    ₹{{ number_format($season->seasonal_price, 2) }}
                                </td>

                                <td>
                                    <span class="badge {{ $season->status ? 'badge-success' : 'badge-danger' }}">
                                        {{ $season->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $seasonalRates->withQueryString()->links() }}
                    </div>

                    @else
                    <p class="text-muted">No seasonal rates found.</p>
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
                    Create Seasonal Room Rate
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="CreateForm" action="{{ route('seasonal-rates.store') }}" method="POST" class="ajax-form" data-reset="1" data-modal="#createModal" novalidate>
                @csrf
                <div class="modal-body">

                    <div class="row">

                        {{-- Room Type --}}
                        <div class="col-md-6 mb-3">
                            <label>Room Type</label>
                            <select name="room_type_id" class="select2 form-control" required>
                                <option value="">Select Room Type</option>
                                @foreach($roomTypes as $roomType)
                                <option value="{{ $roomType->id }}">{{ $roomType->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger error-text" data-error="room_type_id"></small>
                        </div>

                        {{-- Season Name --}}
                        <div class="col-md-6 mb-3">
                            <label>Season Name</label>
                            <input type="text" name="season_name" class="form-control"
                                placeholder="Eg: Diwali, Christmas">
                            <small class="text-danger error-text" data-error="season_name"></small>
                        </div>

                        {{-- Start Date --}}
                        <div class="col-md-6 mb-3">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control" required>
                            <small class="text-danger error-text" data-error="start_date"></small>
                        </div>

                        {{-- End Date --}}
                        <div class="col-md-6 mb-3">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control" required>
                            <small class="text-danger error-text" data-error="end_date"></small>
                        </div>

                        {{-- Seasonal Price --}}
                        <div class="col-md-6 mb-3">
                            <label>Seasonal Price</label>
                            <input type="number" step="0.01" name="seasonal_price" class="form-control" required>
                            <small class="text-danger error-text" data-error="seasonal_price"></small>
                        </div>

                        {{-- Status --}}
                        <div class="col-md-6 mb-3">
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
                    <button type="reset" class="btn btn-secondary" id="clearaddBtn">Clear</button>
                    <button type="submit" class="btn btn-danger" id="saveSeasonalRoomRateBtn">Save</button>
                </div>
            </form>

        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title">Edit Seasonal Room Rate</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="EditForm" method="POST" class="ajax-form" data-reset="0" data-modal="#editModal" novalidate>
                @csrf
                @method('PUT')

                <div class="modal-body">

                    <input type="hidden" name="id" id="season_id">

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label>Room Type</label>
                            <select name="room_type_id" class="select2 form-control" required>
                                <option value="">Select Room Type</option>
                                @foreach($roomTypes as $roomType)
                                <option value="{{ $roomType->id }}">{{ $roomType->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger error-text" data-error="room_type_id"></small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Season Name</label>
                            <input type="text" name="season_name" class="form-control">
                            <small class="text-danger error-text" data-error="season_name"></small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control" required>
                            <small class="text-danger error-text" data-error="start_date"></small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control" required>
                            <small class="text-danger error-text" data-error="end_date"></small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Seasonal Price</label>
                            <input type="number" step="0.01" name="seasonal_price" class="form-control" required>
                            <small class="text-danger error-text" data-error="seasonal_price"></small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Status</label>
                            <select name="status" class="form-control" required>
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
    $(document).on('click', '.editSeasonBtn', function() {

        let el = this;
        let form = $('#EditForm');

        form.find('[name="id"]').val(el.dataset.id);

        form.find('[name="room_type_id"]')
            .val(el.dataset.roomTypeId)
            .trigger('change');

        form.find('[name="season_name"]').val(el.dataset.seasonName);
        form.find('[name="start_date"]').val(el.dataset.startDate);
        form.find('[name="end_date"]').val(el.dataset.endDate);
        form.find('[name="seasonal_price"]').val(el.dataset.seasonalPrice);
        form.find('[name="status"]').val(el.dataset.status);
        $('#EditForm').attr('action', `/room-management/seasonal-rates/${el.dataset.id}`);

        $('#editModal').modal('show');
    });
</script>
@endsection