@extends('layouts.master')

@section('title', 'Employee')
@section('mainbreadcrumb', 'Employee')

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

                        <!-- Employee Name -->
                        <div class="col-md-3">
                            <label>Employee Name</label>
                            <input type="text" name="first_name" class="form-control" value="{{ request('first_name') }}"
                                placeholder="Search name">
                        </div>

                        <!-- Phone -->
                        <div class="col-md-2">
                            <label>Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ request('phone') }}"
                                placeholder="Phone">
                        </div>

                        <!-- Email -->
                        <div class="col-md-2">
                            <label>Email</label>
                            <input type="text" name="email" class="form-control" value="{{ request('email') }}"
                                placeholder="Email">
                        </div>

                        <!-- Department -->
                        <div class="col-md-2">
                            <label>Department</label>
                            <select name="department_id" class="form-control select2">
                                <option value="">All Departments</option>
                                @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="col-md-3 d-flex align-items-end">
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
                <h3 class="mb-0 fw-bolder">Employee</h3>
                <div class="float-right">
                    <a class="btn btn-primary btn-sm btn-loader" href="{{ route('employees.birthdayChart') }}">
                        <i class="fa-solid fa-cake-candles"></i> Birthday Chart
                    </a>
                    @if(auth()->user()->hasPermission('employees', 'create'))
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
                    @if($employees->count() > 0)
                    <table class="table m-0 table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                @if(auth()->user()->hasPermission('employees', 'view') || auth()->user()->hasPermission('employees', 'edit'))
                                <th>Action</th>
                                @endif
                                <th>Sr</th>
                                <th>Employee Code</th>
                                <th>Employee Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Documents</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($employees as $index => $employee)
                            <tr>
                                @if(auth()->user()->hasPermission('employees', 'view') || auth()->user()->hasPermission('employees', 'edit'))
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if(auth()->user()->hasPermission('employees', 'view'))
                                        <button
                                            class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader mx-1"
                                            data-url="{{ route('employees.show', $employee->id) }}" title="View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('employees', 'edit'))
                                        <button type="button"
                                            class="btn btn-outline-warning btn-icon btn-sm assignWeeklyOffBtn btn-loader mx-1"
                                            data-url="{{ route('employees.weekly-offs.store', $employee->id) }}"
                                            data-employee_id="{{ $employee->id }}"
                                            title="Assign Weekly Off">
                                            <i class="fa-solid fa-calendar-week"></i>
                                        </button>
                                        <!-- Edit -->
                                        <a href="javascript:void(0)"
                                            class="btn one-btn-outline-success btn-icon btn-sm editEmployeeBtn btn-loader"
                                            data-id="{{ $employee->id }}"
                                            data-employee_code="{{ $employee->employee_code }}"
                                            data-first_name="{{ $employee->first_name }}"
                                            data-last_name="{{ $employee->last_name }}"
                                            data-phone="{{ $employee->phone }}"
                                            data-email="{{ $employee->email }}"
                                            data-dob="{{ $employee->dob }}"
                                            data-address="{{ $employee->address }}"
                                            data-joining_date="{{ $employee->joining_date }}"
                                            data-department_id="{{ $employee->department_id }}"
                                            data-shift_id="{{ $employee->shift_id }}"
                                            data-user_role_id="{{ $employee->user_role_id }}"
                                            data-employment_type="{{ $employee->employment_type }}"
                                            data-salary_type="{{ $employee->salary_type }}"
                                            data-salary_amount="{{ $employee->salary_amount }}"
                                            data-status="{{ $employee->status }}"
                                            data-documents='@json($employee->documents)'>
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                                @endif

                                <td>
                                    {{ ($employees->currentPage() - 1) * $employees->perPage() + $index + 1 }}
                                </td>
                                <td>{{ $employee->employee_code }}</td>
                                <td>{{ $employee->first_name }} {{ $employee->last_name }}</td>
                                <td>{{ $employee->phone }}</td>
                                <td>{{ $employee->email }}</td>
                                <td>{{ $employee->department->name ?? 'N/A' }}</td>
                                <td>
                                    @if($employee->documents->count())
                                    @foreach($employee->documents as $document)
                                    <a href="{{ asset('storage/'.$document->file_path) }}"
                                        data-fancybox="employee-{{ $employee->id }}"
                                        data-caption="{{ ucfirst(str_replace('_',' ', $document->document_name)) }}"
                                        class="badge badge-info mr-1" style="cursor:pointer;">
                                        {{ ucfirst(str_replace('_',' ', $document->document_name)) }}
                                    </a>
                                    @endforeach
                                    @else
                                    <span class="badge badge-secondary">No Documents</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $employees->links() }}
                    </div>
                    @else
                    <p>No Employees found.</p>
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
                <h4 class="modal-title">Create Employee</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="CreateForm" action="{{ route('employees.store') }}" method="POST" class="ajax-form" data-reset="1" data-modal="#createModal" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="modal-body">

                    <div class="row">
                        <div class="form-group col-4">
                            <label>Employee Code</label>
                            <input type="text" name="employee_code" class="form-control" required>
                            <small class="text-danger error-text" data-error="employee_code"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                            <small class="text-danger error-text" data-error="first_name"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Last Name</label>
                            <input type="text" name="last_name" class="form-control">
                            <small class="text-danger error-text" data-error="last_name"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Phone Number</label>
                            <input type="text" name="phone" class="form-control" required>
                            <small class="text-danger error-text" data-error="phone"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control">
                            <small class="text-danger error-text" data-error="email"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Date of Birth</label>
                            <input type="date" name="dob" class="form-control">
                            <small class="text-danger error-text" data-error="dob"></small>
                        </div>

                        <div class="form-group col-6">
                            <label>Address</label>
                            <textarea name="address" class="form-control" rows="3"></textarea>
                            <small class="text-danger error-text" data-error="address"></small>
                        </div>

                        <div class="form-group col-6">
                            <label>Joining Date</label>
                            <input type="date" name="joining_date" class="form-control" required>
                            <small class="text-danger error-text" data-error="joining_date"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Department</label>
                            <select name="department_id" class="select2 form-control">
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger error-text" data-error="department_id"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Shift</label>
                            <select name="shift_id" class="select2 form-control" required>
                                <option value="">Select Shift</option>
                                @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger error-text" data-error="shift_id"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Role</label>
                            <select name="user_role_id" class="select2 form-control" required>
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->role_name }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger error-text" data-error="user_role_id"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Employment Type</label>
                            <select name="employment_type" class="select2 form-control" required>
                                <option value="">Select Type</option>
                                <option value="full_time">Full Time</option>
                                <option value="part_time">Part Time</option>
                                <option value="contract">Contract</option>
                            </select>
                            <small class="text-danger error-text" data-error="employment_type"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Salary Type</label>
                            <select name="salary_type" class="select2 form-control" required>
                                <option value="">Select Salary Type</option>
                                <option value="monthly">Monthly</option>
                                <option value="daily">Daily</option>
                                <option value="hourly">Hourly</option>
                            </select>
                            <small class="text-danger error-text" data-error="salary_type"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Salary Amount</label>
                            <input type="number" name="salary_amount" class="form-control" step="0.01" required>
                            <small class="text-danger error-text" data-error="salary_amount"></small>
                        </div>

                        <div class="col-12 d-flex justify-content-between align-items-center">
                            <label class="mb-0">Employee Documents</label>
                            <button type="button" class="btn btn-sm btn-success" id="addDocumentRow">
                                <i class="fa fa-plus"></i> Add
                            </button>
                        </div>

                        <div id="documentContainer" class="col-12">
                            <!-- Document Row Template -->
                            <div class="row mt-2 document-row">
                                <div class="form-group col-5">
                                    <label>Document Type</label>
                                    <input type="text" name="document_name[]" class="form-control document-type" placeholder="Enter document type">
                                </div>

                                <div class="form-group col-5">
                                    <label>Document File</label>
                                    <input type="file" name="document_file[]" class="form-control document-file" disabled>
                                </div>

                                <div class="form-group col-2">
                                    <button type="button" class="btn btn-sm btn-danger removeDocumentRow mt-4" disabled>
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
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


