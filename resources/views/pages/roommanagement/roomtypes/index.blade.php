@extends('layouts.master')

@section('title', 'Room types')
@section('mainbreadcrumb', 'Room Management')
@section('subbreadcrumb', 'Room types')

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
                        <div class="col-md-3 mb-3">
                            <!-- Roomtype -->
                            <div class="form-group">
                                <label>Room Type</label>
                                <div class="input-group">
                                    <input type="text" name="room_type" class="form-control"
                                        value="{{ request('room_type') }}" placeholder="Room Type">
                                
                                </div>
                                <small class="text-danger error-text" data-error="hotel"></small>
                            </div>
                        </div>
                        <!-- Status -->
                        <div class="col-md-3 mb-3">
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
                        <div class="col-md-3">
                            <div class="d-flex mt-4">
                                <div class="mx-2">
                                    <a href="{{ url()->current() }}" class="btn btn-secondary">
                                        Reset
                                    </a>
                                </div>
                                <div>
                                    <button type="submit" class="btn btn-info">
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
                <h3 class="mb-0 fw-bolder">Room types</h3>
                <div class="float-right">
                    @if(auth()->user()->hasPermission('room_types', 'create'))
                    <button class="btn btn-danger btn-sm btn-loader" data-toggle="modal" data-target="#createModal"><i
                            class="fa-solid fa-plus"></i>
                        Create</button>
                    @endif
                    <button class="btn btn-info btn-sm" onclick="toggleFilter()"><i class="fa-solid fa-filter"></i>
                        Filter
                    </button>

                </div>
            </div>

            <div class="card-body">
                <div class="frame-wrap p-0 border-0 m-0" id="table-container">
                    @if($roomtypes->count() > 0)
                    <table class="table table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                @if(auth()->user()->hasPermission('room_types', 'view') || 
                                    auth()->user()->hasPermission('room_types', 'edit'))
                                <th width="120">Action</th>
                                @endif
                                <th>Roomtype</th>
                                <th>Description</th>
                                <th>Base Guest allowed</th>
                                <th>Extra Guest allowed</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($roomtypes as $roomtype)
                            <tr>
                                @if(auth()->user()->hasPermission('room_types', 'view') || 
                                    auth()->user()->hasPermission('room_types', 'edit'))
                                <td>
                                    <div class="d-flex align-items-center" >
                                        @if(auth()->user()->hasPermission('room_types', 'view'))
                                        <button data-toggle="tooltip"
                                            class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                            data-url="{{ route('room-types.show', $roomtype->id) }}" title="View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('room_types', 'edit'))
                                        <a class="btn one-btn-outline-success btn-icon btn-sm editBtn btn-loader mx-1"
                                            data-id="{{ $roomtype->id }}"
                                            data-name="{{ $roomtype->name }}"
                                            data-description="{{ $roomtype->description }}"
                                            data-base_guests="{{ $roomtype->default_base_guests }}"
                                            data-extra_guests="{{ $roomtype->default_max_extra_guests }}"
                                            data-status="{{ $roomtype->status }}">
                                            <i class="fa fa-edit"></i>
                                        </a>

                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input statusToggle globalStatusToggle"
                                                id="switch{{ $roomtype->id }}" data-id="{{ $roomtype->id }}" data-model="RoomType"
                                                {{ $roomtype->status ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="switch{{ $roomtype->id }}"></label>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                                @endif

                                <td>{{ $roomtype->name }}</td>
                                <td>{{$roomtype?->description}}</td>
                                <td>{{$roomtype?->default_base_guests}}</td>
                                <td>{{$roomtype?->default_max_extra_guests}}</td>
                                <td>
                                    <span class="badge {{ $roomtype->status ? 'badge-success' : 'badge-danger' }}">
                                        {{ $roomtype->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-3">
                        {{ $roomtypes->withQueryString()->links() }}
                    </div>

                    @else
                    <p class="text-muted">No roomtypes found.</p>
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
                    Create Room Type
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="CreateForm" action="{{ route('room-types.store') }}" method="POST" class="ajax-form" data-reset="1" data-modal="#createModal" novalidate> 
                @csrf
                <div class="modal-body row">

                    <div class="form-group col-6">
                        <label>Room Type Name </label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Deluxe, Suite" required>
                        <small class="text-danger error-text" data-error="name"></small>
                    </div>

                    <div class="form-group col-6">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <small class="text-danger error-text" data-error="status"></small>
                    </div>

                    <div class="form-group col-12">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"
                            placeholder="Optional description"></textarea>
                        <small class="text-danger error-text" data-error="description"></small>
                    </div>

                    <div class="form-group col-6">
                        <label>Base Guest allowed </label>
                        <input type="number" name="default_base_guests" class="form-control" placeholder="e.g. 3"
                            required>
                        <small class="text-danger error-text" data-error="default_base_guests"></small>
                    </div>

                    <div class="form-group col-6">
                        <label>Extra Guest allowed </label>
                        <input type="number" name="default_max_extra_guests" class="form-control" placeholder="e.g. 0"
                            required>
                        <small class="text-danger error-text" data-error="default_max_extra_guests"></small>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="reset" class="btn btn-secondary" id="clearaddBtn">Clear</button>
                    <button type="submit" class="btn btn-danger" id="saveRoomTypeBtn">Save</button>
                </div>
            </form>

        </div>
    </div>
</div>
<!-- editModal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header bg-success text-white">
                <h4 class="modal-title">Edit Room Type</h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="EditForm" method="POST" class="ajax-form" data-reset="0" data-modal="#editModal" novalidate>
                @csrf
                @method('PUT')

                <div class="modal-body row">

                    <input type="hidden" id="roomtype_id" name="roomtype_id">

                    <div class="form-group col-6">
                        <label>Room Type Name </label>
                        <input type="text" id="name" name="name" class="form-control" required>
                        <small class="text-danger error-text" data-error="name"></small>
                    </div>

                    <div class="form-group col-6">
                        <label>Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <small class="text-danger error-text" data-error="status"></small>
                    </div>

                    <div class="form-group col-12">
                        <label>Description</label>
                        <textarea id="description" name="description" class="form-control"></textarea>
                        <small class="text-danger error-text" data-error="description"></small>
                    </div>

                    <div class="form-group col-6">
                        <label>Base Guests allowed</label>
                        <input type="number" id="base_guests" name="default_base_guests" class="form-control" required>
                        <small class="text-danger error-text" data-error="default_base_guests"></small>
                    </div>

                    <div class="form-group col-6">
                        <label>Extra Guests allowed</label>
                        <input type="number" id="extra_guests" name="default_max_extra_guests" class="form-control" required>
                        <small class="text-danger error-text" data-error="default_max_extra_guests"></small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="reset" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success" id="saveRoomTypedetailsBtn">Update</button>
                </div>
            </form>

        </div>
    </div>
</div>
@include('layouts.common-view')
<script>

    $(document).on('click', '.editBtn', function() {

        let btn = $(this);

        $('#roomtype_id').val(btn.data('id'));
        $('#name').val(btn.data('name'));
        $('#description').val(btn.data('description'));
        $('#base_guests').val(btn.data('base_guests'));
        $('#extra_guests').val(btn.data('extra_guests'));
        $('#status').val(btn.data('status'));

        $('#saveRoomTypeBtn').text('Update');
        $('#EditForm').attr('action', `/room-management/room-types/${btn.data('id')}`);

        $('#editModal').modal('show');
    });
    // Update Room Type Details
</script>

@endsection