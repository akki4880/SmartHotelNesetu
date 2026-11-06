@extends('layouts.master')

@section('title', 'Shifts')
@section('mainbreadcrumb', 'Employee Management')
@section('subbreadcrumb', 'Shifts')
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
                    <div class="row mb-3">

                        <!-- Shift Name -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Shift Name</label>
                                <input type="text" name="name" class="form-control"
                                    value="{{ request('name') }}" placeholder="Shift Name">
                            </div>
                        </div>

                        <!-- Start From -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Start From</label>
                                <input type="time" name="start_from" class="form-control"
                                    value="{{ request('start_from') }}">
                            </div>
                        </div>

                        <!-- Start To -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Start To</label>
                                <input type="time" name="start_to" class="form-control"
                                    value="{{ request('start_to') }}">
                            </div>
                        </div>

                        <!-- Night Shift -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Night Shift</label>
                                <select name="is_night_shift" class="form-control">
                                    <option value="">All</option>
                                    <option value="1" {{ request('is_night_shift') == '1' ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ request('is_night_shift') == '0' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-3 mt-2">
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
                        <div class="col-md-3 mt-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                    <a href="{{ url()->current() }}" class="btn btn-secondary">
                                        <i class="fas fa-sync-alt"></i> Reset
                                    </a>
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
                <h3 class="mb-0 fw-bolder">Employee Shifts</h3>
                <div class="float-right">
                    @if(auth()->user()->hasPermission('shifts', 'create'))
                    <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#createModal"><i
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
                    @if(!$shifts->isEmpty())
                    <table class="table m-0 table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                @if(auth()->user()->hasPermission('shifts', 'view') || auth()->user()->hasPermission('shifts', 'edit'))
                                <th>Action</th>
                                @endif
                                <th>Sr</th>
                                <th>Name</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Grace Minutes</th>
                                <th>Expected Hours</th>
                                <th>Night Shift</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shifts as $shift)
                            <tr>
                                @if(auth()->user()->hasPermission('shifts', 'view') || auth()->user()->hasPermission('shifts', 'edit'))
                                <td class="w-auto">
                                    <div class="d-flex">
                                        @if(auth()->user()->hasPermission('shifts', 'view'))
                                        <button
                                            class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                            data-url="{{ route('shifts.show', $shift->id) }}" title="View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('shifts', 'edit'))
                                        <a href="javascript:void(0);"
                                            class="btn one-btn-outline-success btn-icon btn-sm editShiftBtn btn-loader mx-1"
                                            data-id="{{ $shift->id }}" data-start_time="{{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}"
                                            data-end_time="{{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}"
                                            data-name="{{ $shift->name }}" data-grace_minutes="{{ $shift->grace_minutes }}" data-is_night_shift="{{ $shift->is_night_shift }}" data-expected_hours="{{ $shift->expected_hours }}" data-status="{{ $shift->status }}"
                                            title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <div class="custom-control custom-switch mt-1 ml-1">
                                            <input type="checkbox"
                                                class="custom-control-input statusToggle globalStatusToggle"
                                                id="switch{{ $shift->id }}" data-id="{{ $shift->id }}"
                                                data-url="/employee-management/shifts/{{ $shift->id }}/toggle-status" {{
                                            $shift->status ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="switch{{ $shift->id }}"></label>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                                @endif
                                <td>{{ $shifts->firstItem() + $loop->index }}</td>
                                <td>{{ $shift->name }}</td>
                                <td>{{ formatTime($shift->start_time) }}</td>
                                <td>{{ formatTime($shift->end_time) }}</td>
                                <td>{{ $shift->grace_minutes }}</td>
                                <td>{{ $shift->expected_hours }}</td>
                                <td>
                                    @if($shift->is_night_shift)
                                    <span class="badge badge-info">Yes</span>
                                    @else
                                    <span class="badge badge-secondary">No</span>
                                    @endif
                                </td>
                                <td>
                                    @if($shift->status)
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
                        {{ $shifts->links() }}
                    </div>
                    @else
                    No Employee shifts found
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
                    Create Shifts
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="CreateForm" action="{{ route('shifts.store') }}" method="POST" class="ajax-form"
                data-reset="1" data-modal="#createModal" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <!-- Shift Name -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Shift Name</label>
                                <input type="text" name="name" class="form-control" placeholder="Morning Shift" required>
                                <small class="text-danger error-text" data-error="name"></small>
                            </div>
                        </div>

                        <!-- Start Time -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Start Time</label>
                                <input type="time" name="start_time" class="form-control" required>
                                <small class="text-danger error-text" data-error="start_time"></small>
                            </div>
                        </div>

                        <!-- End Time -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>End Time</label>
                                <input type="time" name="end_time" class="form-control" required>
                                <small class="text-danger error-text" data-error="end_time"></small>
                            </div>
                        </div>

                        <!-- Grace Minutes -->
                        <div class="col-md-4 mt-2">
                            <div class="form-group">
                                <label>Grace Minutes</label>
                                <input type="number" name="grace_minutes" class="form-control" placeholder="15" min="0">
                                <small class="text-danger error-text" data-error="grace_minutes"></small>
                            </div>
                        </div>

                        <!-- Night Shift -->
                        <!-- <div class="col-md-4 mt-2">
                            <div class="form-group">
                                <label>Night Shift</label>
                                <select name="is_night_shift" class="form-control">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                                <small class="text-danger error-text" data-error="is_night_shift"></small>
                            </div>
                        </div> -->

                        <div class="col-md-4 mt-2">
                            <div class="form-group">
                                <label>Expected Working Hours</label>
                                <input type="number" step="0.1" name="expected_hours" class="form-control" placeholder="8">
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-4 mt-2">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                                <small class="text-danger error-text" data-error="status"></small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
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
                    Edit Employee Shifts
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
                        <!-- Shift Name -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Shift Name</label>
                                <input type="text" name="name" id="edit_name" class="form-control" placeholder="Morning Shift" required>
                                <small class="text-danger error-text" data-error="name"></small>
                            </div>
                        </div>

                        <!-- Start Time -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Start Time</label>
                                <input type="time" id="edit_start_time" name="start_time" class="form-control" required>
                                <small class="text-danger error-text" data-error="start_time"></small>
                            </div>
                        </div>

                        <!-- End Time -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>End Time</label>
                                <input type="time" name="end_time" id="edit_end_time" class="form-control" required>
                                <small class="text-danger error-text" data-error="end_time"></small>
                            </div>
                        </div>

                        <!-- Grace Minutes -->
                        <div class="col-md-4 mt-2">
                            <div class="form-group">
                                <label>Grace Minutes</label>
                                <input type="number" name="grace_minutes" id="edit_grace_minutes" class="form-control" placeholder="15" min="0">
                                <small class="text-danger error-text" data-error="grace_minutes"></small>
                            </div>
                        </div>

                        <!-- Night Shift -->
                        <div class="col-md-4 mt-2">
                            <div class="form-group">
                                <label>Expected Working Hours</label>
                                <input type="number" step="0.1" name="expected_hours" id="edit_expected_hours" class="form-control">
                                <small class="text-danger error-text" data-error="expected_hours"></small>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-4 mt-2">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" id="edit_status" class="form-control">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                                <small class="text-danger error-text" data-error="status"></small>
                            </div>
                        </div>
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
    document.addEventListener('DOMContentLoaded', function() {

        const startInput = document.querySelector('[name="start_time"]');
        const endInput = document.querySelector('[name="end_time"]');
        const hoursInput = document.querySelector('[name="expected_hours"]');

        function calculateHours() {
            const start = startInput.value;
            const end = endInput.value;

            if (!start || !end) return;

            let startTime = new Date(`1970-01-01T${start}:00`);
            let endTime = new Date(`1970-01-01T${end}:00`);

            // 🔥 Handle night shift automatically
            if (endTime <= startTime) {
                endTime.setDate(endTime.getDate() + 1);
            }

            let diffMs = endTime - startTime;
            let diffHours = diffMs / (1000 * 60 * 60);

            // round to 1 decimal
            diffHours = Math.round(diffHours * 10) / 10;

            hoursInput.value = diffHours;
        }

        startInput.addEventListener('change', calculateHours);
        endInput.addEventListener('change', calculateHours);

    });
    // When edit button is clicked
    $(document).on('click', '.editShiftBtn', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var start_time = $(this).data('start_time');
        var end_time = $(this).data('end_time');
        var grace_minutes = $(this).data('grace_minutes');
        var is_night_shift = $(this).data('is_night_shift');
        var expected_hours = $(this).data('expected_hours');
        var status = $(this).data('status');

        // Set form action
        $('#EditForm').attr('action', '/employee-management/shifts/' + id);

        // Fill the form fields
        $('#edit_name').val(name);
        $('#edit_start_time').val(start_time);
        $('#edit_end_time').val(end_time);
        $('#edit_grace_minutes').val(grace_minutes);
        $('#edit_expected_hours').val(expected_hours);
        $('#edit_is_night_shift').val(is_night_shift);
        $('#edit_status').val(status);

        // Show the modal
        $('#editModal').modal('show');
    });

    document.addEventListener('DOMContentLoaded', function() {

        const startInput = document.getElementById('edit_start_time');
        const endInput = document.getElementById('edit_end_time');
        const hoursInput = document.getElementById('edit_expected_hours');

        function calculateHours() {
            const start = startInput.value;
            const end = endInput.value;

            if (!start || !end) return;

            let startTime = new Date(`1970-01-01T${start}:00`);
            let endTime = new Date(`1970-01-01T${end}:00`);

            if (endTime <= startTime) {
                endTime.setDate(endTime.getDate() + 1);
            }

            let diff = (endTime - startTime) / (1000 * 60 * 60);
            diff = Math.round(diff * 10) / 10;

            hoursInput.value = diff;
        }

        startInput.addEventListener('change', calculateHours);
        endInput.addEventListener('change', calculateHours);

    });
</script>


@endsection