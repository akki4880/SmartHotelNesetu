@extends('layouts.master')

@section('title', 'Audit Logs')
@section('mainbreadcrumb', 'Audit Logs')

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

                        <!-- Date From -->
                        <div class="col-3">
                            <div class="form-group">
                                <label>From Date</label>
                                <input type="date" name="from_date" class="form-control"
                                    value="{{ request('from_date') }}">
                            </div>
                        </div>

                        <!-- Date To -->
                        <div class="col-3">
                            <div class="form-group">
                                <label>To Date</label>
                                <input type="date" name="to_date" class="form-control"
                                    value="{{ request('to_date') }}">
                            </div>
                        </div>

                        <!-- User -->
                        <div class="col-3">
                            <div class="form-group">
                                <label>User</label>
                                <select name="user_name" class="form-control">
                                    <option value="">Select User</option>
                                    @foreach($users as $user)
                                    <option value="{{ $user->name }}"
                                        {{ request('user_name') == $user->name ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Module -->
                        <div class="col-3">
                            <div class="form-group">
                                <label>Module</label>
                                <select name="module" class="form-control">
                                    <option value="">Select Module</option>
                                    @foreach($modules as $module)
                                    <option value="{{ $module }}"
                                        {{ request('module') == $module ? 'selected' : '' }}>
                                        {{ $module }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Action -->
                        <div class="col-3 mt-3">
                            <div class="form-group">
                                <label>Action</label>
                                <select name="action" class="form-control">
                                    <option value="">Select Action</option>
                                    @foreach($actions as $action)
                                    <option value="{{ $action }}"
                                        {{ request('action') == $action ? 'selected' : '' }}>
                                        {{ ucfirst($action) }}
                                    </option>
                                    @endforeach
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
                <h3 class="mb-0 fw-bolder">Audit Logs</h3>
                <div class="float-right">
                    <button class="btn btn-info btn-sm" onclick="toggleFilter()">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>

                </div>
            </div>

            <div class="card-body">
                <div class="frame-wrap p-0 border-0 m-0" id="table-container">
                    @if($audits->count() > 0)
                    <table class="table m-0 table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>User</th>
                                <th>Module</th>
                                <th>Action</th>
                                <th>Event</th>
                                <th>Record</th>
                                <th>Field</th>
                                <th>Old</th>
                                <th>New</th>
                                <th>IP</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($audits as $index => $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                                <td>{{ $log->user->name ?? '-' }}</td>
                                <td>{{ ucfirst($log->module) }}</td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td>{{ $log->event ?? '-' }}</td>
                                <td>#{{ $log->record_id }}</td>
                                <td>{{ $log->field_name ?? '-' }}</td>
                                <td class="text-danger">{{ $log->old_value }}</td>
                                <td class="text-success">{{ $log->new_value }}</td>
                                <td>{{ $log->ip_address }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $audits->links() }}
                    </div>
                    @else
                    <p>No Audit Log found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>



@endsection