@extends('layouts.master')

@section('title', 'Guest')
@section('mainbreadcrumb', 'Guest')

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

                        <!-- Guest Name -->
                        <div class="col-md-3">
                            <label>Guest Name</label>
                            <input type="text" name="full_name" class="form-control" value="{{ request('full_name') }}"
                                placeholder="Search name">
                        </div>

                        <!-- Mobile -->
                        <div class="col-md-2">
                            <label>Mobile</label>
                            <input type="text" name="mobile" class="form-control" value="{{ request('mobile') }}"
                                placeholder="Mobile">
                        </div>

                        <!-- Email -->
                        <div class="col-md-2">
                            <label>Email</label>
                            <input type="text" name="email" class="form-control" value="{{ request('email') }}"
                                placeholder="Email">
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
                <h3 class="mb-0 fw-bolder">Guest</h3>
                <div class="float-right">
                    @if(auth()->user()->hasPermission('guests', 'create'))
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
                    @if($guests->count() > 0)
                    <table class="table m-0 table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                @if(auth()->user()->hasPermission('guests', 'view') || auth()->user()->hasPermission('guests', 'edit'))
                                <th>Action</th>@endif
                                <th>Sr</th>
                                <th>Guest Name</th>
                                <th>Mobile</th>
                                <th>Email</th>
                                <th>ID Proofs</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($guests as $index => $guest)
                            <tr>
                                @if(auth()->user()->hasPermission('guests', 'view') || auth()->user()->hasPermission('guests', 'edit'))
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if(auth()->user()->hasPermission('guests', 'view'))
                                        <button
                                            class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader mx-1"
                                            data-url="{{ route('guests.show', $guest->id) }}" title="View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @endif

                                        @if(auth()->user()->hasPermission('guests', 'edit'))

                                        <!-- Edit -->
                                        <a href="javascript:void(0)"
                                            class="btn one-btn-outline-success btn-icon btn-sm editGuestBtn btn-loader"
                                            data-id="{{ $guest->id }}"
                                            data-full_name="{{ $guest->full_name }}"
                                            data-mobile="{{ $guest->mobile }}"
                                            data-email="{{ $guest->email }}"
                                            data-dob="{{ $guest->dob }}"
                                            data-address="{{ $guest->address }}"
                                            data-country="{{ $guest->country_id }}"
                                            data-state="{{ $guest->state_id }}"
                                            data-proofs='@json($guest->idProofs)'>
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                                @endif

                                <td>
                                    {{ ($guests->currentPage() - 1) * $guests->perPage() + $index + 1 }}
                                </td>
                                <td>{{ $guest->full_name }}</td>
                                <td>{{ $guest->mobile }}</td>
                                <td>{{ $guest->email }}</td>
                                <td>
                                    @if($guest->idProofs->count())
                                    @foreach($guest->idProofs as $proof)
                                    <a href="{{ asset('storage/'.$proof->id_file_path) }}"
                                        data-fancybox="guest-{{ $guest->id }}"
                                        data-caption="{{ ucfirst(str_replace('_',' ', $proof->id_type)) }}"
                                        class="badge badge-info mr-1" style="cursor:pointer;">
                                        {{ ucfirst(str_replace('_',' ', $proof->id_type)) }}
                                    </a>
                                    @endforeach
                                    @else
                                    <span class="badge badge-secondary">No ID</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $guests->links() }}
                    </div>
                    @else
                    <p>No Guests found.</p>
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
                <h4 class="modal-title">Create Guest</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="CreateForm" action="{{ route('guests.store') }}" method="POST" class="ajax-form" data-reset="1" data-modal="#createModal" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="modal-body">

                    <div class="row">
                        <div class="form-group col-4">
                            <label>Guest Full Name</label>
                            <input type="text" name="full_name" class="form-control" required>
                            <small class="text-danger error-text" data-error="full_name"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Mobile Number</label>
                            <input type="number" name="mobile" class="form-control" required>
                            <small class="text-danger error-text" data-error="mobile"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                            <small class="text-danger error-text" data-error="email"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Date of birth</label>
                            <input type="date" name="dob" class="form-control">
                            <small class="text-danger error-text" data-error="dob"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Address</label>
                            <textarea name="address" class="form-control" rows="3"></textarea>
                            <small class="text-danger error-text" data-error="address"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Country</label>
                            <select id="country" name="country_id" data-target="#state" class="select2 form-control country-dropdown" onchange="loadStatesByCountry(this)">
                                <option value="">Select Country</option>
                                @foreach($countries as $country)
                                <option value="{{ $country->id }}"
                                    {{ isset($hotel) && $hotel->country == $country->id ? 'selected' : '' }}>
                                    {{ $country->name }}
                                </option>
                                @endforeach
                            </select>
                            <small class="text-danger error-text" data-error="country_id"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>State</label>
                            <select id="state" name="state_id" class="select2 form-control state-dropdown">
                                <option value="">Select State</option>
                            </select>
                            <small class="text-danger error-text" data-error="state_id"></small>
                        </div>

                        <div class="col-12 d-flex justify-content-between align-items-center">
                            <label class="mb-0">Guest ID Proofs</label>
                            <button type="button" class="btn btn-sm btn-success" id="addIdRow">
                                <i class="fa fa-plus"></i> Add
                            </button>
                        </div>

                        <div id="idProofContainer" class="col-12">
                            <!-- ID Row Template -->
                            <div class="row mt-2 id-proof-row">
                                <div class="form-group col-5">
                                    <label>ID Type</label>
                                    <input type="text" name="id_type[]" class="form-control id-type" placeholder="Enter ID type">
                                </div>

                                <div class="form-group col-5">
                                    <label>ID Image</label>
                                    <input type="file" name="id_image[]" class="form-control id-image" disabled>
                                </div>

                                <div class="form-group col-2">
                                    <button type="button" class="btn btn-sm btn-danger removeIdRow mt-4" disabled>
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


