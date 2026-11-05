@extends('layouts.master')

@section('title', 'Room types')
@section('mainbreadcrumb', 'Room Management')
@section('subbreadcrumb', 'Room types')

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
                <form method="GET" action="{{ url()->current() }}" class="mb-3">
                    <div class="row">
                        <div class="col-4">
                            <label>Room Type</label>
                            <select name="room_type_id" class="form-control select2">
                                <option value="">All Room Types</option>
                                @foreach($roomTypes as $roomType)
                                <option value="{{ $roomType->id }}"
                                    {{ request('room_type_id') == $roomType->id ? 'selected' : '' }}>
                                    {{ $roomType->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-3 d-flex align-items-end">
                            <a href="{{ url()->current() }}" class="btn btn-secondary">Reset</a>
                            <button class="btn btn-info mx-2">Filter</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
<!-- Filter end -->
<div class="row">
    <div class="col-sm-12">
        <div class="card mb-g">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0 fw-bolder">Room Number</h3>
                <div class="float-right">
                    @if(auth()->user()->hasPermission('room_numbers', 'create'))
                    <button class="btn btn-danger btn-sm btn-loader" data-toggle="modal" data-target="#createModal"><i
                            class="fa-solid fa-plus"></i>
                        Create</button>
                    @endif
                    <button id="filterBtn" class="btn btn-info btn-sm" onclick="toggleFilter()">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>

                </div>
            </div>

            <div class="card-body">
                <div class="frame-wrap p-0 border-0 m-0" id="table-container">
                    @if($roomnumbers->count() > 0)
                    @foreach($roomnumbers as $room_type_id => $rooms)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5>{{ $rooms->first()->roomType->name ?? 'Unknown' }}</h5>
                        <div class="float-right">
                            {{--<a class="btn one-btn-outline-success btn-icon btn-sm editBtn" data-room-type-id="{{ $room_type_id }}"
                                data-rooms='@json($rooms)'>
                                <i class="fa fa-edit"></i>
                            </a>--}}

                        </div>
                    </div>
                    <table class="table table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                @if(auth()->user()->hasPermission('room_numbers', 'view') || 
                                    auth()->user()->hasPermission('room_numbers', 'edit'))
                                <th width="120">Action</th>
                                @endif
                                <th>Room Number</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rooms as $room)
                            <tr>
                                @if(auth()->user()->hasPermission('room_numbers', 'view') || 
                                    auth()->user()->hasPermission('room_numbers', 'edit'))
                                <td>
                                    <div class="d-flex  align-items-center" style="width: 70px;">
                                        @if(auth()->user()->hasPermission('room_numbers', 'view'))
                                        <button  
                                            class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                            data-url="{{ route('room-numbers.show', $room->id) }}" title="View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('room_numbers', 'edit'))
                                        <a class="btn one-btn-outline-success btn-icon btn-sm editBtn btn-loader mx-1"
                                            data-id="{{ $room->id }}"
                                            data-room-type-id="{{ $room->room_type_id }}"
                                            data-room-number="{{ $room->room_number }}"
                                            data-status="{{ $room->status }}">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                                @endif

                                <td>{{ $room->room_number }}</td>

                                <td>
                                    @php
                                    $statusMap = [
                                    0 => ['Available', 'success'],
                                    1 => ['Occupied', 'warning'],
                                    2 => ['Maintenance', 'danger'],
                                    3 => ['Dirty', 'secondary'],
                                    ];
                                    @endphp
                                    <span class="badge badge-{{ $statusMap[$room->status][1] }}">
                                        {{ $statusMap[$room->status][0] }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endforeach
                    @else
                    <p class="text-muted">No room numbers found.</p>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h4 class="modal-title">
                    Create Room Number
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="CreateForm" action="{{ route('room-numbers.store') }}" method="POST" class="ajax-form" data-reset="1" data-modal="#createModal" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="row">

                        {{-- Room Type --}}
                        <div class="form-group col-6">
                            <label>Room Type <span class="text-danger">*</span></label>
                            <select name="room_type_id" class="form-control select2">
                                <option value="">Select Room Type</option>
                                @foreach($roomTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger error-text" data-error="room_type_id"></small>
                        </div>

                        <div class="form-group col-6">
                            <label>Number of Rooms <span class="text-danger">*</span></label>
                            <input type="number" name="room_count" class="form-control" min="1" placeholder="e.g. 10">
                            <small class="text-danger error-text" data-error="room_count"></small>
                        </div>
                    </div>
                    <div id="roomNumbersContainer" class="row"></div>

                    {{-- Status --}}
                    <div class="row mt-2">
                        <div class="form-group col-6">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="0" selected>Available</option>
                                <option value="1">Occupied</option>
                                <option value="2">Maintenance</option>
                                <option value="3">Dirty</option>
                                <option value="4">Cleaning</option>
                                <option value="5">Blocked</option>
                            </select>
                            <small class="text-danger error-text" data-error="status"></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="reset" class="btn btn-secondary" id="clearaddBtn">Clear</button>
                    <button type="submit" class="btn btn-danger" id="saveRoomNumberBtn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title">
                    Edit Room Number
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="EditForm" method="POST" class="ajax-form" data-reset="0" data-modal="#editModal" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="id">

                <div class="modal-body">
                    <div class="row">

                        <div class="form-group col-6">
                            <label>Room Type <span class="text-danger">*</span></label>
                            <select name="room_type_id" class="form-control select2">
                                <option value="">Select Room Type</option>
                                @foreach($roomTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger error-text" data-error="room_type_id"></small>
                        </div>

                        <div class="form-group col-6">
                            <label>Room Number <span class="text-danger">*</span></label>
                            <input type="text" name="room_number" class="form-control">
                            <small class="text-danger error-text" data-error="room_number"></small>
                        </div>

                    </div>

                    <div class="row mt-2">
                        <div class="form-group col-6">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="0">Available</option>
                                <option value="1">Occupied</option>
                                <option value="2">Maintenance</option>
                                <option value="3">Dirty</option>
                                <option value="4">Cleaning</option>
                                <option value="5">Blocked</option>
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
    // When user enters room count, create that many input fields
    $('input[name="room_count"]').on('input', function() {
        let count = parseInt($(this).val());
        let container = $('#roomNumbersContainer');
        container.html(''); // clear previous

        if (!isNaN(count) && count > 0) {
            for (let i = 1; i <= count; i++) {
                container.append(`
                <div class="form-group col-6">
                    <label>Room Number ${i} <span class="text-danger">*</span></label>
                    <input type="text" name="room_numbers[]" class="form-control" placeholder="e.g. 101">
                    <small class="text-danger error-text" data-error="room_numbers.${i-1}"></small>
                </div>
            `);
            }
        }
    });

    $(document).on('click', '.editBtn', function() {

        let el = this;
        let form = $('#EditForm');

        form.find('[name="id"]').val(el.dataset.id);

        form.find('[name="room_type_id"]')
            .val(el.dataset.roomTypeId)
            .trigger('change');

        form.find('[name="room_number"]').val(el.dataset.roomNumber);

        form.find('[name="status"]').val(el.dataset.status);

        // ✅ dynamic update URL
        form.attr('action', `/room-management/room-numbers/${el.dataset.id}`);

        $('#editModal').modal('show');
    });
</script>

@endsection