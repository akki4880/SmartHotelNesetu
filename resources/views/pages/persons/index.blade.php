@extends('layouts.master')

@section('title', 'Persons')
@section('mainbreadcrumb', 'Persons')

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
                <h3 class="mb-0 fw-bolder text-white"> <i class="fa-solid fa-filter"></i> Filter Persons</h3>
            </div>
            <div class="card-body">
                <!-- FILTER FORM (HIDDEN BY DEFAULT) -->
                <form method="GET" action="{{ url()->current() }}" class="mb-3">
                    <div class="row">

                        <!-- Full Name -->
                        <div class="col-md-3">
                            <label>Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="{{ request('full_name') }}"
                                placeholder="Search name">
                        </div>

                        <!-- Mobile -->
                        <div class="col-md-2">
                            <label>Mobile</label>
                            <input type="text" name="mobile" class="form-control" value="{{ request('mobile') }}"
                                placeholder="Mobile">
                        </div>

                        <!-- Document ID -->
                        <div class="col-md-2">
                            <label>Document ID</label>
                            <input type="text" name="document_id" class="form-control" value="{{ request('document_id') }}"
                                placeholder="Document ID">
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
                <h3 class="mb-0 fw-bolder">Persons</h3>
                <div class="float-right">
                    <button class="btn btn-danger btn-sm btn-loader" data-toggle="modal" data-target="#createModal"><i class="fa-solid fa-plus"></i> Create</button>
                    <button id="filterBtn" class="btn btn-info btn-sm" onclick="toggleFilter()"><i class="fa-solid fa-filter"></i> Filter</button>
                </div>
            </div>

            <div class="card-body">
                <div class="frame-wrap p-0 border-0 m-0" id="table-container">
                    @if($persons->count() > 0)
                    <table class="table m-0 table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white"><tr>
                            <th>Action</th><th>Sr</th><th>Full Name</th><th>Gender</th><th>Mobile</th><th>Date of Birth</th><th>Document ID</th><th>Documents</th>
                        </tr></thead>
                        <tbody>
                        @foreach($persons as $index => $person)
                        <tr>
                            <td><div class="d-flex align-items-center">
                                <button class="btn btn-outline-primary btn-icon btn-sm view-btn mx-1" data-url="{{ route('persons.show', $person->id) }}" title="View"><i class="fa fa-eye"></i></button>
                                <button class="btn btn-outline-success btn-icon btn-sm editPersonBtn mx-1"
                                    data-id="{{ $person->id }}" data-full_name="{{ $person->full_name }}" data-gender="{{ $person->gender }}"
                                    data-mobile="{{ $person->mobile }}" data-dob="{{ $person->dob ? $person->dob->format('Y-m-d') : '' }}"
                                    data-address="{{ $person->address }}" data-document_id="{{ $person->document_id }}" title="Edit"><i class="fa-solid fa-pen-to-square"></i></button>
                                <button class="btn btn-outline-danger btn-icon btn-sm delete-btn mx-1" data-url="{{ route('persons.destroy', $person->id) }}" title="Delete"><i class="fa fa-trash"></i></button>
                            </div></td>
                            <td>{{ ($persons->currentPage() - 1) * $persons->perPage() + $index + 1 }}</td>
                            <td>{{ $person->full_name }}</td><td>{{ $person->gender }}</td><td>{{ $person->mobile ?: '-' }}</td>
                            <td>{{ $person->dob ? $person->dob->format('Y-m-d') : '-' }}</td><td>{{ $person->document_id ?: '-' }}</td>
                            <td>@foreach($person->documents ?? [] as $document)<a href="{{ asset($document) }}" target="_blank" class="badge badge-info mr-1">View</a>@endforeach@if(empty($person->documents))<span class="badge badge-secondary">No Document</span>@endif</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-end mt-3">{{ $persons->links() }}</div>
                    @else
                    <p>No Persons found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
{{-- Create Person Modal --}}
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
        <div class="modal-header bg-danger text-white"><h4 class="modal-title">Create Person</h4><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <form action="{{ route('persons.store') }}" method="POST" class="ajax-form" data-reset="1" data-modal="#createModal" enctype="multipart/form-data">
            @csrf
            <div class="modal-body"><div class="row">
                <div class="form-group col-md-4"><label>Full Name *</label><input type="text" name="full_name" class="form-control" required><small class="text-danger error-text" data-error="full_name"></small></div>
                <div class="form-group col-md-4"><label>Gender *</label><select name="gender" class="form-control" required><option value="">Select</option><option>Male</option><option>Female</option><option>Other</option></select><small class="text-danger error-text" data-error="gender"></small></div>
                <div class="form-group col-md-4"><label>Mobile</label><input type="text" name="mobile" class="form-control"><small class="text-danger error-text" data-error="mobile"></small></div>
                <div class="form-group col-md-4"><label>Date of Birth</label><input type="date" name="dob" class="form-control"></div>
                <div class="form-group col-md-4"><label>Document ID</label><input type="text" name="document_id" class="form-control"><small class="text-danger error-text" data-error="document_id"></small></div>
                <div class="form-group col-md-4"><label>Documents</label><input type="file" name="documents[]" class="form-control" multiple accept="image/*"></div>
                <div class="form-group col-12"><label>Address</label><textarea name="address" class="form-control" rows="3"></textarea></div>
            </div></div>
            <div class="modal-footer"><button type="reset" class="btn btn-secondary">Clear</button><button type="submit" class="btn btn-danger">Save</button></div>
        </form>
    </div></div>
