@extends('layouts.master')

@section('title', 'Leaves')
@section('mainbreadcrumb', 'Leaves')

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

                        <!-- Employee -->
                        <div class="col-md-3">
                            <label>Employee</label>
                            <select name="employee_id" class="form-control select2">
                                <option value="">All Employees</option>
                                @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_code }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Leave Type -->
                        <div class="col-md-2">
                            <label>Leave Type</label>
                            <select name="leave_type" class="form-control select2">
                                <option value="">All Types</option>
                                <option value="sick" {{ request('leave_type') == 'sick' ? 'selected' : '' }}>Sick Leave</option>
                                <option value="casual" {{ request('leave_type') == 'casual' ? 'selected' : '' }}>Casual Leave</option>
                                <option value="paid" {{ request('leave_type') == 'paid' ? 'selected' : '' }}>Paid Leave</option>
                                <option value="emergency" {{ request('leave_type') == 'emergency' ? 'selected' : '' }}>Emergency Leave</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="col-md-2">
                            <label>Status</label>
                            <select name="status" class="form-control select2">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <!-- From Date -->
                        <div class="col-md-2">
                            <label>From Date</label>
                            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                        </div>

                        <!-- To Date -->
                        <div class="col-md-2">
                            <label>To Date</label>
                            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                        </div>

                        <!-- Buttons -->
                        <div class="col-md-1 d-flex align-items-end">
                            <a href="{{ url()->current() }}" class="btn btn-secondary">Reset</a>
                            <button class="btn btn-info mx-2">Filter</button>
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
                <h3 class="mb-0 fw-bolder">Leaves</h3>
                <div class="float-right">
                    @if(auth()->user()->hasPermission('leaves', 'create'))
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
                    @if($leaves->count() > 0)
                    <table class="table m-0 table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                @if(auth()->user()->hasPermission('leaves', 'view') || auth()->user()->hasPermission('leaves', 'edit') || auth()->user()->hasPermission('leaves', 'approve') || auth()->user()->hasPermission('leaves', 'reject') || auth()->user()->hasPermission('leaves', 'delete'))
                                <th>Action</th>@endif
                                <th>Sr</th>
                                <th>Employee</th>
                                <th>Leave Type</th>
                                <th>Reason</th>
                                <th>From Date</th>
                                <th>To Date</th>
                                <th>Days</th>
                                <th>Status</th>
                                <th>Applied On</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($leaves as $index => $leave)
                            <tr>
                                @if(auth()->user()->hasPermission('leaves', 'view') || auth()->user()->hasPermission('leaves', 'edit') || auth()->user()->hasPermission('leaves', 'approve') || auth()->user()->hasPermission('leaves', 'reject') || auth()->user()->hasPermission('leaves', 'delete'))
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if(auth()->user()->hasPermission('leaves', 'view'))
                                        <button
                                            class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader mx-1"
                                            data-url="{{ route('leaves.show', $leave->id) }}" title="View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @endif
                                        @if($leave->status === 'pending')
                                        @if(auth()->user()->hasPermission('leaves', 'edit'))
                                        <button
                                            class="btn btn-outline-success btn-icon btn-sm edit-btn mx-1"
                                            data-id="{{ $leave->id }}"
                                            title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('leaves', 'delete'))
                                        <button
                                            class="btn one-btn-outline-danger btn-icon btn-sm delete-btn mx-1"
                                            data-id="{{ $leave->id }}"
                                            data-title="Delete Leave"
                                            data-message="Are you sure you want to delete this leave application?"
                                            data-color="danger"
                                            title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('leaves', 'approve'))
                                        <button
                                            class="btn btn-outline-success btn-icon btn-sm mx-1 approve-btn"
                                            data-id="{{ $leave->id }}"
                                            data-title="Approve Leave"
                                            data-message="Are you sure you want to approve this leave?"
                                            data-color="success"
                                            title="Approve">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('leaves', 'reject'))
                                        <button
                                            class="btn btn-outline-danger btn-icon btn-sm mx-1 reject-btn"
                                            data-id="{{ $leave->id }}"
                                            title="Reject">
                                            <i class="fa fa-times"></i>
                                        </button>
                                        @endif
                                        @endif
                                    </div>
                                </td>
                                @endif

                                <td>
                                    {{ ($leaves->currentPage() - 1) * $leaves->perPage() + $index + 1 }}
                                </td>
                                <td>
                                    {{ $leave->employee->first_name }} {{ $leave->employee->last_name }}<br>
                                    <small class="text-muted">{{ $leave->employee->employee_code }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $leave->leave_type === 'sick' ? 'warning' : ($leave->leave_type === 'casual' ? 'info' : ($leave->leave_type === 'paid' ? 'success' : 'danger')) }}">
                                        {{ ucfirst($leave->leave_type) }}
                                    </span>
                                </td>
                                <td>{{ $leave->reason ?? '-' }}</td>
                                <td>{{ formatDateTime($leave->from_date, 'd M Y') }}</td>
                                <td>{{ formatDateTime($leave->to_date, 'd M Y') }}</td>
                                <td>{{ $leave->total_days }}</td>
                                <td>
                                    <span class="badge badge-{{ $leave->status === 'approved' ? 'success' : ($leave->status === 'rejected' ? 'danger' : ($leave->status === 'pending' ? 'warning' : 'secondary')) }}">
                                        {{ ucfirst($leave->status) }}
                                    </span>
                                </td>
                                <td>{{ formatDateTime($leave->created_at, 'd M Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $leaves->links() }}
                    </div>
                    @else
                    <p>No Leaves found.</p>
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
                <h4 class="modal-title">Apply Leave</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="CreateForm" action="{{ route('leaves.store') }}" method="POST" class="ajax-form" data-reset="1" data-modal="#createModal" novalidate>
                @csrf
                <div class="modal-body">

                    <div class="row">
                        <div class="form-group col-6">
                            <label>Employee</label>
                            <select name="employee_id" class="form-control select2" id="employeeSelect" required>
                                <option value="">Select Employee</option>
                                @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">
                                    {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_code }})
                                </option>
                                @endforeach
                            </select>
                            <small class="text-danger error-text" data-error="employee_id"></small>
                        </div>

                        <div class="form-group col-6">
                            <label>Leave Type</label>
                            <select name="leave_type" class="form-control select2" required>
                                <option value="">Select Leave Type</option>
                                <option value="sick">Sick Leave</option>
                                <option value="casual">Casual Leave</option>
                                <option value="paid">Paid Leave</option>
                                <option value="emergency">Emergency Leave</option>
                            </select>
                            <small class="text-danger error-text" data-error="leave_type"></small>
                        </div>

                        <div class="form-group col-6">
                            <label>From Date</label>
                            <input type="date" name="from_date" class="form-control" required>
                            <small class="text-danger error-text" data-error="from_date"></small>
                        </div>

                        <div class="form-group col-6">
                            <label>To Date</label>
                            <input type="date" name="to_date" class="form-control" required>
                            <small class="text-danger error-text" data-error="to_date"></small>
                        </div>

                        <div class="form-group col-12">
                            <label>Reason</label>
                            <textarea name="reason" class="form-control" rows="3" placeholder="Please provide reason for leave"></textarea>
                            <small class="text-danger error-text" data-error="reason"></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="reset" class="btn btn-secondary" id="clearAddBtn">Clear</button>
                    <button type="submit" class="btn btn-danger" id="saveBtn">Apply Leave</button>
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
                <h4 class="modal-title">Edit Leave</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="EditForm" method="POST" class="ajax-form" data-reset="0" data-modal="#editModal" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-6">
                            <label>Employee</label>
                            <select name="employee_id" class="form-control select2" id="editEmployeeSelect" required readonly>
                                <option value="">Select Employee</option>
                                @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">
                                    {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_code }})
                                </option>
                                @endforeach
                            </select>
                            <small class="text-danger error-text" data-error="employee_id"></small>
                        </div>

                        <div class="form-group col-6">
                            <label>Leave Type</label>
                            <select name="leave_type" class="form-control select2" required>
                                <option value="">Select Leave Type</option>
                                <option value="sick">Sick Leave</option>
                                <option value="casual">Casual Leave</option>
                                <option value="paid">Paid Leave</option>
                                <option value="emergency">Emergency Leave</option>
                            </select>
                            <small class="text-danger error-text" data-error="leave_type"></small>
                        </div>

                        <div class="form-group col-6">
                            <label>From Date</label>
                            <input type="date" name="from_date" class="form-control" required>
                            <small class="text-danger error-text" data-error="from_date"></small>
                        </div>

                        <div class="form-group col-6">
                            <label>To Date</label>
                            <input type="date" name="to_date" class="form-control" required>
                            <small class="text-danger error-text" data-error="to_date"></small>
                        </div>

                        <div class="form-group col-12">
                            <label>Reason</label>
                            <textarea name="reason" class="form-control" rows="3" placeholder="Please provide reason for leave"></textarea>
                            <small class="text-danger error-text" data-error="reason"></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Update Leave</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h4 class="modal-title">Reject Leave</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="RejectForm" method="POST" class="ajax-form" data-reset="0" data-modal="#rejectModal" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Reason for Rejection</label>
                        <textarea name="admin_remark" class="form-control" rows="3" placeholder="Please provide reason for rejection" required></textarea>
                        <small class="text-danger error-text" data-error="admin_remark"></small>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Leave</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('layouts.common-view')
