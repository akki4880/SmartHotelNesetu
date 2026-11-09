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
                    @if(auth()->user()->hasPermission('attendance', 'create'))
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
                    @if($attendances->count() > 0)
                    <table class="table m-0 table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                @if(auth()->user()->hasPermission('attendance', 'view') || auth()->user()->hasPermission('attendance', 'edit') || auth()->user()->hasPermission('attendance', 'delete'))
                                <th>Action</th>@endif
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
                                @if(auth()->user()->hasPermission('attendance', 'view') || auth()->user()->hasPermission('attendance', 'edit') || auth()->user()->hasPermission('attendance', 'delete'))
                                <td>
                                    @if(auth()->user()->hasPermission('attendance', 'view'))
                                    <button
                                        class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                        data-url="{{ route('attendances.show', $attendance->id) }}" title="View">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    @endif
                                    @if(auth()->user()->hasPermission('attendance', 'edit'))
                                    <a href="javascript:void(0);"
                                        class="btn one-btn-outline-success btn-icon btn-sm editAttendanceBtn btn-loader"
                                        data-id="{{ $attendance->id }}" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('attendance', 'delete'))
                                    <button
                                        class="btn btn-outline-danger btn-icon btn-sm delete-btn"
                                        data-id="{{ $attendance->id }}" data-url="{{ route('attendances.destroy', $attendance->id) }}" title="Delete">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                    @endif
                                </td>
                                @endif
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
                                <label>Employee </label>
                                <select id="create_employee_id" name="employee_id" class="form-control create-employee-select" required>
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" data-shift="{{ $employee->shift_id }}">
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
                                <select id="create_shift_id" name="shift_id" class="form-control">
                                    <option value="">Select Shift</option>
                                    @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}" data-start="{{ $shift->start_time }}" data-end="{{ $shift->end_time }}" data-grace="{{ $shift->grace_minutes ?? 0 }}">{{ $shift->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-danger error-text" data-error="shift_id"></small>
                            </div>
                        </div>
                        <div class="col-4 mt-2">
                            <!-- Attendance Date -->
                            <div class="form-group">
                                <label>Attendance Date</label>
                                <input type="date" id="create_attendance_date" name="attendance_date" class="form-control" required>
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
                                <input type="time" id="create_check_in" name="check_in" class="form-control">
                                <small class="text-danger error-text" data-error="check_in"></small>
                            </div>
                        </div>
                        <div class="col-4 mt-2">
                            <!-- Check Out -->
                            <div class="form-group">
                                <label>Check Out</label>
                                <input type="time" id="create_check_out" name="check_out" class="form-control">
                                <small class="text-danger error-text" data-error="check_out"></small>
                            </div>
                        </div>
                        <div class="col-4 mt-2">
                            <!-- Working Hours -->
                            <div class="form-group">
                                <label>Working Hours</label>
                                <input type="number" step="0.01" id="create_working_hours" name="working_hours" class="form-control" readonly>
                                <small class="text-danger error-text" data-error="working_hours"></small>
                            </div>
                        </div>
                        <div class="col-4 mt-2">
                            <!-- Overtime Hours -->
                            <div class="form-group">
                                <label>Overtime Hours</label>
                                <input type="number" step="0.01" id="create_overtime_hours" name="overtime_hours" class="form-control" readonly>
                                <small class="text-danger error-text" data-error="overtime_hours"></small>
                            </div>
                        </div>
                        <div class="col-4 mt-2">
                            <!-- Late Minutes -->
                            <div class="form-group">
                                <label>Late Minutes</label>
                                <input type="number" id="create_late_minutes" name="late_minutes" class="form-control" readonly>
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
                                    <option value="{{ $shift->id }}" data-start="{{ $shift->start_time }}" data-end="{{ $shift->end_time }}" data-grace="{{ $shift->grace_minutes ?? 0 }}">{{ $shift->name }}</option>
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
                                <input type="number" step="0.01" id="edit_working_hours" name="working_hours" class="form-control" readonly>
                                <small class="text-danger error-text" data-error="working_hours"></small>
                            </div>
                        </div>
                        <div class="col-4 mt-2">
                            <!-- Overtime Hours -->
                            <div class="form-group">
                                <label>Overtime Hours</label>
                                <input type="number" step="0.01" id="edit_overtime_hours" name="overtime_hours" class="form-control" readonly>
                                <small class="text-danger error-text" data-error="overtime_hours"></small>
                            </div>
                        </div>
                        <div class="col-4 mt-2">
                            <!-- Late Minutes -->
                            <div class="form-group">
                                <label>Late Minutes</label>
                                <input type="number" id="edit_late_minutes" name="late_minutes" class="form-control" readonly>
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
    // Build shift data map for quick access
    const shiftData = {};
    $('[data-start]').each(function() {
        const $option = $(this);
        const shiftId = $option.val();
        shiftData[shiftId] = {
            start: $option.data('start'),
            end: $option.data('end'),
            grace: parseInt($option.data('grace')) || 0
        };
    });

    // Build employee to shift mapping
    const employeeShifts = {};
    $('#create_employee_id option').each(function() {
        const shiftId = $(this).data('shift');
        if (shiftId) {
            employeeShifts[$(this).val()] = shiftId;
        }
    });

    // Auto-select shift and calculate when employee is selected (Create Modal)
    $(document).on('change', '.create-employee-select', function() {
        const employeeId = $(this).val();
        if (employeeId && employeeShifts[employeeId]) {
            $('#create_shift_id').val(employeeShifts[employeeId]).change();
        }
    });

    // Auto-select shift and calculate when employee is selected (Edit Modal)
    $(document).on('change', '#edit_employee_id', function() {
        const employeeId = $(this).val();
        if (employeeId && employeeShifts[employeeId]) {
            $('#edit_shift_id').val(employeeShifts[employeeId]).change();
        }
    });

    // Calculate working hours, overtime, and late minutes
    function calculateTimings(checkInStr, checkOutStr, shiftId, attendanceDate, isEditMode = false) {
        const prefix = isEditMode ? 'edit' : 'create';
        
        if (!checkInStr || !checkOutStr || !shiftId || !attendanceDate) {
            $(`#${prefix}_working_hours`).val('');
            $(`#${prefix}_overtime_hours`).val('');
            $(`#${prefix}_late_minutes`).val('');
            return;
        }

        const shift = shiftData[shiftId];
        if (!shift) return;

        // Parse times
        const [checkInHour, checkInMin] = checkInStr.split(':').map(Number);
        const [checkOutHour, checkOutMin] = checkOutStr.split(':').map(Number);
        const [shiftStartHour, shiftStartMin] = shift.start.split(':').map(Number);
        const [shiftEndHour, shiftEndMin] = shift.end.split(':').map(Number);

        // Create date objects
        const dateObj = new Date(attendanceDate);
        const checkInTime = new Date(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate(), checkInHour, checkInMin);
        let checkOutTime = new Date(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate(), checkOutHour, checkOutMin);
        
        const shiftStartTime = new Date(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate(), shiftStartHour, shiftStartMin);
        let shiftEndTime = new Date(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate(), shiftEndHour, shiftEndMin);

        // Handle night shifts
        if (shiftEndTime < shiftStartTime) {
            shiftEndTime.setDate(shiftEndTime.getDate() + 1);
        }
        if (checkOutTime < checkInTime) {
            checkOutTime.setDate(checkOutTime.getDate() + 1);
        }

        // Calculate working hours
        const workingMinutes = (checkOutTime - checkInTime) / (1000 * 60);
        const workingHours = parseFloat((workingMinutes / 60).toFixed(2));

        // Calculate overtime
        let overtimeHours = 0;
        if (workingHours > 8) {
            overtimeHours = parseFloat((workingHours - 8).toFixed(2));
        }

        // Calculate late minutes
        let lateMinutes = 0;
        const graceTime = new Date(shiftStartTime.getTime() + shift.grace * 60000);
        if (checkInTime > graceTime) {
            lateMinutes = Math.round((checkInTime - shiftStartTime) / (1000 * 60));
        }

        // Update fields
        $(`#${prefix}_working_hours`).val(workingHours || '');
        $(`#${prefix}_overtime_hours`).val(overtimeHours || '');
        $(`#${prefix}_late_minutes`).val(lateMinutes || '');
    }

    // Trigger calculations when check-in, check-out, shift, or date changes (Create Modal)
    $(document).on('change', '#create_check_in, #create_check_out, #create_shift_id, #create_attendance_date', function() {
        const prefix = 'create';
        const checkIn = $(`#${prefix}_check_in`).val();
        const checkOut = $(`#${prefix}_check_out`).val();
        const shift = $(`#${prefix}_shift_id`).val();
        const date = $(`#${prefix}_attendance_date`).val();
        calculateTimings(checkIn, checkOut, shift, date, false);
    });

    // Trigger calculations when check-in, check-out, shift, or date changes (Edit Modal)
    $(document).on('change', '#edit_check_in, #edit_check_out, #edit_shift_id, #edit_attendance_date', function() {
        const checkIn = $('#edit_check_in').val();
        const checkOut = $('#edit_check_out').val();
        const shift = $('#edit_shift_id').val();
        const date = $('#edit_attendance_date').val();
        calculateTimings(checkIn, checkOut, shift, date, true);
    });

    // When edit button is clicked
    $(document).on('click', '.editAttendanceBtn', function() {
        let attendanceId = $(this).data('id');

        // Fetch attendance data via AJAX
        $.get('/employee-management/attendances/' + attendanceId + '/edit', function(data) {
            $('#edit_attendance_id').val(data.id);
            $('#edit_employee_id').val(data.employee_id);
            $('#edit_shift_id').val(data.shift_id);
            $('#edit_attendance_date').val(data.attendance_date ? data.attendance_date.split('T')[0] : '');
            $('#edit_status').val(data.status);
            
            // Fix: Extract time properly from datetime string
            if (data.check_in) {
                const checkInDate = new Date(data.check_in);
                const checkInTime = String(checkInDate.getHours()).padStart(2, '0') + ':' + String(checkInDate.getMinutes()).padStart(2, '0');
                $('#edit_check_in').val(checkInTime);
            }
            
            if (data.check_out) {
                const checkOutDate = new Date(data.check_out);
                const checkOutTime = String(checkOutDate.getHours()).padStart(2, '0') + ':' + String(checkOutDate.getMinutes()).padStart(2, '0');
                $('#edit_check_out').val(checkOutTime);
            }
            
            $('#edit_working_hours').val(data.working_hours || '');
            $('#edit_overtime_hours').val(data.overtime_hours || '');
            $('#edit_late_minutes').val(data.late_minutes || '');
            $('#edit_remarks').val(data.remarks);

            // Change action dynamically
            $('#EditForm').attr('action', '/employee-management/attendances/' + attendanceId);

            // Show the edit modal
            $('#editModal').modal('show');
            
            // Trigger calculation after values are set
            setTimeout(() => {
                calculateTimings(
                    $('#edit_check_in').val(),
                    $('#edit_check_out').val(),
                    data.shift_id,
                    data.attendance_date ? data.attendance_date.split('T')[0] : '',
                    true
                );
            }, 100);
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
        modalButton = $('#confirmationModalConfirmBtn')[0];

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
                    $(modalButton).prop('disabled', false);
                    $(modalButton).text(originalText);
                }
            });
        });
    }
</script>

@endsection