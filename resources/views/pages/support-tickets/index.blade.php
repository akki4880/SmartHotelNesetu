@extends('layouts.master')

@section('title', 'Support Tickets')
@section('mainbreadcrumb', 'Support')
@section('subbreadcrumb', 'Tickets')

@section('content')

@php
$filters = request()->except(['_token', 'page']);
$hasFilter = collect($filters)->filter(fn($value) => !empty($value))->isNotEmpty();
@endphp

<div class="row" style="{{ $hasFilter ? '' : 'display:none;' }}" id="filterRow">
    <div class="col-sm-12">
        <div class="card mb-g">
            <div class="card-header bg-info d-flex justify-content-between align-items-center">
                <h3 class="mb-0 fw-bolder text-white"><i class="fa-solid fa-filter"></i> Filter</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('tickets.index') }}" id="ticketFilterForm">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Search</label>
                                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Title or description">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All</option>
                                    @foreach($statuses as $status)
                                    <option value="{{ $status->value }}" @selected(request('status')===$status->value)>{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Priority</label>
                                <select name="priority" class="form-control">
                                    <option value="">All</option>
                                    @foreach($priorities as $priority)
                                    <option value="{{ $priority->value }}" @selected(request('priority')===$priority->value)>{{ $priority->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex">
                                <div class="mx-2">
                                    <a href="{{ route('tickets.index') }}" class="btn btn-secondary mt-4">Reset</a>
                                </div>
                                <div>
                                    <button type="submit" class="btn btn-info mt-4">Apply</button>
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
                <h3 class="mb-0 fw-bolder">{{ $isSupport ? 'All Support Tickets' : 'My Support Tickets' }}</h3>
                <div class="float-right">
                    @if(auth()->user()->hasPermission('support_tickets', 'create'))
                    @if(!$isSupport)
                    <button class="btn btn-danger btn-sm btn-loader" data-toggle="modal" data-target="#createTicketModal">
                        <i class="fa-solid fa-plus"></i> New Ticket
                    </button>
                    @endif
                    @endif
                    <button class="btn btn-info btn-sm" onclick="toggleFilter()">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="frame-wrap p-0 border-0 m-0" id="table-container">
                    @if($tickets->count() > 0)
                    <table class="table m-0 table-bordered table-sm" id="tickets-table">
                        <thead class="bg-primary text-white">
                            <tr>
                                @if(auth()->user()->hasPermission('support_tickets', 'edit'))
                                <th>Action</th>@endif
                                <th>Sr</th>
                                <th>ID</th>
                                <th>Subject</th>
                                @if($isSupport)
                                <th>Client</th>
                                <th>Assigned To</th>
                                @endif
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Updated</th>
                            </tr>
                        </thead>
                        <tbody id="tickets-table-body">
                            @foreach($tickets as $index => $ticket)
                            <tr data-ticket-id="{{ $ticket->id }}">
                                @if(auth()->user()->hasPermission('support_tickets', 'edit'))
                                <td>
                                    @if(auth()->user()->hasPermission('support_tickets', 'edit'))
                                    @if($isSupport)
                                    <button type="button" class="btn one-btn-outline-success btn-icon btn-sm quick-edit-btn btn-loader"
                                        data-id="{{ $ticket->id }}"
                                        data-status="{{ $ticket->status->value }}"
                                        data-priority="{{ $ticket->priority->value }}"
                                        data-assigned="{{ $ticket->assigned_to }}"
                                        title="Quick Update">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    @endif

                                    <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-outline-primary btn-icon btn-sm" title="Open Chat" style="overflow: visible !important;">
                                        <i class="fa fa-comments"></i>
                                        @if($ticket->unread_count > 0)
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill badge-danger">
                                            {{ $ticket->unread_count }}
                                        </span>
                                        @endif
                                    </a>
                                    @endif
                                </td>
                                @endif
                                <td>{{ ($tickets->currentPage() - 1) * $tickets->perPage() + $index + 1 }}</td>
                                <td>#{{ $ticket->id }}</td>
                                <td>
                                    <a href="{{ route('tickets.show', $ticket) }}" class="font-weight-bold text-dark">
                                        {{ $ticket->title }}
                                    </a>
                                    @if($ticket->latestMessage)
                                    <br><small class="text-muted ticket-latest-msg">{{ $ticket->latestMessage->message }}</small>
                                    @endif
                                </td>
                                @if($isSupport)
                                <td class="ticket-client-name">{{ $ticket->user?->name }}</td>
                                <td class="ticket-assignee-name">{{ $ticket->assignee?->name ?? 'Unassigned' }}</td>
                                @endif
                                <td>
                                    <span class="badge ticket-priority-badge {{ $ticket->priority->badgeClass() }}">
                                        {{ $ticket->priority->label() }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge ticket-status-badge {{ $ticket->status->badgeClass() }}">
                                        {{ $ticket->status->label() }}
                                    </span>
                                </td>
                                <td>{{ $ticket->updated_at->format('M d, Y H:i') }}</td>
                            </tr>
                            @endforeach

                        </tbody>
                    </table>
                    <div class="d-flex justify-content-end mt-3" id="tickets-pagination">
                        {{ $tickets->links() }}
                    </div>
                    @else
                    <p id="no-tickets-msg">No tickets found. Create your first support ticket.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createTicketModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h4 class="modal-title">Create Support Ticket</h4>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="CreateTicketForm" action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data" class="ajax-form" data-reset="1" data-modal="#createTicketModal" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" required placeholder="Brief summary of the issue">
                        <small class="text-danger error-text" data-error="title"></small>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="4" required placeholder="Describe your issue in detail"></textarea>
                        <small class="text-danger error-text" data-error="description"></small>
                    </div>
                    <div class="form-group">
                        <label>Priority</label>
                        <select name="priority" class="form-control" required>
                            @foreach($priorities as $priority)
                            <option value="{{ $priority->value }}" @selected($priority->value === 'medium')>{{ $priority->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Attachments (optional)</label>
                        <input type="file" name="attachments[]" class="form-control" multiple accept="image/*,.pdf,.doc,.docx,.txt,.zip">
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger" id="saveTicketBtn">Submit Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($isSupport)
<div class="modal fade" id="quickEditModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title">Update Ticket</h4>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="QuickEditForm" data-modal="#quickEditModal" method="POST" class="ajax-form" data-reset="0" novalidate>
                @csrf
                @method('PUT')

                <div class="modal-body">
                    <input type="hidden" id="quick_edit_ticket_id">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" id="quick_edit_status" class="form-control">
                            @foreach($statuses as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Priority</label>
                        <select name="priority" id="quick_edit_priority" class="form-control">
                            @foreach($priorities as $priority)
                            <option value="{{ $priority->value }}">{{ $priority->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Assign To</label>
                        <select name="assigned_to" id="quick_edit_assigned" class="form-control select2">
                            <option value="">Unassigned</option>
                            @foreach($developers as $developer)
                            <option value="{{ $developer->id }}">{{ $developer->name }}</option>
                            @endforeach
                        </select>
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
@endif
<script>
    $(document).on('click', '.quick-edit-btn', function() {

        let id = $(this).data('id');
        let status = $(this).data('status');
        let priority = $(this).data('priority');
        let assigned = $(this).data('assigned');

        $('#quick_edit_ticket_id').val(id);
        $('#quick_edit_status').val(status);
        $('#quick_edit_priority').val(priority);
        $('#quick_edit_assigned').val(assigned).trigger('change');

        $('#QuickEditForm').attr(
            'action',
            '/tickets/' + id
        );

        $('#quickEditModal').modal('show');
    });
</script>
@endsection