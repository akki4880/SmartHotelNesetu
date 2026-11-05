@extends('layouts.master')

@section('title', 'Invoice Templates')
@section('mainbreadcrumb', 'Invoice Management')
@section('subbreadcrumb', 'Invoice Templates')
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

                        <!-- Name -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Template Name</label>
                                <input type="text" name="name" class="form-control"
                                    value="{{ request('name') }}" placeholder="Search template name">
                            </div>
                        </div>

                        <!-- Type -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Type</label>
                                <select name="type" class="form-control">
                                    <option value="">All</option>
                                    <option value="free" {{ request('type') == 'free' ? 'selected' : '' }}>Free</option>
                                    <option value="paid" {{ request('type') == 'paid' ? 'selected' : '' }}>Paid</option>
                                </select>
                            </div>
                        </div>

                        <!-- Default -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Default</label>
                                <select name="is_default" class="form-control">
                                    <option value="">All</option>
                                    <option value="1" {{ request('is_default') == '1' ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ request('is_default') == '0' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-3">
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
                        <div class="col-md-3 ">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                    <a href="{{ url()->current() }}" class="btn btn-secondary">
                                        Reset
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
                <h3 class="mb-0 fw-bolder">Invoice Templates</h3>
                <div class="float-right">
                    @if(auth()->user()->hasPermission('invoice_templates', 'create'))
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

                    @if($templates->count() > 0)

                    <table class="table table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                @if(auth()->user()->hasPermission('invoice_templates', 'view') || 
                                    auth()->user()->hasPermission('invoice_templates', 'edit'))
                                <th>Action</th>
                                @endif
                                <th>Sr</th>
                                <th>Name</th>
                                <th>Preview</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Default</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($templates as $index => $template)
                            <tr>

                                <!-- ACTIONS -->
                                @if(auth()->user()->hasPermission('invoice_templates', 'view') || 
                                    auth()->user()->hasPermission('invoice_templates', 'edit'))
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if(auth()->user()->hasPermission('invoice_templates', 'view'))
                                        <button
                                            class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                            data-url="{{ route('templates.show', $template->id) }}" title="View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('invoice_templates', 'edit'))
                                        <a href="javascript:void(0);"
                                            class="btn one-btn-outline-success btn-icon btn-sm editTemplateBtn btn-loader mx-1"
                                            data-id="{{ $template->id }}"
                                            data-name="{{ $template->name }}"
                                            data-view_file="{{ $template->view_file }}"
                                            data-type="{{ $template->type }}"
                                            data-price="{{ $template->price }}"
                                            data-default_settings='@json($template->default_settings)'
                                            data-description="{{ $template->description }}"
                                            data-is_default="{{ $template->is_default }}"
                                            data-status="{{ $template->status }}"
                                            data-preview_image="{{ $template->preview_image ? asset('storage/'.$template->preview_image) : '' }}"
                                            title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input statusToggle globalStatusToggle"
                                                id="rateSwitch{{ $template->id }}" data-id="{{ $template->id }}" data-url="/invoice-management/templates/{{ $template->id }}/toggle-status"
                                                {{ $template->status ? 'checked' : '' }}>

                                            <label class="custom-control-label" for="rateSwitch{{ $template->id }}"></label>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                                @endif

                                <!-- SR -->
                                <td>
                                    {{ ($templates->currentPage() - 1) * $templates->perPage() + $index + 1 }}
                                </td>

                                <!-- NAME -->
                                <td>{{ $template->name }}</td>

                                <!-- PREVIEW IMAGE -->
                                <td>
                                    @if($template->preview_image)
                                    <a href="{{ asset('storage/'.$template->preview_image) }}" data-fancybox="gallery">
                                        <img src="{{ asset('storage/'.$template->preview_image) }}" width="50">
                                    </a>
                                    @else
                                    <span class="badge badge-secondary">No Image</span>
                                    @endif
                                </td>

                                <!-- TYPE -->
                                <td>
                                    <span class="badge badge-{{ $template->type == 'free' ? 'success' : 'info' }}">
                                        {{ ucfirst($template->type) }}
                                    </span>
                                </td>

                                <!-- PRICE -->
                                <td>
                                    {{ $template->type == 'paid' ? '₹'.$template->price : 'Free' }}
                                </td>

                                <!-- DEFAULT -->
                                <td>
                                    @if($template->is_default)
                                    <span class="badge badge-success">Default</span>
                                    @else
                                    <span class="badge badge-light">No</span>
                                    @endif
                                </td>

                                <!-- STATUS -->
                                <td>
                                    <span class="badge badge-{{ $template->status ? 'success' : 'danger' }}">
                                        {{ $template->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- PAGINATION -->
                    <div class="d-flex justify-content-end mt-3">
                        {{ $templates->links() }}
                    </div>

                    @else
                    No Templates Found
                    @endif

                </div>

            </div>
        </div>
    </div>
</div>
<!-- Create MOdal -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content ">
            <div class="modal-header bg-danger text-white">
                <h4 class="modal-title">
                    Create Invoice Templates
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="CreateForm" action="{{ route('templates.store') }}" method="POST" class="ajax-form"
                data-reset="1" data-modal="#createModal" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="row">

                        <!-- Name -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" required>
                                <small class="text-danger error-text" data-error="name"></small>
                            </div>
                        </div>

                        <!-- View File -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>View File</label>
                                <input type="text" name="view_file" class="form-control"
                                    placeholder="invoice.templates.modern" required>
                                <small class="text-danger error-text" data-error="view_file"></small>
                            </div>
                        </div>

                        <!-- Type -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Type</label>
                                <select name="type" class="form-control" required>
                                    <option value="">Select Type</option>
                                    <option value="free">Free</option>
                                    <option value="paid">Paid</option>
                                </select>
                                <small class="text-danger error-text" data-error="type"></small>
                            </div>
                        </div>

                        <!-- Price -->
                        <div class="col-md-4 mt-2">
                            <div class="form-group">
                                <label>Price</label>
                                <input type="number" step="0.01" name="price" class="form-control"
                                    placeholder="0.00">
                                <small class="text-danger error-text" data-error="price"></small>
                            </div>
                        </div>

                        <!-- Preview Image -->
                        <div class="col-md-4 mt-2">
                            <div class="form-group">
                                <label>Preview Image</label>
                                <input type="file" name="preview_image" class="form-control">
                                <small class="text-danger error-text" data-error="preview_image"></small>
                            </div>
                        </div>

                        <!-- Is Default -->
                        <div class="col-md-4 mt-2">
                            <div class="form-group">
                                <label>Default Template</label>
                                <select name="is_default" class="form-control">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                                <small class="text-danger error-text" data-error="is_default"></small>
                            </div>
                        </div>

                        <!-- Default Settings -->
                        <div class="col-md-4 mt-2">
                            <div class="form-group">
                                <label>Default Settings</label>
                                <textarea name="default_settings" class="form-control" rows="3"
                                    placeholder='{"color":"blue"}'></textarea>
                                <small class="text-danger error-text" data-error="default_settings"></small>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="col-md-4 mt-2">
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                                <small class="text-danger error-text" data-error="description"></small>
                            </div>
                        </div>

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
{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title">Edit Invoice Template</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>

            <form id="EditForm" data-modal="#editModal" method="POST"
                enctype="multipart/form-data" class="ajax-form" data-reset="0" novalidate>
                @csrf
                @method('PUT')

                <div class="modal-body">

                    <input type="hidden" id="edit_template_id" name="template_id">

                    <div class="row">

                        <!-- Name -->
                        <div class="form-group col-md-3">
                            <label>Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                            <small class="text-danger error-text" data-error="name"></small>
                        </div>

                        <!-- View File -->
                        <div class="form-group col-md-3">
                            <label>View File</label>
                            <input type="text" name="view_file" id="edit_view_file"
                                class="form-control" required>
                            <small class="text-danger error-text" data-error="view_file"></small>
                        </div>

                        <!-- Type -->
                        <div class="form-group col-md-3">
                            <label>Type</label>
                            <select name="type" id="edit_type" class="form-control" required>
                                <option value="free">Free</option>
                                <option value="paid">Paid</option>
                            </select>
                            <small class="text-danger error-text" data-error="type"></small>
                        </div>

                        <!-- Price -->
                        <div class="form-group col-md-3">
                            <label>Price</label>
                            <input type="number" name="price" id="edit_price"
                                class="form-control" step="0.01">
                            <small class="text-danger error-text" data-error="price"></small>
                        </div>

                        <!-- Default Settings -->
                        <div class="form-group col-md-6">
                            <label>Default Settings (JSON)</label>
                            <textarea name="default_settings" id="edit_default_settings"
                                class="form-control" rows="4"></textarea>
                            <small class="text-danger error-text" data-error="default_settings"></small>
                        </div>

                        <!-- Description -->
                        <div class="form-group col-md-6">
                            <label>Description</label>
                            <textarea name="description" id="edit_description"
                                class="form-control" rows="4"></textarea>
                            <small class="text-danger error-text" data-error="description"></small>
                        </div>

                        <!-- Preview Image -->
                        <div class="form-group col-md-3">
                            <label>Preview Image</label>

                            <div class="d-flex align-items-center">

                                <!-- File Input -->
                                <input type="file" name="preview_image" id="edit_preview_image" class="form-control">

                                <!-- Eye Icon -->
                                <a href="javascript:void(0);"
                                    id="preview_eye_icon"
                                    class="btn btn-outline-primary btn-sm ml-2 d-none"
                                    data-fancybox="editPreview">
                                    <i class="fa fa-eye"></i>
                                </a>

                            </div>

                            <small class="text-danger error-text" data-error="preview_image"></small>
                        </div>

                        <!-- Default -->
                        <div class="form-group col-md-3">
                            <label>Default Template</label>
                            <select name="is_default" id="edit_is_default" class="form-control">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                            <small class="text-danger error-text" data-error="is_default"></small>
                        </div>

                        <!-- Status -->
                        <div class="form-group col-md-3">
                            <label>Status</label>
                            <select name="status" id="edit_status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <small class="text-danger error-text" data-error="status"></small>
                        </div>

                    </div>
                </div>

                <div class="modal-footer border-top">
                    <button type="reset" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Update Template</button>
                </div>
            </form>
        </div>
    </div>
</div>
@include('layouts.common-view')
<script>
    $(document).on('click', '.editTemplateBtn', function() {
        let btn = $(this);

        $('#edit_template_id').val(btn.data('id'));
        $('#edit_name').val(btn.data('name'));
        $('#edit_view_file').val(btn.data('view_file'));
        $('#edit_type').val(btn.data('type'));
        $('#edit_price').val(btn.data('price'));
        let defaultSettings = btn.data('default_settings');

        if (!defaultSettings) {
            $('#edit_default_settings').val('');
        } else {
            $('#edit_default_settings').val(
                typeof defaultSettings === 'object' ?
                JSON.stringify(defaultSettings, null, 2) :
                defaultSettings
            );
        }
        $('#edit_description').val(btn.data('description'));
        $('#edit_is_default').val(btn.data('is_default'));
        $('#edit_status').val(btn.data('status'));

        let imageUrl = btn.data('preview_image');

        if (imageUrl) {
            $('#preview_eye_icon')
                .attr('href', imageUrl)
                .removeClass('d-none');
        } else {
            $('#preview_eye_icon').addClass('d-none');
        }

        // Reset file input
        $('#edit_preview_image').val('');

        $('#EditForm').attr('action', `/invoice-management/templates/${btn.data('id')}`);
        $('#editModal').modal('show');
    });

    $('#edit_preview_image').on('change', function() {
        if (this.files.length > 0) {
            $('#preview_eye_icon').addClass('d-none');
        }
    });
</script>
@endsection