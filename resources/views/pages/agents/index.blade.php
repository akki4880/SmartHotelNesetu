@extends('layouts.master')

@section('title', 'Agents')
@section('mainbreadcrumb', 'Agents')

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
                        <div class="col-3">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control"
                                    value="{{ request('name') }}" placeholder="Agent Name">
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-3">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="text" name="email" class="form-control"
                                    value="{{ request('email') }}" placeholder="Email">
                            </div>
                        </div>

                        <!-- Mobile -->
                        <div class="col-3">
                            <div class="form-group">
                                <label>Mobile</label>
                                <input type="text" name="mobile" class="form-control"
                                    value="{{ request('mobile') }}" placeholder="Mobile">
                            </div>
                        </div>

                        <!-- Company -->
                        <div class="col-3">
                            <div class="form-group">
                                <label>Company</label>
                                <input type="text" name="company_name" class="form-control"
                                    value="{{ request('company_name') }}" placeholder="Company">
                            </div>
                        </div>

                        <!-- Commission Type -->
                        <div class="col-3 mt-3">
                            <div class="form-group">
                                <label>Commission Type</label>
                                <select name="commission_type" class="form-control">
                                    <option value="">Select Type</option>
                                    <option value="percentage" {{ request('commission_type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                    <option value="fixed" {{ request('commission_type') == 'fixed' ? 'selected' : '' }}>Fixed</option>
                                </select>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-3 mt-3">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="">Select Status</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="col-4 mt-4">
                            <div class="d-flex mt-3">
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
            <div class="card-header  d-flex justify-content-between align-items-center">
                <h3 class="mb-0 fw-bolder">Agents</h3>
                <div class="float-right">
                    @if(auth()->user()->hasPermission('agents', 'create'))
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
                    @if($agents->count() > 0)
                    <table class="table m-0 table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                @if(auth()->user()->hasPermission('agents', 'edit') || auth()->user()->hasPermission('agents', 'view'))
                                <th>Action</th>@endif
                                <th>Sr</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Company</th>
                                <th>Commission</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($agents as $index => $agent)
                            <tr>
                                @if(auth()->user()->hasPermission('agents', 'edit') || auth()->user()->hasPermission('agents', 'view'))
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if(auth()->user()->hasPermission('agents', 'view'))
                                        <button
                                            class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                            data-url="{{ route('agents.show', $agent->id) }}" title="View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('agents', 'edit'))
                                        <!-- Edit -->
                                        <a href="javascript:void(0);"
                                            class="btn one-btn-outline-success btn-icon btn-sm editAgentBtn btn-loader mx-1"
                                            data-id="{{ $agent->id }}"
                                            data-name="{{ $agent->name }}"
                                            data-email="{{ $agent->email }}"
                                            data-mobile="{{ $agent->mobile }}"
                                            data-company="{{ $agent->company_name }}"
                                            data-type="{{ $agent->commission_type }}"
                                            data-value="{{ $agent->commission_value }}"
                                            data-status="{{ $agent->status }}"
                                            title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>

                                        <!-- Status -->
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox"
                                                class="custom-control-input globalStatusToggle"
                                                id="agentSwitch{{ $agent->id }}"
                                                data-id="{{ $agent->id }}"
                                                data-model="Agent"
                                                {{ $agent->status ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="agentSwitch{{ $agent->id }}"></label>
                                        </div>
                                        @endif

                                    </div>
                                </td>
                                @endif

                                <!-- Sr -->
                                <td>{{ ($agents->currentPage() - 1) * $agents->perPage() + $index + 1 }}</td>

                                <!-- Basic Info -->
                                <td>{{ $agent->name }}</td>
                                <td>{{ $agent->email ?? '-' }}</td>
                                <td>{{ $agent->mobile ?? '-' }}</td>
                                <td>{{ $agent->company_name ?? '-' }}</td>

                                <!-- Commission -->
                                <td>
                                    @if($agent->commission_type == 'percentage')
                                    <span class="badge badge-info">{{ $agent->commission_value }} %</span>
                                    @else
                                    <span class="badge badge-warning">₹ {{ number_format($agent->commission_value, 2) }}</span>
                                    @endif
                                </td>

                                <!-- Status -->
                                <td>
                                    @if($agent->status)
                                    <span class="badge badge-success">Active</span>
                                    @else
                                    <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $agents->links() }}
                    </div>
                    @else
                    <p>No agents found.</p>
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
                    Create Agent
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="CreateForm" action="{{ route('agents.store') }}" method="POST"
                class="ajax-form" data-reset="1" data-modal="#createModal" novalidate>
                @csrf

                <div class="modal-body row">

                    <!-- Agent Name -->
                    <div class="form-group col-6">
                        <label>Agent Name</label>
                        <div class="input-group">
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <small class="text-danger error-text" data-error="name"></small>
                    </div>

                    <!-- Email -->
                    <div class="form-group col-6">
                        <label>Email</label>
                        <div class="input-group">
                            <input type="email" name="email" class="form-control">
                        </div>
                        <small class="text-danger error-text" data-error="email"></small>
                    </div>

                    <!-- Mobile -->
                    <div class="form-group col-6">
                        <label>Mobile</label>
                        <div class="input-group">
                            <input type="text" name="mobile" class="form-control">
                        </div>
                        <small class="text-danger error-text" data-error="mobile"></small>
                    </div>

                    <!-- Company -->
                    <div class="form-group col-6">
                        <label>Company Name</label>
                        <div class="input-group">
                            <input type="text" name="company_name" class="form-control" required>
                        </div>
                        <small class="text-danger error-text" data-error="company_name"></small>
                    </div>

                    <!-- Commission Type -->
                    <div class="form-group col-6">
                        <label>Commission Type</label>
                        <div class="input-group">
                            <select name="commission_type" class="form-control" required>
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount</option>
                            </select>
                        </div>
                        <small class="text-danger error-text" data-error="commission_type"></small>
                    </div>

                    <!-- Commission Value -->
                    <div class="form-group col-6">
                        <label>Commission Value</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="commission_value" class="form-control" value="0" required>
                        </div>
                        <small class="text-danger error-text" data-error="commission_value"></small>
                    </div>

                    <!-- Status -->
                    <div class="form-group col-6">
                        <label>Status</label>
                        <div class="input-group">
                            <select name="status" class="form-control" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <small class="text-danger error-text" data-error="status"></small>
                    </div>

                </div>

                <div class="modal-footer border-top">
                    <button type="reset" class="btn btn-secondary" id="clearaddBtn">Clear</button>
                    <button type="submit" class="btn btn-danger">Save</button>
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
                    Edit Agent
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="EditForm" method="POST" class="ajax-form" data-reset="0" data-modal="#editModal" novalidate>
                @csrf
                @method('PUT')

                <div class="modal-body row">
                    <input type="hidden" id="edit_agent_id" name="agent_id">

                    <!-- Name -->
                    <div class="form-group col-6">
                        <label>Agent Name</label>
                        <div class="input-group">
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <small class="text-danger error-text" data-error="name"></small>
                    </div>

                    <!-- Email -->
                    <div class="form-group col-6">
                        <label>Email</label>
                        <div class="input-group">
                            <input type="email" name="email" id="edit_email" class="form-control">
                        </div>
                        <small class="text-danger error-text" data-error="email"></small>
                    </div>

                    <!-- Mobile -->
                    <div class="form-group col-6">
                        <label>Mobile</label>
                        <div class="input-group">
                            <input type="text" name="mobile" id="edit_mobile" class="form-control">
                        </div>
                        <small class="text-danger error-text" data-error="mobile"></small>
                    </div>

                    <!-- Company -->
                    <div class="form-group col-6">
                        <label>Company Name</label>
                        <div class="input-group">
                            <input type="text" name="company_name" id="edit_company_name" class="form-control" required>
                        </div>
                        <small class="text-danger error-text" data-error="company_name"></small>
                    </div>

                    <!-- Commission Type -->
                    <div class="form-group col-6">
                        <label>Commission Type</label>
                        <div class="input-group">
                            <select name="commission_type" id="edit_commission_type" class="form-control" required>
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount</option>
                            </select>
                        </div>
                        <small class="text-danger error-text" data-error="commission_type"></small>
                    </div>

                    <!-- Commission Value -->
                    <div class="form-group col-6">
                        <label>Commission Value</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="commission_value"
                                id="edit_commission_value" class="form-control" required>
                        </div>
                        <small class="text-danger error-text" data-error="commission_value"></small>
                    </div>

                    <!-- Status -->
                    <div class="form-group col-6">
                        <label>Status</label>
                        <div class="input-group">
                            <select name="status" id="edit_status" class="form-control" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <small class="text-danger error-text" data-error="status"></small>
                    </div>

                </div>

                <div class="modal-footer border-top">
                    <button type="reset" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@include('layouts.common-view')
<script>
    // When edit button is clicked
    $(document).on('click', '.editAgentBtn', function() {

        let id = $(this).data('id');
        let name = $(this).data('name');
        let email = $(this).data('email');
        let mobile = $(this).data('mobile');
        let company = $(this).data('company');
        let type = $(this).data('type');
        let value = $(this).data('value');
        let status = $(this).data('status');

        let form = $('#EditForm');

        // dynamic route
        form.attr('action', '/agents/' + id);

        // fill values
        $('#edit_agent_id').val(id);
        $('#edit_name').val(name);
        $('#edit_email').val(email);
        $('#edit_mobile').val(mobile);
        $('#edit_company_name').val(company);
        $('#edit_commission_type').val(type);
        $('#edit_commission_value').val(value);
        $('#edit_status').val(status);

        // show modal
        $('#editModal').modal('show');
    });
</script>


@endsection