</div>


{{-- Edit Person Modal --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
        <div class="modal-header bg-success text-white"><h4 class="modal-title">Edit Person</h4><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <form id="EditForm" method="POST" class="ajax-form" data-reset="0" data-modal="#editModal" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="modal-body"><div class="row">
                <div class="form-group col-md-4"><label>Full Name *</label><input type="text" name="full_name" id="edit_full_name" class="form-control" required><small class="text-danger error-text" data-error="full_name"></small></div>
                <div class="form-group col-md-4"><label>Gender *</label><select name="gender" id="edit_gender" class="form-control" required><option>Male</option><option>Female</option><option>Other</option></select><small class="text-danger error-text" data-error="gender"></small></div>
                <div class="form-group col-md-4"><label>Mobile</label><input type="text" name="mobile" id="edit_mobile" class="form-control"><small class="text-danger error-text" data-error="mobile"></small></div>
                <div class="form-group col-md-4"><label>Date of Birth</label><input type="date" name="dob" id="edit_dob" class="form-control"></div>
                <div class="form-group col-md-4"><label>Document ID</label><input type="text" name="document_id" id="edit_document_id" class="form-control"><small class="text-danger error-text" data-error="document_id"></small></div>
                <div class="form-group col-md-4"><label>Add Documents</label><input type="file" name="documents[]" class="form-control" multiple accept="image/*"></div>
                <div class="form-group col-12"><label>Address</label><textarea name="address" id="edit_address" class="form-control" rows="3"></textarea></div>
            </div></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button type="submit" class="btn btn-success">Update</button></div>
        </form>
    </div></div>
</div>
@include('layouts.common-view')
<script>
    document.addEventListener('DOMContentLoaded', function() {

    });


    // Click to Edit Btn
    $(document).on('click', '.editPersonBtn', function () {
        const button = $(this);
        $('#EditForm').attr('action', '/persons/' + button.data('id'));
        $('#edit_full_name').val(button.data('full_name'));
        $('#edit_gender').val(button.data('gender'));
        $('#edit_mobile').val(button.data('mobile'));
        $('#edit_dob').val(button.data('dob'));
        $('#edit_document_id').val(button.data('document_id'));
        $('#edit_address').val(button.data('address'));
        $('#editModal').modal('show');
    });
</script>

@endsection