@extends('layouts.master')

@section('title', 'Attendance Management')
@section('mainbreadcrumb', 'Attendance Management')

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
                        <div class="col-3">
                            <!-- Employee -->
                            <div class="form-group">
                                <label>Employee</label>
                                <select name="employee_id" class="form-control">
                                    <option value="">All Employees</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_code }})
                                        </option>
                                    @endforeach
                                    
                                </select>
                            </div>
                        </div>
                        <div class="col-3">
                            <!-- Attendance Date -->
                            <div class="form-group">
                                <label>Attendance Date</label>
                                <input type="date" name="attendance_date" class="form-control"
                                    value="{{ request('attendance_date') }}">
                            </div>
                        </div>
                        <div class="col-3">
                            <!-- Status -->
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                                    <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                                    <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                                    <option value="half_day" {{ request('status') == 'half_day' ? 'selected' : '' }}>Half Day</option>
                                    <option value="leave" {{ request('status') == 'leave' ? 'selected' : '' }}>Leave</option>
                                    <option value="week_off" {{ request('status') == 'week_off' ? 'selected' : '' }}>Week Off</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-3">
                            <!-- Buttons -->
                            <div class="d-flex">
                                <div class="mx-2">
                                    <a href="{{ url()->current() }}" class="btn btn-secondary mt-4">
                                        Reset
                                    </a>
                                </div>
                                <div class="">
                                    <button type="submit" class="btn btn-info mt-4">
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
                <h3 class="mb-0 fw-bolder">Attendance Records</h3>
                <div class="float-right">
                    <a href="{{ route('check-attendances') }}" class="btn btn-primary btn-sm btn-loader"><i
                            class="fa-solid fa-plus"></i>
                        Check Attendance</a>
                    <button class="btn btn-danger btn-sm btn-loader" data-toggle="modal" data-target="#createModal"><i
                            class="fa-solid fa-plus"></i>
                        Create</button>
                    <button class="btn btn-info btn-sm" onclick="toggleFilter()">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="frame-wrap p-0 border-0 m-0" id="table-container">
                    @if($attendances->count() > 0)
                    <table class="table m-0 table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Action</th>
                                <th>Sr</th>
                                <th>Employee</th>
                                <th>Date</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Working Hours</th>
                                <th>Status</th>
                                <th>Late Minutes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attendances as $index => $attendance)
                            <tr>
                                <td>
                                    <button 
                                        class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                        data-url="{{ route('attendances.show', $attendance->id) }}" title="View">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <a href="javascript:void(0);" 
                                        class="btn one-btn-outline-success btn-icon btn-sm editAttendanceBtn btn-loader"
                                        data-id="{{ $attendance->id }}" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <button 
                                        class="btn btn-outline-danger btn-icon btn-sm delete-btn"
                                        data-id="{{ $attendance->id }}" data-url="{{ route('attendances.destroy', $attendance->id) }}" title="Delete">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                                <td>{{ ($attendances->currentPage() - 1) * $attendances->perPage() + $index + 1 }}</td>
                                <td>{{ $attendance->employee->first_name ?? 'N/A' }} {{ $attendance->employee->last_name ?? 'N/A' }}</td>
                                <td>{{ $attendance->attendance_date->format('Y-m-d') }}</td>
                                <td>{{ $attendance->check_in ? $attendance->check_in->format('H:i') : '-' }}</td>
                                <td>{{ $attendance->check_out ? $attendance->check_out->format('H:i') : '-' }}</td>
                                <td>{{ $attendance->working_hours }}</td>
                                <td>
                                    <span class="badge badge-{{ $attendance->status == 'present' ? 'success' : ($attendance->status == 'absent' ? 'danger' : 'warning') }}">
                                        {{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                                    </span>
                                </td>
                                <td>{{ $attendance->late_minutes }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-end mt-3">
                        {{ $attendances->links() }}
                    </div>

                    @else
                    <p>No attendance records found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h4 class="modal-title">
                    Add Attendance
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="CreateForm" action="{{ route('attendances.store') }}" method="POST" class="ajax-form" data-reset="1" data-modal="#createModal" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-4 mt-2">
                            <!-- Employee -->
                            <div class="form-group">
                                <label>Employee  </label>
                                <select name="employee_id" class="form-control" required>
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">
                                    {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_code }})
                                    </option>
                                    @endforeach
                                </select>
                                <small class="text-danger error-text" data-error="employee_id"></small>
                            </div>
                        </div>
                        <div class="col-4 mt-2">
                            <!-- Shift -->
                            <div class="form-group">
                                <label>Shift</label>
                                <select name="shift_id" class="form-control">
                                    <option value="">Select Shift</option>
                                    @foreach($shifts as $shift)
                                        <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-danger error-text" data-error="shift_id"></small>
                            </div>
                        </div>
                        <div class="col-4 mt-2">
                            <!-- Attendance Date -->
                            <div class="form-group">
                                <label>Attendance Date</label>
                                <input type="date" name="attendance_date" class="form-control" required>
                                <small class="text-danger error-text" data-error="attendance_date"></small>
                            </div>
                        </div>
                        <div class="col-4 mt-2">
                            <!-- Status -->
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="present">Present</option>
                                    <option value="absent">Absent</option>
                                    <option value="late">Late</option>
                                    <option value="half_day">Half Day</option>
                                    <option value="leave">Leave</option>
                                    <option value="week_off">Week Off</option>
                                </select>
                                <small class="text-danger error-text" data-error="status"></small>
                            </div>
                        </div>
                        <div class="col-4 mt-2">
                            <!-- Check In -->
                            <div class="form-group">
                                <label>Check In</label>
                                <input type="time" name="check_in" class="form-control">
                                <small class="text-danger error-text" data-error="check_in"></small>
                            </div>
                        </div>
                        <div class="col-4 mt-2">
                            <!-- Check Out -->
                            <div class="form-group">
                                <label>Check Out</label>
                                <input type="time" name="check_out" class="form-control">
                                <small class="text-danger error-text" data-error="check_out"></small>
                            </div>
                        </div>
                        <div class="col-4 mt-2">
                            <!-- Working Hours -->
                            <div class="form-group">
                                <label>Working Hours</label>
                                <input type="number" step="0.01" name="working_hours" class="form-control">
                                <small class="text-danger error-text" data-error="working_hours"></small>
                            </div>
                        </div>
                        <div class="col-4 mt-2">
                            <!-- Overtime Hours -->
                            <div class="form-group">
                                <label>Overtime Hours</label>
                                <input type="number" step="0.01" name="overtime_hours" class="form-control">
                                <small class="text-danger error-text" data-error="overtime_hours"></small>
                            </div>
                        </div>
                        <div class="col-4 mt-2">
                            <!-- Late Minutes -->
                            <div class="form-group">
                                <label>Late Minutes</label>
                                <input type="number" name="late_minutes" class="form-control">
                                <small class="text-danger error-text" data-error="late_minutes"></small>
                            </div>
                        </div>
                        <div class="col-12">
                            <!-- Remarks -->
                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea name="remarks" class="form-control" rows="3"></textarea>
                                <small class="text-danger error-text" data-error="remarks"></small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="reset" class="btn btn-secondary" id="clearaddBtn">Clear</button>
                    <button type="submit" class="btn btn-danger" id="saveAttendanceBtn">Save</button>
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
                    Edit Attendance
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="EditForm" method="POST" class="ajax-form" data-reset="0" data-modal="#editModal" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" id="edit_attendance_id" name="attendance_id">
                    <div class="row">
                        <div class="col-4 mt-2">
                            <!-- Employee -->
                            <div class="form-group">
                                <label>Employee</label>
                                <select id="edit_employee_id" name="employee_id" class="form-control" required>
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_code }})</option>
                                    @endforeach
                                </select>
                                <small class="text-danger error-text" data-error="employee_id"></small>
                            </div>
                        </div>
                        <div class="col-4 mt-2">
                            <!-- Shift -->
                            <div class="form-group">
                                <label>Shift</label>
                                <select id="edit_shift_id" name="shift_id" class="form-control">
                                    <option value="">Select Shift</option>
                                    @foreach($shifts as $shift)
                                        <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-danger error-text" data-error="shift_id"></small>
                            </div>
                        </div>
                        <div class="col-4 mt-2">
                            <!-- Attendance Date -->
                            <div class="form-group">
                                <label>Attendance Date</label>
                                <input type="date" id="edit_attendance_date" name="attendance_date" class="form-control" required>
                                <small class="text-danger error-text" data-error="attendance_date"></small>
                            </div>
                        </div>
                        <div class="col-4 mt-2">
                            <!-- Status -->
                            <div class="form-group">
                                <label>Status</label>
                                <select id="edit_status" name="status" class="form-control" required>
                                    <option value="present">Present</option>
                                    <option value="absent">Absent</option>
                                    <option value="late">Late</option>
                                    <option value="half_day">Half Day</option>
                                    <option value="leave">Leave</option>
                                    <option value="week_off">Week Off</option>
                                </select>
                                <small class="text-danger error-text" data-error="status"></small>
                            </div>
                        </div>
                        <div class="col-4 mt-2">
                            <!-- Check In -->
                            <div class="form-group">
                                <label>Check In</label>
                                <input type="time" id="edit_check_in" name="check_in" class="form-control">
                                <small class="text-danger error-text" data-error="check_in"></small>
                            </div>
                        </div>
                        <div class="col-4 mt-2">
                            <!-- Check Out -->
                            <div class="form-group">
                                <label>Check Out</label>
                                <input type="time" id="edit_check_out" name="check_out" class="form-control">
                                <small class="text-danger error-text" data-error="check_out"></small>
                            </div>
                        </div>
                        <div class="col-4 mt-2">
                            <!-- Working Hours -->
                            <div class="form-group">
                                <label>Working Hours</label>
                                <input type="number" step="0.01" id="edit_working_hours" name="working_hours" class="form-control">
                                <small class="text-danger error-text" data-error="working_hours"></small>
                            </div>
                        </div>
                        <div class="col-4 mt-2">
                            <!-- Overtime Hours -->
                            <div class="form-group">
                                <label>Overtime Hours</label>
                                <input type="number" step="0.01" id="edit_overtime_hours" name="overtime_hours" class="form-control">
                                <small class="text-danger error-text" data-error="overtime_hours"></small>
                            </div>
                        </div>
                        <div class="col-4 mt-2">
                            <!-- Late Minutes -->
                            <div class="form-group">
                                <label>Late Minutes</label>
                                <input type="number" id="edit_late_minutes" name="late_minutes" class="form-control">
                                <small class="text-danger error-text" data-error="late_minutes"></small>
                            </div>
                        </div>
                        <div class="col-12">
                            <!-- Remarks -->
                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea id="edit_remarks" name="remarks" class="form-control" rows="3"></textarea>
                                <small class="text-danger error-text" data-error="remarks"></small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="reset" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success" id="updateAttendanceBtn">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('layouts.common-view')

