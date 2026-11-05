@extends('layouts.master')

@section('title', 'Contact Requests')
@section('mainbreadcrumb', 'Contact Requests')

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
                <!-- FILTER FORM -->
                <form method="GET" action="{{ url()->current() }}">
                    <div class="row">
                        <div class="col-md-2">
                            <!-- Name -->
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control"
                                    value="{{ request('name') }}" placeholder="Customer Name">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <!-- Email -->
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control"
                                    value="{{ request('email') }}" placeholder="Email Address">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <!-- Subject -->
                            <div class="form-group">
                                <label>Subject</label>
                                <select name="subject" class="form-control">
                                    <option value="">Select a subject...</option>
                                    <option value="General Inquiry" {{ request('subject') == 'General Inquiry' ? 'selected' : '' }}>General Inquiry</option>
                                    <option value="Book a Demo" {{ request('subject') == 'Book a Demo' ? 'selected' : '' }}>Book a Demo</option>
                                    <option value="Technical Support" {{ request('subject') == 'Technical Support' ? 'selected' : '' }}>Technical Support</option>
                                    <option value="Billing &amp; Pricing" {{ request('subject') == 'Billing &amp; Pricing' ? 'selected' : '' }}>Billing &amp; Pricing</option>
                                    <option value="Partnership Opportunity" {{ request('subject') == 'Partnership Opportunity' ? 'selected' : '' }}>Partnership Opportunity</option>
                                    <option value="Other" {{ request('subject') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <!-- Company -->
                            <div class="form-group">
                                <label>Company</label>
                                <input type="text" name="company" class="form-control"
                                    value="{{ request('company') }}" placeholder="Company">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- Date Range -->
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>From Date</label>
                                        <input type="date" name="from_date" class="form-control"
                                            value="{{ request('from_date') }}">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>To Date</label>
                                        <input type="date" name="to_date" class="form-control"
                                            value="{{ request('to_date') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 text-end">
                            <div class="d-flex mt-2">
                                <a href="{{ url()->current() }}" class="btn btn-secondary mr-2">
                                    Reset
                                </a>
                                <button type="submit" class="btn btn-info">
                                    Apply
                                </button>
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
                <h3 class="mb-0 fw-bolder">Contact Requests</h3>
                <div class="float-right">
                    <button class="btn btn-info btn-sm" onclick="toggleFilter()">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="frame-wrap p-0 border-0 m-0" id="table-container">
                    @if($contactRequests->count() > 0)
                    <table class="table m-0 table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                @if(auth()->user()->hasPermission('contact_requests', 'view') || auth()->user()->hasPermission('contact_requests', 'delete'))
                                <th style="width: 100px;">Action</th>
                                @endif
                                <th>Sr</th>
                                <th>Date</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Company</th>
                                <th>Subject</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contactRequests as $index => $request)
                            <tr>
                                @if(auth()->user()->hasPermission('contact_requests', 'view') || auth()->user()->hasPermission('contact_requests', 'delete'))
                                <td>
                                    @if(auth()->user()->hasPermission('contact_requests', 'view'))
                                    <button class="btn btn-outline-primary btn-icon btn-sm view-btn btn-loader"
                                        data-url="{{ route('contact-requests.show', $request->id) }}" title="View">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    @endif
                                    @if(auth()->user()->hasPermission('contact_requests', 'delete'))
                                    <a href="javascript:void(0);"
                                        class="btn one-btn-outline-danger btn-icon btn-sm delete-contact-btn"
                                        data-id="{{ $request->id }}" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                    @endif
                                </td>
                                @endif
                                <td>{{ ($contactRequests->currentPage() - 1) * $contactRequests->perPage() + $index + 1 }}</td>
                                <td>{{ formatDateTime($request->created_at) }}</td>
                                <td>{{ $request->name }}</td>
                                <td>{{ $request->email }}</td>
                                <td>{{ $request->phone ?? '-' }}</td>
                                <td>{{ $request->company ?? '-' }}</td>
                                <td><span data-toggle="tooltip" title="{{ $request->subject }}"> {{ shortText($request->subject, 30) }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-end mt-3">
                        {{ $contactRequests->links() }}
                    </div>

                    @else
                    <div class="alert alert-info mt-3 text-center">
                        <i class="fa-solid fa-info-circle"></i> No Contact Requests found.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.common-view')

@include('layouts.confirmationModal')
<script>
    function toggleFilter() {
        const filterRow = document.getElementById('filterRow');
        if (filterRow.style.display === 'none') {
            filterRow.style.display = '';
        } else {
            filterRow.style.display = 'none';
        }
    }

    // Initialize event listeners when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Delete contact buttons
        const deleteButtons = document.querySelectorAll('.delete-contact-btn');
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                showDeleteConfirmation(id);
            });
        });

        // View buttons
        const viewButtons = document.querySelectorAll('.view-btn');
        viewButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const url = this.getAttribute('data-url');
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status) {
                            showContactDetails(data.data);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        });
    });

    function showDeleteConfirmation(id) {
        $('#confirmationModalTitle').html('Delete Contact Request');
        $('#confirmationModalBody').html('<p>Are you sure you want to delete this contact request? This action cannot be undone.</p>');
        $('#confirmationModalConfirmBtn').removeClass('btn-primary btn-warning btn-success').addClass('btn-danger');
        $('#confirmationModal').modal('show');

        // Remove previous event listeners
        $('#confirmationModalConfirmBtn').off('click');

        // Add new event listener for this specific delete action
        $('#confirmationModalConfirmBtn').click(function() {
            deleteContact(id);
            $('#confirmationModal').modal('hide');
        });
    }

    function deleteContact(id) {
        fetch(`/contact-requests/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    showAlert('Success', data.message, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('Error', data.message, 'error');
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function showContactDetails(data) {
        const html = `
            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Name:</strong> ${data.name}</p>
                    <p><strong>Email:</strong> ${data.email}</p>
                    <p><strong>Phone:</strong> ${data.phone || 'N/A'}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Company:</strong> ${data.company || 'N/A'}</p>
                    <p><strong>Subject:</strong> ${data.subject}</p>
                    <p><strong>Date:</strong> ${new Date(data.created_at).toLocaleString()}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <p><strong>Message:</strong></p>
                    <div class="border p-3 bg-light">
                        ${data.message}
                    </div>
                </div>
            </div>
        `;
        document.getElementById('modalBody').innerHTML = html;
        $('#commonViewModal').modal('show');
    }

    function showAlert(title, message, type) {
        // Using toastr or similar notification library. Adjust if needed.
        if (typeof toastr !== 'undefined') {
            toastr[type](message, title);
        } else {
            alert(`${title}: ${message}`);
        }
    }
</script>

@endsection