{{-- Edit Employee Modal --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title">Edit Employee</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="EditForm" method="POST" class="ajax-form" data-reset="0" data-modal="#editModal" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')

                <div class="modal-body">

                    <input type="hidden" name="id" id="edit_employee_id">

                    <div class="row">
                        <div class="form-group col-4">
                            <label>Employee Code</label>
                            <input type="text" name="employee_code" id="edit_employee_code" class="form-control" readonly>
                            <small class="text-danger error-text" data-error="employee_code"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>First Name</label>
                            <input type="text" name="first_name" id="edit_first_name" class="form-control" required>
                            <small class="text-danger error-text" data-error="first_name"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Last Name</label>
                            <input type="text" name="last_name" id="edit_last_name" class="form-control">
                            <small class="text-danger error-text" data-error="last_name"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Phone</label>
                            <input type="text" name="phone" id="edit_phone" class="form-control" required>
                            <small class="text-danger error-text" data-error="phone"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control">
                            <small class="text-danger error-text" data-error="email"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Date of Birth</label>
                            <input type="date" name="dob" id="edit_dob" class="form-control">
                            <small class="text-danger error-text" data-error="dob"></small>
                        </div>

                        <div class="form-group col-6">
                            <label>Address</label>
                            <textarea name="address" id="edit_address" class="form-control" rows="3"></textarea>
                            <small class="text-danger error-text" data-error="address"></small>
                        </div>

                        <div class="form-group col-6">
                            <label>Joining Date</label>
                            <input type="date" name="joining_date" id="edit_joining_date" class="form-control" required>
                            <small class="text-danger error-text" data-error="joining_date"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Department</label>
                            <select name="department_id" id="edit_department_id" class="select2 form-control" required>
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger error-text" data-error="department_id"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Shift</label>
                            <select name="shift_id" id="edit_shift_id" class="select2 form-control" required>
                                <option value="">Select Shift</option>
                                @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger error-text" data-error="shift_id"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Role</label>
                            <select name="user_role_id" id="edit_user_role_id" class="select2 form-control" required>
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->role_name }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger error-text" data-error="user_role_id"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Employment Type</label>
                            <select name="employment_type" id="edit_employment_type" class="select2 form-control" required>
                                <option value="">Select Type</option>
                                <option value="full_time">Full Time</option>
                                <option value="part_time">Part Time</option>
                                <option value="contract">Contract</option>
                            </select>
                            <small class="text-danger error-text" data-error="employment_type"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Salary Type</label>
                            <select name="salary_type" id="edit_salary_type" class="select2 form-control" required>
                                <option value="">Select Salary Type</option>
                                <option value="monthly">Monthly</option>
                                <option value="daily">Daily</option>
                                <option value="hourly">Hourly</option>
                            </select>
                            <small class="text-danger error-text" data-error="salary_type"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Salary Amount</label>
                            <input type="number" name="salary_amount" id="edit_salary_amount" class="form-control" step="0.01" required>
                            <small class="text-danger error-text" data-error="salary_amount"></small>
                        </div>

                        <div id="editDocumentContainer" class="form-group col-12">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="mb-0">Employee Documents</label>
                                <button type="button" class="btn btn-sm btn-success" id="addEditDocumentRow">
                                    <i class="fa fa-plus"></i> Add
                                </button>
                            </div>
                            <!-- Hidden input to track deleted document IDs -->
                            <input type="hidden" id="deleted_documents" name="deleted_documents" value="">

                        </div>



                    </div>
                </div>

                <div class="modal-footer border-top">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
                    <button class="btn btn-success" type="submit" id="updateEmployeeBtn">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@include('layouts.common-view')

<!-- Weekly Off Modal -->
<div class="modal fade" id="weeklyOffModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h4 class="modal-title">Assign Weekly Off</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="WeeklyOffForm" method="POST" class="ajax-form" data-reset="1" data-modal="#weeklyOffModal" novalidate>
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="employee_id" id="weekly_off_employee_id">
                    <div class="form-group">
                        <label>Day of Week</label>
                        <select name="day_of_week" id="weekly_off_day_of_week" class="form-control" required>
                            <option value="">Select Day</option>
                            <option value="monday">Monday</option>
                            <option value="tuesday">Tuesday</option>
                            <option value="wednesday">Wednesday</option>
                            <option value="thursday">Thursday</option>
                            <option value="friday">Friday</option>
                            <option value="saturday">Saturday</option>
                            <option value="sunday">Sunday</option>
                        </select>
                        <small class="text-danger error-text" data-error="day_of_week"></small>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const container = document.getElementById('documentContainer');
        const addBtn = document.getElementById('addDocumentRow');

        // Enable file when user types
        container.addEventListener('input', function(e) {
            if (e.target.classList.contains('document-type')) {

                const row = e.target.closest('.document-row');
                const fileInput = row.querySelector('.document-file');

                if (e.target.value.trim() !== '') {
                    fileInput.disabled = false;
                } else {
                    fileInput.disabled = true;
                    fileInput.value = '';
                }
            }
        });

        // Add new row
        addBtn.addEventListener('click', function() {

            const firstRow = container.querySelector('.document-row');
            const newRow = firstRow.cloneNode(true);

            newRow.querySelector('.document-type').value = '';
            newRow.querySelector('.document-file').value = '';
            newRow.querySelector('.document-file').disabled = true;
            newRow.querySelector('.removeDocumentRow').disabled = false;

            container.appendChild(newRow);
        });

        // Remove row
        container.addEventListener('click', function(e) {
            if (e.target.closest('.removeDocumentRow')) {
                e.target.closest('.document-row').remove();
            }
        });

    });


    // Click to Edit Btn
    $(document).on('click', '.editEmployeeBtn', function() {

        let id = $(this).data('id'); 

        let modal = $('#editModal'); // ⭐ target only edit modal

        modal.find('.error-text').text('');

        modal.find('#edit_employee_id').val(id);
        modal.find('#edit_employee_code').val($(this).data('employee_code'));
        modal.find('#edit_first_name').val($(this).data('first_name'));
        modal.find('#edit_last_name').val($(this).data('last_name'));
        modal.find('#edit_phone').val($(this).data('phone'));
        modal.find('#edit_email').val($(this).data('email'));
        modal.find('#edit_dob').val($(this).data('dob'));
        modal.find('#edit_address').val($(this).data('address'));
        modal.find('#edit_joining_date').val($(this).data('joining_date'));
        modal.find('#edit_department_id').val($(this).data('department_id')).trigger('change');
        modal.find('#edit_shift_id').val($(this).data('shift_id')).trigger('change');
        modal.find('#edit_user_role_id').val($(this).data('user_role_id')).trigger('change');
        modal.find('#edit_employment_type').val($(this).data('employment_type')).trigger('change');
        modal.find('#edit_salary_type').val($(this).data('salary_type')).trigger('change');
        modal.find('#edit_salary_amount').val($(this).data('salary_amount'));

        modal.find('#EditForm').attr('action', '/employee-management/employees/' + id);

        // ⭐ documents
        let documents = $(this).data('documents');

        if (typeof documents === 'string') {
            documents = JSON.parse(documents);
        }

        loadDocuments(documents || []);

        modal.modal('show');
    });


    function loadDocuments(documents = []) {

        let html = `
    <div class="col-12 d-flex justify-content-between align-items-center mb-2">
        <label class="mb-0">Employee Documents</label>
        <button type="button" class="btn btn-sm btn-success" id="addEditDocumentRow">
            <i class="fa fa-plus"></i> Add
        </button>
    </div>
    `;

        // if empty → create one default row
        if (!documents || documents.length === 0) {
            html += getEmptyRow(true); // true = cannot delete
            $('#editDocumentContainer').html(html);
            return;
        }

        documents.forEach(function(document, index) {

            html += `
        <div class="row mt-2 document-row" data-id="${document.id}">
            <div class="form-group col-4">
                <input type="text" class="form-control" value="${document.document_name}" readonly>
                <input type="hidden" name="document_name[]" value="${document.document_name}">
            </div>

            <div class="form-group col-4">
                <input type="file" name="document_file[]" class="form-control replaceFile">
            </div>

            <div class="form-group col proof-actions">
                <a href="/${document.file_path}" target="_blank" class="btn btn-sm btn-info">
                    <i class="fa fa-eye"></i>
                </a>

                <a href="/${document.file_path}" download class="btn btn-sm btn-success">
                    <i class="fa fa-download"></i>
                </a>

                <button type="button" class="btn btn-sm btn-danger deleteDocumentBtn">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </div>`;
        });

        $('#editDocumentContainer').html(html);
    }


    function getEmptyRow(disableDelete = false) {

        let disabled = disableDelete ? 'disabled' : '';

        return `
    <div class="row mt-2 document-row">
        <div class="form-group col-4">
            <input type="text" name="document_name[]" class="form-control document-type-input"
                   placeholder="Enter document type">
        </div>

        <div class="form-group col-4">
            <input type="file" name="document_file[]" class="form-control document-file-input" disabled>
        </div>

        <div class="form-group col-2">
            <button type="button" class="btn btn-sm btn-danger removeDocumentRow" ${disabled}>
                <i class="fa fa-trash"></i>
            </button>
        </div>
    </div>
    `;
    }
    $(document).on('input', '.document-type-input', function() {

        let row = $(this).closest('.document-row');
        let fileInput = row.find('.document-file-input');

        if ($(this).val().trim() !== '') {
            fileInput.prop('disabled', false);
        } else {
            fileInput.prop('disabled', true);
            fileInput.val(''); // clear file if text removed
        }
    });


    $(document).on('click', '.deleteDocumentBtn', function() {

        let row = $(this).closest('.document-row');
        let totalRows = $('#editDocumentContainer .document-row').length;

        if (totalRows <= 1) {
            toastr.warning('At least one document is required');
            return;
        }

        // Track deleted document ID if it exists
        let docId = row.data('id');
        if (docId) {
            let deletedDocs = $('#deleted_documents').val();
            let deletedArray = deletedDocs ? deletedDocs.split(',') : [];
            if (!deletedArray.includes(String(docId))) {
                deletedArray.push(docId);
            }
            $('#deleted_documents').val(deletedArray.join(','));
        }

        row.remove();
    });

    $(document).on('click', '#addEditDocumentRow', function() {
        $('#editDocumentContainer').append(getEmptyRow());
    });

    $(document).on('change', '.replaceFile', function() {
        $(this).closest('.document-row').find('.proof-actions a').hide();
    });


    $(document).on('click', '.removeDocumentRow', function() {

        let totalRows = $('#editDocumentContainer .document-row').length;

        if (totalRows <= 1) {
            toastr.warning('At least one document is required');
            return;
        }

        $(this).closest('.document-row').remove();
    });

    // Assign weekly off button
    $(document).on('click', '.assignWeeklyOffBtn', function() {
        let employeeId = $(this).data('employee_id');
        let actionUrl = $(this).data('url');

        $('#weekly_off_employee_id').val(employeeId);
        $('#WeeklyOffForm').attr('action', actionUrl);
        $('#weekly_off_day_of_week').val('');
        $('#weeklyOffModal').modal('show');
    });
</script>

@endsection