@include('layouts.confirmationModal')

<script>
    // When edit button is clicked
    $(document).on('click', '.editAttendanceBtn', function() {
        let attendanceId = $(this).data('id');

        // Fetch attendance data via AJAX
        $.get('/employee-management/attendances/' + attendanceId + '/edit', function(data) {
            // Assuming you return JSON data
            $('#edit_attendance_id').val(data.id);
            $('#edit_employee_id').val(data.employee_id);
            $('#edit_shift_id').val(data.shift_id);
            $('#edit_attendance_date').val(data.attendance_date ? data.attendance_date.split('T')[0] : '');
            $('#edit_status').val(data.status);
            $('#edit_check_in').val(data.check_in ? data.check_in.split('T')[1].substring(0, 5) : '');
            $('#edit_check_out').val( data.check_out ? data.check_out.split('T')[1].substring(0, 5) : '');
            $('#edit_working_hours').val(data.working_hours);
            $('#edit_overtime_hours').val(data.overtime_hours);
            $('#edit_late_minutes').val(data.late_minutes);
            $('#edit_remarks').val(data.remarks);

            // Change action dynamically
            $('#EditForm').attr('action', '/employee-management/attendances/' + attendanceId);
            
            // Show the edit modal
            $('#editModal').modal('show');
        });
    });

    // Delete Attendance
    $(document).on('click', '.delete-btn', function() {
        const attendanceId = $(this).data('id') || $(this).attr('data-url').split('/').pop();
        const title = 'Delete Attendance';
        const message = 'Are you sure you want to delete this attendance record?';
        const color = 'danger';
        $('#confirmationModal .modal-header').removeClass('bg-primary bg-warning bg-danger bg-success').addClass('one-bg-danger');
        $('#confirmationModalConfirmBtn').removeClass('btn-primary btn-warning btn-danger btn-success').addClass('btn-' + color);
        openConfirmationModal(`/employee-management/attendances/${attendanceId}`, title, message, 'DELETE');
    });

    function toggleFilter() {
        const filterRow = document.getElementById('filterRow');
        if (filterRow.style.display === 'none' || filterRow.style.display === '') {
            filterRow.style.display = 'block';
        } else {
            filterRow.style.display = 'none';
        }
    }

    function openConfirmationModal(url, title = 'Confirm Action', message = 'Are you sure you want to proceed?', method = 'POST') {
        $('#confirmationModalTitle').text(title);
        $('#confirmationModalBody').html('<p>' + message + '</p>');
        $('#confirmationModal').modal('show');
        modalButton = $('#confirmationModalConfirmBtn')[0]; // store DOM button reference

        // Remove previous event listeners
        $(modalButton).off('click');

        // Add new event listener
        $(modalButton).on('click', function() {
            // Disable button and show loading
            $(modalButton).prop('disabled', true);
            const originalText = $(modalButton).text();
            $(modalButton).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

            $.ajax({
                url: url,
                type: method,
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#confirmationModal').modal('hide');
                    if (response.status) {
                        toastr.success(response.message || 'Action completed successfully');
                        // Reload the page or update the table
                        location.reload();
                    } else {
                        toastr.error(response.message || 'Something went wrong');
                    }
                },
                error: function(xhr) {
                    $('#confirmationModal').modal('hide');
                    toastr.error('An error occurred. Please try again.');
                },
                complete: function() {
                    // Re-enable button and restore text
                    $(modalButton).prop('disabled', false);
                    $(modalButton).text(originalText);
                }
            });
        });
    }
</script>

@endsection