@include('layouts.confirmationModal')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let actionUrl = '';
        let actionMethod = 'POST';
        let modalButton = null; // store confirm button reference

        // --- Modal open function ---
        function openConfirmationModal(url, title = 'Confirm Action', message = 'Are you sure you want to proceed?', method = 'POST') {
            actionUrl = url;
            actionMethod = method;
            $('#confirmationModalTitle').text(title);
            $('#confirmationModalBody').html('<p>' + message + '</p>');
            $('#confirmationModal').modal('show');
            modalButton = $('#confirmationModalConfirmBtn')[0]; // store DOM button reference
        }

        // --- Loader functions ---
        function showButtonLoader(button) {
            if (!button) return;
            button.disabled = true;
            const originalText = button.innerHTML;
            button.setAttribute('data-original-text', originalText);
            button.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Processing...
        `;
        }

        function resetButtonLoader(button) {
            if (!button) return;
            const originalText = button.getAttribute('data-original-text');
            if (originalText) button.innerHTML = originalText;
            button.disabled = false;
        }

        // --- Confirm button inside modal ---
        $('#confirmationModalConfirmBtn').on('click', function() {
            if (!actionUrl) return;

            showButtonLoader(modalButton);

            $.ajax({
                url: actionUrl,
                type: actionMethod,
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#confirmationModal').modal('hide');
                    resetButtonLoader(modalButton);

                    if (response.status) {
                        toastr.success(response.message);
                        location.reload(); // reload table or page
                    } else {
                        toastr.error(response.message || 'Action failed');
                    }
                },
                error: function(xhr, status, error) {
                    resetButtonLoader(modalButton);
                    console.error(error);
                    toastr.error('Something went wrong.');
                }
            });
        });

        // Approve Leave
        $(document).on('click', '.approve-btn', function() {
            const leaveId = $(this).data('id');
            const title = $(this).data('title') || 'Approve Leave';
            const message = $(this).data('message') || 'Are you sure you want to approve this leave?';
            const color = $(this).data('color') || 'success';
            $('#confirmationModal .modal-header').removeClass('bg-primary bg-warning bg-danger bg-success one-bg-danger').addClass('bg-' + color);
            $('#confirmationModalConfirmBtn').removeClass('btn-primary btn-warning btn-danger btn-success one-btn-danger').addClass('btn-' + color);
            openConfirmationModal(`/employee-management/leaves/${leaveId}/approve`, title, message);
        });

        // Edit Leave
        $(document).on('click', '.edit-btn', function() {
            let leaveId = $(this).data('id');

            // Fetch leave data
            $.ajax({
                url: `/employee-management/leaves/${leaveId}/edit`,
                type: 'GET',
                success: function(response) {
                    // Set form action
                    $('#EditForm').attr('action', `/employee-management/leaves/${leaveId}`);

                    // Populate fields
                    $('#editModal [name="employee_id"]').val(response.employee_id).trigger('change');
                    $('#editModal [name="leave_type"]').val(response.leave_type).trigger('change');
                    $('#editModal [name="from_date"]').val(response.from_date);
                    $('#editModal [name="to_date"]').val(response.to_date);
                    $('#editModal [name="reason"]').val(response.reason);

                    $('#editModal').modal('show');
                },
                error: function(xhr) {
                    toastr.error('Failed to load leave data');
                }
            });
        });

        // Delete Leave
        $(document).on('click', '.delete-btn', function() {
            const leaveId = $(this).data('id');
            const title = $(this).data('title') || 'Delete Leave';
            const message = $(this).data('message') || 'Are you sure you want to delete this leave?';
            const color = $(this).data('color') || 'one-bg-danger';
            $('#confirmationModal .modal-header').removeClass('bg-primary bg-warning bg-danger bg-success').addClass('one-bg-danger');
            $('#confirmationModalConfirmBtn').removeClass('btn-primary btn-warning btn-danger btn-success').addClass('btn-' + color);
            openConfirmationModal(`/employee-management/leaves/${leaveId}`, title, message, 'DELETE');
        });

        // Reject Leave
        $(document).on('click', '.reject-btn', function() {
            let leaveId = $(this).data('id');

            $('#RejectForm').attr('action', `/employee-management/leaves/${leaveId}/reject`);
            $('#rejectModal').modal('show');
        });

        function toggleFilter() {
            const filterRow = document.getElementById('filterRow');
            if (filterRow.style.display === 'none' || filterRow.style.display === '') {
                filterRow.style.display = 'block';
            } else {
                filterRow.style.display = 'none';
            }
        }

    });

    function toggleFilter() {
        const filterRow = document.getElementById('filterRow');
        if (filterRow.style.display === 'none' || filterRow.style.display === '') {
            filterRow.style.display = 'block';
        } else {
            filterRow.style.display = 'none';
        }
    }
</script>

@endsection