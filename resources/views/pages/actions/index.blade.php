@extends('layouts.master')

@section('title', 'Action')
@section('mainbreadcrumb', 'Action')

@section('content')

@php
// Get all request parameters except _token or page
$filters = request()->except(['_token', 'page']);

// Check if any filter has a non-empty value
$hasFilter = collect($filters)->filter(fn($value) => !empty($value))->isNotEmpty();
@endphp

<!-- fillter form  -->
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
                            <!-- Action Name -->
                            <div class="form-group">
                                <label>Action</label>
                                <div class="input-group">
                                    <input type="text" name="name" class="form-control" value="{{ request('name') }}"
                                        placeholder="Action Name">
                                </div>
                                <small class="text-danger error-text" data-error="action"></small>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All</option>
                                    <option value="1" {{ request('status')=='1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status')=='0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>


                        <div class="col-4">
                            <!-- Buttons -->
                            <div class="d-flex">
                                <div class="mx-2">
                                    <a href="{{ url()->current() }}" class="btn btn-secondary btn-loader mt-4">
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

<!-- table data  -->
<div class="row" id="data-table-container">
    <div class="col-sm-12">
        <div class="card mb-g">
            <div class="card-header  d-flex justify-content-between align-items-center">
                <h3 class="mb-0 fw-bolder">Actions</h3>
                <div class="float-right">
                    @if(auth()->user()->hasPermission('actions', 'create'))
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
                    @if($actions->count() > 0)
                    <table class="table m-0 table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                @if(
                                auth()->user()->hasPermission('actions', 'view') ||
                                auth()->user()->hasPermission('actions', 'edit')  
                                )
                                <th style="width:90px;">Action</th>
                                @endif
                                <th>Sr</th>
                                <th>Name</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($actions as $index => $action)
                            <tr>
                                <!-- Action -->
                                 @if(
                                auth()->user()->hasPermission('actions', 'view') ||
                                auth()->user()->hasPermission('actions', 'edit') 
                                )
                                <td> 
                                    <div class="d-flex align-items-center ">
                                        @if(auth()->user()->hasPermission('actions', 'view') )
                                        <!-- <a href="javascript:void(0);" class="btn one-btn-outline-danger btn-icon btn-sm" title="Delete"> <i class="fa-solid fa-trash"></i> </a> -->
                                        <button class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                            data-url="{{ route('actions.show', $action->id) }}" title="View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('actions', 'edit') )  
                                        <a href="javascript:void(0)"
                                            class="btn btn-outline-success btn-icon btn-sm btn-loader editActionBtn mx-2"
                                            data-id="{{ $action->id }}" data-status="{{ $action->status == 1 ? 1 : 0 }}"
                                            data-name="{{ $action->action_name }}" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox"
                                                class="custom-control-input statusToggle globalStatusToggle"
                                                id="switch{{ $action->id }}" data-id="{{ $action->id }}"
                                                data-model="Action" {{ $action->status ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="switch{{ $action->id }}"></label>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                                @endif
                                <td>{{ ($actions->currentPage() - 1) * $actions->perPage() + $index + 1 }}</td>
                                <td>{{ $action->action_name }}</td>
                                <td>
                                    <span class="badge {{ $action->status ? 'badge-success' : 'badge-danger' }}">
                                        {{ $action->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-end mt-3">
                        {{ $actions->links() }}
                    </div>

                    @else
                    <p>No actions found.</p>
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
                    Create Action
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="CreateForm" action="{{ route('actions.store') }}" method="POST" class="ajax-form" data-reset="1"
                data-modal="#createModal">
                @csrf

                <div class="modal-body">
                    <div class="form-group">
                        <label>Action Name</label>
                        <input type="text" name="action_name" class="form-control" required data-rules="min:7">
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>



                <div class="modal-footer border-top">
                    <button type="reset" class="btn btn-secondary">
                        Clear
                    </button>

                    <button type="submit" class="btn btn-danger">
                        Save
                    </button>
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
                    Edit Action
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="EditForm" method="POST" class="ajax-form" data-modal="#editModal" novalidate>

                @csrf
                @method('PUT')

                <div class="modal-body">

                    <input type="hidden" id="edit_action_id" name="action_id">

                    <div class="form-group">
                        <label>Action Name</label>
                        <input type="text" id="edit_action_name" name="action_name" class="form-control"
                            data-rules="required|min:3|max:100">
                    </div>

                    {{-- Status --}}
                    <div class="form-group ">
                        <label>Status</label>
                        <select name="status" id="edit_status" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer border-top">

                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Close
                    </button>

                    <button type="submit" class="btn btn-success">
                        Update
                    </button>

                </div>

            </form>
        </div>
    </div>
</div>

@include('layouts.common-view')

<script>
    // When edit button is clicked
    $(document).on('click', '.editActionBtn', function () {
        let btn = $(this);
        $('#EditForm').attr('action', '/actions/' + btn.data('id'));

        $('#edit_action_id').val(btn.data('id'));
        $('#edit_action_name').val(btn.data('name'));
        $('#edit_status').val(btn.data('status'));

        $('#editModal').modal('show');
    });
</script>


@endsection