{{-- Edit Guest Modal --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title">Edit Guest</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="EditForm" method="POST" class="ajax-form" data-reset="0" data-modal="#editModal" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')

                <div class="modal-body">

                    <input type="hidden" name="id" id="edit_guest_id">

                    <div class="row">
                        <div class="form-group col-4">
                            <label>Full Name</label>
                            <input type="text" name="full_name" id="edit_full_name" class="form-control" required>
                            <small class="text-danger error-text" data-error="full_name"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Mobile</label>
                            <input type="text" name="mobile" id="edit_mobile" class="form-control" required>
                            <small class="text-danger error-text" data-error="mobile"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control">
                            <small class="text-danger error-text" data-error="email"></small>
                        </div>
                        <div class="form-group col-4">
                            <label>Date of birth</label>
                            <input type="date" name="dob" id="edit_dob" class="form-control">
                            <small class="text-danger error-text" data-error="dob"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Address</label>
                            <textarea name="address" id="edit_address" class="form-control" rows="3"></textarea>
                            <small class="text-danger error-text" data-error="address"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>Country</label>
                            <select name="country_id" id="edit_country" data-target="#edit_state" class="select2 form-control country-dropdown" onchange="loadStatesByCountry(this)">
                                <option value="">Select Country</option>
                                @foreach($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger error-text" data-error="country_id"></small>
                        </div>

                        <div class="form-group col-4">
                            <label>State</label>
                            <select name="state_id" id="edit_state" class="select2 form-control state-dropdown">
                                <option value="">Select State</option>
                            </select>
                            <small class="text-danger error-text" data-error="state_id"></small>
                        </div>

                        <div id="editIdProofContainer" class="form-group col-12">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="mb-0">Guest ID Proofs</label>
                                <button type="button" class="btn btn-sm btn-success" id="addEditIdRow">
                                    <i class="fa fa-plus"></i> Add
                                </button>
                            </div>

                        </div>



                    </div>
                </div>

                <div class="modal-footer border-top">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
                    <button class="btn btn-success" type="submit" id="updateGuestBtn">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@include('layouts.common-view')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const container = document.getElementById('idProofContainer');
        const addBtn = document.getElementById('addIdRow');

        // Enable image when user types
        container.addEventListener('input', function(e) {
            if (e.target.classList.contains('id-type')) {

                const row = e.target.closest('.id-proof-row');
                const imageInput = row.querySelector('.id-image');

                if (e.target.value.trim() !== '') {
                    imageInput.disabled = false;
                } else {
                    imageInput.disabled = true;
                    imageInput.value = '';
                }
            }
        });

        // Add new row
        addBtn.addEventListener('click', function() {

            const firstRow = container.querySelector('.id-proof-row');
            const newRow = firstRow.cloneNode(true);

            newRow.querySelector('.id-type').value = '';
            newRow.querySelector('.id-image').value = '';
            newRow.querySelector('.id-image').disabled = true;
            newRow.querySelector('.removeIdRow').disabled = false;

            container.appendChild(newRow);
        });

        // Remove row
        container.addEventListener('click', function(e) {
            if (e.target.closest('.removeIdRow')) {
                e.target.closest('.id-proof-row').remove();
            }
        });

    });


    // Click to Edit Btn
    $(document).on('click', '.editGuestBtn', function() {

        let id = $(this).data('id');

        let modal = $('#editModal'); // ⭐ target only edit modal

        modal.find('.error-text').text('');

        modal.find('#edit_guest_id').val(id);
        modal.find('#edit_full_name').val($(this).data('full_name'));
        modal.find('#edit_mobile').val($(this).data('mobile'));
        modal.find('#edit_email').val($(this).data('email'));
        modal.find('#edit_dob').val($(this).data('dob'));
        modal.find('#edit_address').val($(this).data('address'));

        modal.find('#edit_country').val($(this).data('country')).trigger('change');

        setTimeout(() => {
            modal.find('#edit_state').val($(this).data('state')).trigger('change');
        }, 500);

        modal.find('#EditForm').attr('action', '/guests/' + id);

        // ⭐ proofs
        let proofs = $(this).data('proofs');

        if (typeof proofs === 'string') {
            proofs = JSON.parse(proofs);
        }

        loadProofs(proofs || []);

        modal.modal('show');
    });


    function loadProofs(proofs = []) {

        let html = `
    <div class="col-12 d-flex justify-content-between align-items-center mb-2">
        <label class="mb-0">Guest ID Proofs</label>
        <button type="button" class="btn btn-sm btn-success" id="addEditIdRow">
            <i class="fa fa-plus"></i> Add
        </button>
    </div>
    `;

        // if empty → create one default row
        if (!proofs || proofs.length === 0) {
            html += getEmptyRow(true); // true = cannot delete
            $('#editIdProofContainer').html(html);
            return;
        }

        proofs.forEach(function(proof, index) {

            html += `
        <div class="row mt-2 id-proof-row" data-id="${proof.id}">
            <div class="form-group col-4">
                <input type="text" class="form-control" value="${proof.id_type}" readonly>
                <input type="hidden" name="id_type[]" value="${proof.id_type}">
            </div>

            <div class="form-group col-4">
                <input type="file" name="id_image[]" class="form-control replaceFile">
            </div>

            <div class="form-group col proof-actions">
                <a href="/storage/${proof.id_file_path}" target="_blank" class="btn btn-sm btn-info">
                    <i class="fa fa-eye"></i>
                </a>

                <a href="/storage/${proof.id_file_path}" download class="btn btn-sm btn-success">
                    <i class="fa fa-download"></i>
                </a>

                <button type="button" class="btn btn-sm btn-danger deleteProofBtn">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </div>`;
        });

        $('#editIdProofContainer').html(html);
    }


    function getEmptyRow(disableDelete = false) {

        let disabled = disableDelete ? 'disabled' : '';

        return `
    <div class="row mt-2 id-proof-row">
        <div class="form-group col-4">
            <input type="text" name="id_type[]" class="form-control id-type-input"
                   placeholder="Enter ID type">
        </div>

        <div class="form-group col-4">
            <input type="file" name="id_image[]" class="form-control id-file-input" disabled>
        </div>

        <div class="form-group col-2">
            <button type="button" class="btn btn-sm btn-danger removeIdRow" ${disabled}>
                <i class="fa fa-trash"></i>
            </button>
        </div>
    </div>
    `;
    }
    $(document).on('input', '.id-type-input', function() {

        let row = $(this).closest('.id-proof-row');
        let fileInput = row.find('.id-file-input');

        if ($(this).val().trim() !== '') {
            fileInput.prop('disabled', false);
        } else {
            fileInput.prop('disabled', true);
            fileInput.val(''); // clear file if text removed
        }
    });


    $(document).on('click', '.deleteProofBtn', function() {

        let row = $(this).closest('.id-proof-row');
        let totalRows = $('#editIdProofContainer .id-proof-row').length;

        if (totalRows <= 1) {
            toastr.warning('At least one ID proof is required');
            return;
        }

        row.remove();
    });

    $(document).on('click', '#addEditIdRow', function() {
        $('#editIdProofContainer').append(getEmptyRow());
    });

    $(document).on('change', '.replaceFile', function() {
        $(this).closest('.id-proof-row').find('.proof-actions a').hide();
    });


    $(document).on('click', '.removeIdRow', function() {

        let totalRows = $('#editIdProofContainer .id-proof-row').length;

        if (totalRows <= 1) {
            toastr.warning('At least one ID proof is required');
            return;
        }

        $(this).closest('.id-proof-row').remove();
    });
</script>

@endsection