@extends('layouts.master')

@section('title', 'Holidays')
@section('mainbreadcrumb', 'Employee Management')
@section('subbreadcrumb', 'Holidays')
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

                        <!-- Holiday Name -->
                        <div class="col-4">
                            <div class="form-group">
                                <label>Holiday Name</label>
                                <input type="text" name="name" class="form-control"
                                    value="{{ request('name') }}" placeholder="Holiday Name">
                            </div>
                        </div>

                        <!-- Date -->
                        <div class="col-3">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" name="date" class="form-control"
                                    value="{{ request('date') }}">
                            </div>
                        </div>

                        <!-- Type -->
                        <div class="col-3">
                            <div class="form-group">
                                <label>Type</label>
                                <select name="is_optional" class="form-control">
                                    <option value="">Select Type</option>
                                    <option value="0" {{ request('is_optional') === '0' ? 'selected' : '' }}>Mandatory</option>
                                    <option value="1" {{ request('is_optional') === '1' ? 'selected' : '' }}>Optional</option>
                                </select>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="col-4">
                            <div class="d-flex">
                                <div class="mx-2">
                                    <a href="{{ url()->current() }}" class="btn btn-secondary mt-4">
                                        Reset
                                    </a>
                                </div>
                                <div>
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
            <div class="card-header  d-flex justify-content-between align-items-center">
                <h3 class="mb-0 fw-bolder">Holidays</h3>
                <div class="float-right">
                    @if(auth()->user()->hasPermission('holidays', 'create'))
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
                    @if(!$holidays->isEmpty())
                    <table class="table m-0 table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                @if(auth()->user()->hasPermission('holidays', 'view') || auth()->user()->hasPermission('holidays', 'edit'))
                                <th>Action</th>@endif
                                <th>Sr</th>
                                <th>Name</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($holidays as $holiday)
                            <tr>
                                @if(auth()->user()->hasPermission('holidays', 'view') || auth()->user()->hasPermission('holidays', 'edit'))
                                <td class="w-auto">
                                    <div class="d-flex">
                                        @if(auth()->user()->hasPermission('holidays', 'view'))
                                        <button
                                            class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                            data-url="{{ route('holidays.show', $holiday->id) }}" title="View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('holidays', 'edit'))
                                        <a href="javascript:void(0);"
                                            class="btn one-btn-outline-success btn-icon btn-sm editHolidayBtn btn-loader mx-1"
                                            data-id="{{ $holiday->id }}" data-name="{{ $holiday->name }}"
                                            data-date="{{ $holiday->date->format('Y-m-d') }}" data-description="{{ $holiday->description }}"
                                            data-is-optional="{{ $holiday->is_optional }}" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <div class="custom-control custom-switch mt-1 ml-1" >
                                            <input type="checkbox"
                                                class="custom-control-input statusToggle globalStatusToggle"
                                                id="switch{{ $holiday->id }}" data-id="{{ $holiday->id }}"
                                                data-url="/employee-management/holidays/{{ $holiday->id }}/toggle-status" {{
                                            $holiday->is_optional ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="switch{{ $holiday->id }}"></label>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                                @endif
                                <td>{{ $holidays->firstItem() + $loop->index }}</td>
                                <td>{{ $holiday->name }}</td>
                                <td>{{ $holiday->date->format('d M Y') }}</td>
                                <td>
                                    @if($holiday->is_optional)
                                    <span class="badge badge-warning">Optional</span>
                                    @else
                                    <span class="badge badge-danger">Mandatory</span>
                                    @endif
                                </td>
                                <td>{{ $holiday->description ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-end mt-3">
                        {{ $holidays->links() }}
                    </div>
                    @else
                    No Holidays found
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
                    Create Holiday
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="CreateForm" action="{{ route('holidays.store') }}" method="POST" class="ajax-form"
                data-reset="1" data-modal="#createModal" novalidate>
                @csrf
                <div class="modal-body">

                    <!-- Holiday Name -->
                    <div class="form-group">
                        <label>Holiday Name</label>
                        <div class="input-group">
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <small class="text-danger error-text" data-error="name"></small>
                    </div>

                    <!-- Date -->
                    <div class="form-group">
                        <label>Date</label>
                        <div class="input-group">
                            <input type="date" name="date" class="form-control" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                        </div>
                        <small class="text-danger error-text" data-error="date"></small>
                    </div>

                    <!-- Is Optional -->
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_optional" name="is_optional" value="1">
                            <label class="custom-control-label" for="is_optional">
                                Optional Holiday
                            </label>
                        </div>
                        <small class="text-muted">Check if this is an optional holiday</small>
                    </div>

                    <!-- Holiday Description -->
                    <div class="form-group">
                        <label>Description</label>
                        <div class="input-group">
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <small class="text-danger error-text" data-error="description"></small>
                    </div>

                </div>
                <div class="modal-footer border-top">
                    <button type="reset" class="btn btn-secondary" id="clearaddBtn">Clear</button>
                    <button type="submit" class="btn btn-danger" id="saveRoleBtn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title">
                    Edit Holiday
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

                        <!-- Holiday Name -->
                        <div class="form-group col-6">
                            <label>Holiday Name</label>
                            <div class="input-group">
                                <input type="text" name="name" id="edit_holiday_name" class="form-control" required>
                            </div>
                            <small class="text-danger error-text" data-error="name"></small>
                        </div>

                        <!-- Date -->
                        <div class="form-group col-6">
                            <label>Date</label>
                            <div class="input-group">
                                <input type="date" name="date" id="edit_date" class="form-control" required>
                            </div>
                            <small class="text-danger error-text" data-error="date"></small>
                        </div>

                    </div>

                    <!-- Is Optional -->
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="edit_is_optional" name="is_optional" value="1">
                            <label class="custom-control-label" for="edit_is_optional">
                                Optional Holiday
                            </label>
                        </div>
                        <small class="text-muted">Check if this is an optional holiday</small>
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label>Description</label>
                        <div class="input-group">
                            <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                        </div>
                        <small class="text-danger error-text" data-error="description"></small>
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
@include('layouts.confirmationModal')
<script>
    // When edit button is clicked
    $(document).on('click', '.editHolidayBtn', function() {

        let holidayId = $(this).data('id');
        let holidayName = $(this).data('name');
        let date = $(this).data('date');
        let description = $(this).data('description');
        let isOptional = $(this).data('is-optional');

        let form = $('#EditForm');

        form.attr('action', '/employee-management/holidays/' + holidayId);

        $('#edit_holiday_name').val(holidayName);
        $('#edit_date').val(date);
        $('#edit_description').val(description);
        $('#edit_is_optional').prop('checked', isOptional == 1);

        $('#editModal').modal('show');
    });
</script>


@endsection