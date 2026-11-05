@extends('layouts.master')

@section('title', 'Generate Invoice')
@section('mainbreadcrumb', 'Invoices')
@section('subbreadcrumb', 'Generate Invoice')

@section('content')

<style>
    .fade-enter {
        opacity: 0;
        transform: translateY(10px);
    }

    .fade-enter-active {
        opacity: 1;
        transform: translateY(0);
        transition: all 0.3s ease-in-out;
    }
</style>
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
                    <div class="row"></div>

                </form>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="card mb-g">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0 fw-bolder">Generate Invoice</h3>
                <div class="float-right">
                    <button id="filterBtn" class="btn btn-info btn-sm" onclick="toggleFilter()">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                </div>
            </div> 
            <div class="card-body">
                <div class="frame-wrap p-0 border-0 m-0" id="table-container">
                    @if($bookings->count() > 0)
                    <table class="table m-0 table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                @if(auth()->user()->hasPermission('generate_invoice', 'checking') || 
                                    auth()->user()->hasPermission('generate_invoice', 'checkout') || 
                                    auth()->user()->hasPermission('generate_invoice', 'edit') ||
                                    auth()->user()->hasPermission('generate_invoice', 'view'))
                                <th width="120">Actions</th>
                                @endif
                                <th>Booking ID</th>
                                <th>Guest</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Total Hours</th>
                                <th>Rooms</th>
                                <th>Guests</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($bookings as $booking)

                            @php
                            $today = \Carbon\Carbon::today();
                            $checkin = \Carbon\Carbon::parse($booking->check_in_date);
                            $paidAmount = \App\Models\BookingPayment::where('booking_id', $booking->id)->sum('amount');
                            $pendingAmount = $booking->total_amount - $paidAmount;

                            $checkInDateTime = $booking->actual_check_in_at
                            ? \Carbon\Carbon::parse($booking->actual_check_in_at)
                            : null;

                            $checkOutDateTime = $booking->actual_check_out_at
                            ? \Carbon\Carbon::parse($booking->actual_check_out_at)
                            : null;

                            $totalDurationMinutes = null;
                            $totalHoursText = 'N/A';

                            if ($checkInDateTime && $checkOutDateTime) {

                            // Calculate checkout - check-in
                            $totalDurationMinutes = $checkInDateTime->diffInMinutes($checkOutDateTime, false);

                            if ($totalDurationMinutes > 0) {

                            $hours = (int) floor($totalDurationMinutes / 60);
                            $minutes = (int) floor($totalDurationMinutes % 60);

                            $totalHoursText = $hours . 'h ' . $minutes . 'm';

                            } else {
                            $totalHoursText = '0h 0m';
                            }
                            }
                            @endphp

                            <tr>

                                @if(auth()->user()->hasPermission('generate_invoice', 'checking') || 
                                    auth()->user()->hasPermission('generate_invoice', 'checkout') || 
                                    auth()->user()->hasPermission('generate_invoice', 'edit') ||
                                    auth()->user()->hasPermission('generate_invoice', 'view'))
                                <td>
                                    <div class="d-flex" style="gap:4px;">

                                        {{-- EDIT --}}
                                        @if(auth()->user()->hasPermission('generate_invoice', 'edit'))
                                        <a href="javascript:void(0)"
                                            class="btn btn-outline-success btn-icon btn-sm editBookingBtn"
                                            data-id="{{ $booking->id }}"
                                            title="Edit Booking">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        @endif

                                        {{-- CHECK OUT --}}
                                        @if(auth()->user()->hasPermission('generate_invoice', 'checkout'))
                                        @if(($booking->status === 'checked_in' || $booking->actual_check_in_at) && !$booking->actual_check_out_at)
                                        <a href="javascript:void(0)"
                                            class="btn btn-outline-primary btn-icon btn-sm checkout-btn"
                                            data-id="{{ $booking->id }}"
                                            data-booking-code="{{ $booking->booking_code }}"
                                            title="Check Out">
                                            <i class="fa-solid fa-door-closed"></i>
                                        </a>
                                        @endif
                                        @endif

                                        {{-- Generate Invoice --}}
                                        @if(auth()->user()->hasPermission('generate_invoice', 'view'))
                                        <a href="javascript:void(0)"
                                            class="btn btn-outline-primary btn-icon btn-sm generateInvoiceBtn"
                                            data-id="{{ $booking->id }}"
                                            title="Generate Invoice">
                                            <i class="fa-solid fa-file-invoice"></i>
                                        </a>
                                        @endif

                                        {{-- Download Invoice --}}
                                        @if($booking->invoices->isNotEmpty())
                                            <a href="javascript:void(0)"
                                            class="btn btn-outline-success btn-icon btn-sm downloadInvoiceBtn"
                                            data-id="{{ $booking->id }}"
                                            data-invoice-id="{{ $booking->invoices->first()->id }}"
                                            title="Download Invoice">
                                                <i class="fa-solid fa-download"></i>
                                            </a>
                                        @endif

                                    </div>
                                </td>
                                @endif

                                {{-- BOOKING CODE --}}
                                <td>
                                    <strong>{{ $booking->booking_code }}</strong>
                                </td>

                                <td>{{ $booking->primaryGuest->full_name ?? 'N/A' }}</td>

                                <td>
                                    @php
                                    $checkInDisplay = $booking->actual_check_in_at
                                    ? \Carbon\Carbon::parse($booking->actual_check_in_at)->format('d M Y, h:i A')
                                    : 'N/A';
                                    @endphp
                                    {{ $checkInDisplay }}
                                </td>

                                <td>
                                    @php
                                    $checkOutDisplay = $booking->actual_check_out_at
                                    ? \Carbon\Carbon::parse($booking->actual_check_out_at)->format('d M Y, h:i A')
                                    : 'N/A';
                                    @endphp
                                    {{ $checkOutDisplay }}
                                </td>

                                <td>
                                    {{ $totalHoursText }}
                                </td>

                                <td>{{ $booking->no_of_rooms }}</td>

                                <td>{{ $booking->no_of_guests }}</td>
                                @php
                                $statusColors = [
                                'pending' => 'badge-warning',
                                'confirmed' => 'badge-secondary',
                                'checked_in' => 'one-badge-success',
                                'checked_out' => 'badge-primary',
                                'cancelled' => 'one-badge-danger',
                                'no_show' => 'badge-warning text-light'
                                ];
                                @endphp
                                <td>
                                    <span class="badge {{ $statusColors[$booking->status] ?? 'badge-light' }}">
                                        {{ ucfirst(str_replace('_',' ', $booking->status)) }}
                                    </span>
                                </td>
                            </tr>

                            @endforeach
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-end mt-3">
                        {{ $bookings->links() }}
                    </div>
                    @else
                    <p>No Bookings found.</p>
                    @endif
                    <div class="d-flex justify-content-end mt-3">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<x-edit-booking-modal
    :agents="$agents"
    :additional-services="$additionalServices"
/>

<x-checkin-modal />

<div class="modal fade" id="invoiceModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog  modal-lg" role="document" style="max-width: 80% !important; ">
        <div class="modal-content ">
            <div class="modal-header bg-primary text-white">
                <h4 class="modal-title" id="invoiceModalTitle">
                    Generate Invoice
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form id="CreateForm" action="{{ route('invoices.store') }}" method="POST" class="ajax-form"
                data-reset="1" data-modal="#invoiceModal" novalidate>
                @csrf
                <input type="hidden" name="booking_id" id="invoice_booking_id" value="">
                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-3">
                            <label>Name</label>
                            <input type="text" id="inv_name" name="name" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>Email</label>
                            <input type="text" id="inv_email" name="email" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>GSTIN</label>
                            <input type="text" id="inv_gstin" name="gstin" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>Invoice Date</label>
                            <input type="date" id="inv_date" name="invoice_date" class="form-control" readonly>
                        </div>

                        <div class="col-md-6 mt-2">
                            <label>Address</label>
                            <textarea id="inv_address" name="address" class="form-control"></textarea>
                        </div>

                        <div class="col-md-3 mt-2">
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

                        <div class="col-md-3 mt-2">
                            <label>State</label>
                            <select id="state" name="state_id" class="select2 form-control state-dropdown">
                                <option value="">Select State</option>
                            </select>
                            <small class="text-danger error-text" data-error="state_id"></small>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between">
                        <h5 class="">Invoice Items</h5>
                        <button type="button" class="btn btn-sm btn-success mb-3" id="addRowBtn">
                            + Add More
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table m-0 table-bordered table-sm" id="invoiceItemsTable">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th width="14%">DESCRIPTION</th>
                                    <th>HSN</th>
                                    <th>RATE</th>
                                    <th>QTY</th>
                                    <th>SUBTOTAL</th>
                                    <th>DISCOUNT</th>
                                    <th>TAXABLE</th>
                                    <th>GST %</th>
                                    <th class="cgst_head">CGST</th>
                                    <th class="sgst_head">SGST</th>
                                    <th>TOTAL</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="invoiceItemsBody">
                                <!-- Dynamic Rows -->
                            </tbody>
                        </table>
                    </div>

                    <hr>

                    <h5>Summary</h5>

                    <div class="row">
                        <div class="col-md-3">
                            <label>Total Taxable</label>
                            <input type="text" id="total_taxable" name="total_taxable" class="form-control" readonly>
                        </div>

                        <div class="col-md-3">
                            <label>Total GST</label>
                            <input type="text" id="total_gst" name="total_gst" class="form-control" readonly>
                        </div>

                        <div class="col-md-3">
                            <label>Grand Total</label>
                            <input type="text" id="grand_total" name="grand_total" class="form-control" readonly>
                        </div>

                        <div class="col-md-3">
                            <label>Balance</label>
                            <input type="text" id="balance_amount" name="balance_amount" class="form-control" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <!-- <button type="reset" class="btn btn-secondary" id="clearaddBtn">Clear</button> -->
                    <button type="submit" class="btn btn-primary" id="saveRoleBtn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('layouts.confirmationModal')

{{-- CHECKOUT MODAL (Reuse from bookings) --}}
<div class="modal fade" id="checkoutModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fa-solid fa-door-open mr-2"></i>Check Out: <span id="checkoutBookingCode"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <i class="fal fa-times"></i>
                </button>
            </div>

            <div class="modal-body">
                <form id="checkoutForm">
                    @csrf
                    <input type="hidden" name="booking_id" id="checkout_booking_id">

                    <div class="row">
                        <div class="col-12">
                            <h6 class="mb-3">
                                <i class="fa-solid fa-sign-in-alt mr-2"></i>Check-In Information
                            </h6>
                            <div class="border rounded p-3 mb-4 bg-light">
                                <div class="form-group">
                                    <label class="font-weight-bold">Check-In Date & Time</label>
                                    <p id="displayCheckInDateTime" class="mb-0 text-muted"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-12">
                            <h6 class="mb-3">
                                <i class="fa-solid fa-sign-out-alt mr-2"></i>Check-Out Information
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="checkoutDate" class="font-weight-bold">
                                    <i class="fa-regular fa-calendar mr-1"></i>Checkout Date
                                </label>
                                <input type="date" class="form-control" id="checkoutDate" name="checkout_date" required>
                                <small class="form-text text-muted">Date when guest checks out</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="checkoutTime" class="font-weight-bold">
                                    <i class="fa-regular fa-clock mr-1"></i>Checkout Time
                                </label>
                                <input type="time" class="form-control" id="checkoutTime" name="checkout_time" required>
                                <small class="form-text text-muted">Time when guest checks out</small>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer border-top">
                <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="confirmCheckout">
                    <i class="fa-solid fa-door-open mr-1"></i> Confirm Check Out
                </button>
            </div>
        </div>
    </div>
</div>
@php
    $hotelAdditionalServices = ($additionalServices ?? collect())
        ->map(function ($service) {
            return [
                'id' => (int) $service->id,
                'name' => $service->service_name,
                'cost' => (float) $service->cost,
            ];
        })
        ->values()
        ->all();
@endphp

<script>
    const hotelAdditionalServices = @json($hotelAdditionalServices);
</script>
<script>
    let editBookingId = null;
    let baseAmount = 0;
    let invoiceItemIndex = 0;

    let countriesOptions = `
        @foreach($countries as $country)
            <option value="{{ $country->id }}">{{ $country->name }}</option>
        @endforeach
    `;

    function calculateTotal() {
        let total = baseAmount;

        document.querySelectorAll('.serviceCheck:checked').forEach(cb => {
            const price = parseFloat(cb.dataset.price || 0);
            const qtyInput = cb.closest('.form-check').querySelector('.serviceQty');
            const quantity = parseInt(qtyInput.value) || 1;
            total += price * quantity;
        });

        document.getElementById('totalAmountInput').value = total.toFixed(2);
        document.getElementById('finalAmount').innerText = total.toFixed(2);
    }

    function toggleQty(checkbox) {
        const qtyInput = checkbox.closest('.form-check').querySelector('.serviceQty');
        if (checkbox.checked) {
            qtyInput.disabled = false;
        } else {
            qtyInput.disabled = true;
            qtyInput.value = 1;
        }
        calculateTotal();
    }

    function validatePaymentFields(root = document) {
        const paidAtField = root.querySelector ? root.querySelector('#paidAt') : null;
        const referenceField = root.querySelector ? root.querySelector('#reference') : null;
        const amountField = root.querySelector ? root.querySelector('#amount') : null;

        if (!paidAtField || !referenceField || !amountField) {
            return true;
        }

        const hasPaidAt = (paidAtField.value || '').trim() !== '';
        const hasReference = (referenceField.value || '').trim() !== '';
        const hasAmount = (amountField.value || '').trim() !== '' && Number(amountField.value) > 0;

        referenceField.required = hasPaidAt;
        amountField.required = hasPaidAt;

        if (hasPaidAt && (!hasReference || !hasAmount)) {
            toastr.error('When Paid At is selected, Reference and Amount are required.');
            if (!hasReference) {
                referenceField.focus();
            } else {
                amountField.focus();
            }
            return false;
        }

        return true;
    }

    function createGuestForm(index) {
        return `
        <div class="card p-3 mb-3 guest-card" data-index="${index}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Guest ${index + 1}</h6>
                <div class="d-flex align-items-center gap-2">
                    <label class="mb-0">
                        <input type="radio" name="primary_guest" value="${index}" ${index == 0 ? 'checked' : ''}> Lead Guest
                    </label>
                    ${index > 0 ? '<button type="button" class="btn btn-sm btn-danger removeGuestBtn">Remove</button>' : ''}
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-3">
                    <label>Mobile</label>
                    <input type="text" name="guests[${index}][mobile]" class="form-control guestMobile">
                </div>
                <div class="col-md-3">
                    <label>Name</label>
                    <input type="text" name="guests[${index}][name]" class="form-control">
                </div>
                <div class="col-md-3">
                    <label>Email</label>
                    <input type="email" name="guests[${index}][email]" class="form-control">
                </div>
                <div class="col-md-3">
                    <label>Date of Birth</label>
                    <input type="date" name="guests[${index}][dob]" class="form-control">
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-3">
                    <label>Country</label>
                    <select name="guests[${index}][country_id]" class="form-control select2 country-dropdown" data-target="#state_${index}" onchange="hydrateGuestStateSelection(this, document.getElementById('state_${index}'))">
                        <option value="">Select Country</option>
                        ${countriesOptions}
                    </select>
                </div>
                <div class="col-md-3">
                    <label>State</label>
                    <select id="state_${index}" name="guests[${index}][state_id]" class="form-control select2 state-dropdown">
                        <option value="">Select State</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Address</label>
                    <textarea name="guests[${index}][address]" class="form-control"></textarea>
                </div>
            </div>

            <div class="idProofSection">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="mb-0">ID Proofs</label>
                    <button type="button" class="btn btn-sm btn-success addProofBtn" data-guest="${index}">
                        <i class="fa fa-plus"></i> Add
                    </button>
                </div>
                <div class="proofContainer">
                    ${getProofRow(index, 0, true)}
                </div>
            </div>
        </div>`;
    }

    function getProofRow(guestIndex, proofIndex, isDefault = false) {
        return `
            <div class="row mt-2 proof-row">
                <div class="col-md-4">
                    <label class="small text-muted d-block mb-1">ID Type</label>
                    <input type="text" name="guests[${guestIndex}][id_proofs][${proofIndex}][id_type]" class="form-control" placeholder="ID Type" ${!isDefault ? 'required' : ''}>
                </div>
                <div class="col-md-4">
                    <label class="small text-muted d-block mb-1">ID Proof File</label>
                    <input type="file" name="guests[${guestIndex}][id_proofs][${proofIndex}][id_file]" class="form-control" ${!isDefault ? 'required' : ''}>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    ${!isDefault ? '<button type="button" class="btn btn-sm btn-danger removeProofBtn"><i class="fa fa-trash"></i></button>' : ''}
                </div>
            </div>`;
    }

    function hydrateGuestStateSelection(countrySelect, stateSelect, selectedStateId = null) {
        const countryId = $(countrySelect).val();
        if (!countryId) {
            $(stateSelect).html('<option value="">Select State</option>');
            return;
        }

        $.ajax({
            url: "{{ route('get.states.by.country') }}",
            type: 'GET',
            data: { country_id: countryId },
            success: function(states) {
                let options = '<option value="">Select State</option>';
                $.each(states, function(key, state) {
                    const selected = Number(selectedStateId) === Number(state.id) ? 'selected' : '';
                    options += `<option value="${state.id}" ${selected}>${state.state_name}</option>`;
                });
                $(stateSelect).html(options);
            }
        });
    }

    function loadGuestProofs(guestCard, proofs = [], guestIndex) {
        const container = guestCard.find('.proofContainer');
        container.html('');

        if (!proofs || proofs.length === 0) {
            container.append(getProofRow(guestIndex, 0, true));
            return;
        }

        proofs.forEach(function(proof, index) {
            const proofPath = ((proof.id_file_path || '').replace(/^\/+/, ''));
            const proofUrl = proofPath ? '/storage/' + proofPath : '';

            container.append(`
                <div class="row mt-2 proof-row align-items-end" data-proof-id="${proof.id ?? ''}">
                    <input type="hidden" name="guests[${guestIndex}][id_proofs][${index}][proof_id]" value="${proof.id ?? ''}">
                    <input type="hidden" name="guests[${guestIndex}][id_proofs][${index}][id_type]" value="${proof.id_type || ''}">
                    <div class="col-md-4">
                        <label class="small text-muted d-block mb-1">ID Type</label>
                        <input type="text" class="form-control" name="guests[${guestIndex}][id_proofs][${index}][id_type_display]" value="${proof.id_type || ''}" readonly>
                    </div>
                    <div class="col-md-4">
                        <input type="file" name="guests[${guestIndex}][id_proofs][${index}][id_file]" class="form-control replaceFile">
                    </div>
                    <div class="col-md-2 d-flex align-items-center gap-2">
                        ${proofUrl ? `<a href="${proofUrl}" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>` : ''}
                        <button type="button" class="btn btn-sm btn-danger removeProofBtn"><i class="fa fa-trash"></i></button>
                    </div>
                </div>
            `);
        });
    }

    document.addEventListener('click', async function(e) {
        const editBtn = e.target.closest('.editBookingBtn');
        if (!editBtn) return;

        const id = editBtn.dataset.id;

        try {
            const res = await fetch(`/bookings/${id}/edit`);
            const json = await res.json();

            if (!json.status) {
                toastr.error('Failed to load booking');
                return;
            }

            editBookingId = id;
            fillEditModal(json.data);
            $('#editModal').modal('show');
        } catch (err) {
            console.error(err);
            toastr.error('Something went wrong');
        }
    });

    function fillEditModal(data) {
        const booking = data.booking;
        const modal = $('#editModal');
        const guests = data.guests ?? [];
        const payments = data.payments ?? [];

        modal.find('#checkInDate').val(booking.check_in_date || '');
        modal.find('#checkOutDate').val(booking.check_out_date || '');
        modal.find('#noOfGuests').val(booking.no_of_guests || 1);
        modal.find('#noOfRooms').val(booking.no_of_rooms || 1);
        modal.find('#remark').val(booking.remark || '');
        modal.find('#paymentMethod').val('cash');
        modal.find('#paidAt').val('');
        modal.find('#reference').val('');
        modal.find('#amount').val('');

        if (booking.agent_id) {
            modal.find('select[name="agent_id"]').val(booking.agent_id).trigger('change');
        }

        const room = data.room_type;
        const roomName = room ? room.name : 'N/A';
        const roomValue = booking.base_amount ?? booking.total_amount ?? 0;
        modal.find('#selectedRoomContainer').html(`
            <div class="card bg-warning shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Selected Room : ${roomName} (₹${Number(roomValue).toFixed(2)})</h5>
                </div>
            </div>
        `);

        baseAmount = Number(booking.base_amount ?? booking.total_amount ?? 0);
        modal.find('#finalAmount').text(baseAmount.toFixed(2));
        modal.find('#totalAmountInput').val(baseAmount.toFixed(2));

        modal.find('.serviceCheck').prop('checked', false);
        modal.find('.serviceQty').prop('disabled', true).val(1);

        (data.services || []).forEach(service => {
            const checkbox = modal.find(`.serviceCheck[value="${service.id}"]`);
            if (!checkbox.length) return;
            checkbox.prop('checked', true);
            const qtyInput = checkbox.closest('.form-check').find('.serviceQty');
            qtyInput.prop('disabled', false);
            qtyInput.val(service.pivot?.quantity || 1);
        });
        calculateTotal();

        let guestContainer = modal.find('#editGuestContainer');
        guestContainer.html('');

        if (!guests.length) {
            guestContainer.append(createGuestForm(0));
        } else {
            guests.forEach(function(guest, index) {
                guestContainer.append(createGuestForm(index));
                const card = guestContainer.find('.guest-card').last();
                card.find(`input[name="guests[${index}][mobile]"]`).val(guest.mobile || '');
                card.find(`input[name="guests[${index}][name]"]`).val(guest.full_name || '');
                card.find(`input[name="guests[${index}][email]"]`).val(guest.email || '');
                card.find(`input[name="guests[${index}][dob]"]`).val(guest.dob || '');
                card.find(`textarea[name="guests[${index}][address]"]`).val(guest.address || '');

                const countrySelect = card.find(`select[name="guests[${index}][country_id]"]`);
                const stateSelect = card.find(`select[name="guests[${index}][state_id]"]`);
                countrySelect.val(guest.country_id || '');

                if (guest.country_id) {
                    hydrateGuestStateSelection(countrySelect[0], stateSelect[0], guest.state_id ?? null);
                } else {
                    stateSelect.html('<option value="">Select State</option>');
                }

                if (guest.is_primary_guest) {
                    card.find('input[type="radio"]').prop('checked', true);
                }

                loadGuestProofs(card, guest.id_proofs ?? [], index);
            });
        }

        let paymentTable = `
            <table class="table m-0 table-bordered table-sm">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>Date</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
        `;

        if (!payments.length) {
            paymentTable += `<tr><td colspan="4" class="text-center text-muted">No payment records available</td></tr>`;
        } else {
            payments.forEach(payment => {
                paymentTable += `
                    <tr>
                        <td>${payment.paid_at ?? '-'}</td>
                        <td>${payment.method ?? '-'}</td>
                        <td>${payment.transaction_reference ?? '-'}</td>
                        <td>₹${Number(payment.amount || 0).toFixed(2)}</td>
                    </tr>
                `;
            });
        }

        paymentTable += `</tbody></table>`;
        modal.find('#paymentHistory').html(paymentTable);
    }

    $('#updateBtn').on('click', function(e) {
        e.preventDefault();

        if (!editBookingId) {
            toastr.error('Booking not selected');
            return;
        }

        if (!validatePaymentFields(document.getElementById('EditForm'))) {
            return;
        }

        const formData = new FormData(document.getElementById('EditForm'));
        formData.append('_method', 'PUT');

        $.ajax({
            url: `/bookings/${editBookingId}`,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status) {
                    toastr.success('Booking updated successfully');
                    $('#editModal').modal('hide');
                    location.reload();
                } else {
                    toastr.error(res.message || 'Update failed');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const firstError = Object.values(xhr.responseJSON.errors || {})[0];
                    toastr.error(Array.isArray(firstError) ? firstError[0] : 'Validation failed');
                } else {
                    toastr.error('Server error');
                }
            }
        });
    });

    $(document).on('change', '.serviceCheck', function() {
        toggleQty(this);
    });
    $(document).on('input', '.serviceQty', function() { calculateTotal(); });

    $(document).on('change', 'input[name="paid_at"], #reference, #amount', function() {
        validatePaymentFields(document.getElementById('EditForm'));
    });

    $(document).on('click', '#editAddGuestBtn', function() {
        const guestContainer = $('#editGuestContainer');
        const currentGuests = guestContainer.find('.guest-card').length;
        const maxGuests = parseInt($('#noOfGuests').val()) || 1;

        if (currentGuests >= maxGuests) {
            toastr.warning('You can only add ' + maxGuests + ' guest(s).');
            return;
        }

        guestContainer.append(createGuestForm(currentGuests));
    });

    $(document).on('click', '.removeGuestBtn', function() {
        const guestCard = $(this).closest('.guest-card');
        guestCard.remove();

        $('#editGuestContainer .guest-card').each(function(index) {
            $(this).attr('data-index', index);
            $(this).find('h6').text('Guest ' + (index + 1));
            $(this).find('input[type="radio"]').attr('value', index);
            $(this).find('input, select, textarea').each(function() {
                const name = $(this).attr('name');
                if (!name) return;
                $(this).attr('name', name.replace(/guests\[\d+\]/, `guests[${index}]`));
            });
        });
    });

    $(document).on('click', '.addProofBtn', function() {
        const guestIndex = $(this).data('guest');
        const proofContainer = $(this).closest('.guest-card').find('.proofContainer');
        const proofCount = proofContainer.find('.proof-row').length;
        proofContainer.append(getProofRow(guestIndex, proofCount, false));
    });

    $(document).on('click', '.removeProofBtn', function() {
        const proofRow = $(this).closest('.proof-row');
        if (proofRow.siblings('.proof-row').length === 0 && proofRow.closest('.proofContainer').find('.proof-row').length === 1) {
            toastr.warning('At least one ID proof is required');
            return;
        }
        proofRow.remove();
    });

    $(document).on('blur', '.guestMobile', function() {
        const mobile = $(this).val().trim();
        if (!mobile) return;

        const guestCard = $(this).closest('.guest-card');
        const guestIndex = guestCard.data('index');

        $.ajax({
            url: '/guest/find',
            type: 'GET',
            data: { mobile },
            success: function(response) {
                const guest = response.data;
                if (!guest || !guest.id) return;

                guestCard.find(`input[name="guests[${guestIndex}][name]"]`).val(guest.full_name ?? '');
                guestCard.find(`input[name="guests[${guestIndex}][email]"]`).val(guest.email ?? '');
                guestCard.find(`textarea[name="guests[${guestIndex}][address]"]`).val(guest.address ?? '');
                guestCard.find(`input[name="guests[${guestIndex}][dob]"]`).val(guest.dob ?? '');

                const countrySelect = guestCard.find(`select[name="guests[${guestIndex}][country_id]"]`);
                const stateSelect = guestCard.find(`select[name="guests[${guestIndex}][state_id]"]`);
                countrySelect.val(guest.country_id || '');

                if (guest.country_id) {
                    hydrateGuestStateSelection(countrySelect[0], stateSelect[0], guest.state_id ?? null);
                }

                loadGuestProofs(guestCard, guest.id_proofs ?? [], guestIndex);
            }
        });
    });

    // =====================================
    // GLOBAL GST CONFIG
    // =====================================
    let gstConfig = {
        is_applicable: 0,
        cgst: 0,
        sgst: 0,
        igst: 0,
        type: 'exclusive'
    };

    // =====================================
    // CONFIRMATION MODAL / CHECKOUT FLOW
    // =====================================
    let actionUrl = '';
    let modalButton = null;

    function openConfirmationModal(url, title = 'Confirm Action', message = 'Are you sure you want to proceed?') {
        actionUrl = url;
        $('#confirmationModalTitle').text(title);
        $('#confirmationModalBody').html('<p>' + message + '</p>');
        $('#confirmationModal').modal('show');
        modalButton = $('#confirmationModalConfirmBtn')[0];
    }

    function showButtonLoader(button) {
        if (!button) return;
        button.disabled = true;
        const originalText = button.innerHTML;
        button.setAttribute('data-original-text', originalText);
        button.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Processing...
        `;
    }

    function resetButtonLoader(button) {
        if (!button) return;
        const originalText = button.getAttribute('data-original-text');
        if (originalText) button.innerHTML = originalText;
        button.disabled = false;
    }

    $('#confirmationModalConfirmBtn').on('click', function() {
        if (!actionUrl) return;
        showButtonLoader(modalButton);

        $.ajax({
            url: actionUrl,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#confirmationModal').modal('hide');
                resetButtonLoader(modalButton);

                if (response.status) {
                    toastr.success(response.message || 'Action completed successfully.');
                    location.reload();
                } else {
                    toastr.error(response.message || 'Action failed');
                }
            },
            error: function(xhr, status, error) {
                resetButtonLoader(modalButton);
                console.error(error);
                toastr.error('Something went wrong.');
            }
        });
    });

    $(document).on('click', '.checkout-btn', function() {
        const bookingId = $(this).data('id');
        const title = $(this).data('title') || 'Confirm Check-Out';
        const message = $(this).data('message') || 'Are you sure you want to check-out this booking?';
        const color = $(this).data('color') || 'primary';

        $('#confirmationModal .modal-header')
            .removeClass('bg-primary bg-warning bg-danger bg-success one-bg-danger')
            .addClass('bg-' + color);

        $('#confirmationModalConfirmBtn')
            .removeClass('btn-primary btn-warning btn-danger btn-success one-btn-danger')
            .addClass('btn-' + color);

        openConfirmationModal('/bookings/' + bookingId + '/checkout', title, message);
    });

    $(document).on('click', '.downloadInvoiceBtn', function() {
        const bookingId = $(this).data('id');

        $.ajax({
            url: `/invoice-management/generate-invoice/${bookingId}/invoice-data`,
            type: 'GET',
            success: function(res) {
                if (!res.invoice_id) {
                    toastr.info('No invoice found for this booking.');
                    return;
                }

                window.open(`/invoice-management/invoices/${res.invoice_id}/download`, '_blank');
            },
            error: function() {
                toastr.error('Unable to download invoice.');
            }
        });
    });

    $(document).on('click', '.generateInvoiceBtn', function() {

        let bookingId = $(this).data('id');

        $.ajax({
            url: `/invoice-management/generate-invoice/${bookingId}/invoice-data`,
            type: 'GET',
            success: function(res) {
                window.hotelStateId = res.hotel_state_id;
                window.guestStateId = res.guest_state_id;

                const rateLabel = (res.booking_type === 'hourly') ? 'Hourly Rate' : 'Room Rate';
                $('#invoice_booking_id').val(bookingId);
                $('#invoiceModalTitle').text('Generate Invoice - ' + rateLabel);

                invoiceItemIndex = 0;
                $('#invoiceItemsBody').html('');

                // Fill customer
                $('#inv_name').val(res.name);
                $('#inv_email').val(res.email);
                $('#inv_address').val(res.address);
                $('#country').val(res.country).trigger('change');

                setTimeout(() => {
                    $('#state').val(res.state).trigger('change');
                }, 1000);
                $('#inv_gstin').val(res.gstin);
                $('#inv_date').val(res.invoice_date);

                // Store GST config globally
                gstConfig = res.gst;

                // Reset table
                $('#invoiceItemsBody').html('');

                // Add ROOM ROW (base room charge)
                const roomQty = Number(res.no_of_rooms || 1);
                const perRoomRate = roomQty > 0 ? Number(res.room_price || 0) / roomQty : Number(res.room_price || 0);

                $('#invoiceItemsBody').append(generateRoomRow({
                    desc: 'Room Charges',
                    rate: perRoomRate,
                    qty: roomQty
                }));

                // Add extra-hour surcharge as separate row when applicable
                if (Number(res.extra_room_charge || 0) > 0) {
                    const extraHours = Number(res.hourly_extra_hours || 0);
                    const extraChargePerHour = extraHours > 0 ? Number(res.extra_room_charge || 0) / extraHours : Number(res.extra_room_charge || 0);

                    $('#invoiceItemsBody').append(generateExtraChargeRow({
                        amount: extraChargePerHour,
                        hours: extraHours,
                        qty: extraHours
                    }));
                }

                if (Array.isArray(res.additional_services) && res.additional_services.length) {
                    res.additional_services.forEach(function(service) {
                        const $row = generateServiceRow({
                            service_id: service.id,
                            desc: service.name,
                            rate: service.unit_price,
                            qty: service.quantity || 1,
                            discount: 0
                        });
                        $('#invoiceItemsBody').append($row);
                    });
                }

                calculateSummary();

                $('#invoiceModal').modal('show');
                toggleGSTColumns();
            }
        });
    });

    function getGSTType() {
        if (!gstConfig.is_applicable) return 'none';

        return (hotelStateId == guestStateId) ? 'cgst_sgst' : 'igst';
    }
    // =====================================
    // GENERATE ROOM ROW (FIRST ROW)
    // =====================================
    function generateRoomRow(data = {}) {
        const index = invoiceItemIndex++;
        return `
    <tr data-item-index="${index}">
        <input type="hidden" name="items[${index}][item_type]" value="room">
        <td><input type="text" name="items[${index}][description]" class="form-control desc" value="${data.desc || 'Room Charges'}" readonly></td>
        <td><input type="text" name="items[${index}][hsn]" class="form-control hsn"></td>
        <td><input type="number" name="items[${index}][rate]" class="form-control rate" value="${data.rate || 0}"></td>
        <td><input type="number" name="items[${index}][qty]" class="form-control qty" value="${data.qty || 1}"></td>
        <td><input type="text" name="items[${index}][subtotal]" class="form-control subtotal" readonly></td>
        <td><input type="number" name="items[${index}][discount]" class="form-control discount" value="0"></td>
        <td><input type="text" name="items[${index}][taxable]" class="form-control taxable" readonly></td>
        <td><input type="number" name="items[${index}][gst]" class="form-control gst" readonly></td>
        <td><input type="text" name="items[${index}][cgst]" class="form-control cgst" readonly></td>
        <td><input type="text" name="items[${index}][sgst]" class="form-control sgst" readonly></td>
        <td><input type="text" name="items[${index}][total]" class="form-control total" readonly></td>
        <td></td>
    </tr>`;
    }

    function generateExtraChargeRow(data = {}) {
        const hours = Number(data.hours || 0);
        const amount = Number(data.amount || 0);
        const qty = Number(data.qty || 1);
        const label = hours > 0 ? `Extra Charges (${hours}h)` : 'Extra Charges';
        const index = invoiceItemIndex++;

        return `
    <tr data-item-index="${index}">
        <input type="hidden" name="items[${index}][item_type]" value="extra">
        <td><input type="text" name="items[${index}][description]" class="form-control desc" value="${label}" readonly></td>
        <td><input type="text" name="items[${index}][hsn]" class="form-control hsn"></td>
        <td><input type="number" name="items[${index}][rate]" class="form-control rate" value="${amount}"></td>
        <td><input type="number" name="items[${index}][qty]" class="form-control qty" value="${qty}"></td>
        <td><input type="text" name="items[${index}][subtotal]" class="form-control subtotal" readonly></td>
        <td><input type="number" name="items[${index}][discount]" class="form-control discount" value="0"></td>
        <td><input type="text" name="items[${index}][taxable]" class="form-control taxable" readonly></td>
        <td><input type="number" name="items[${index}][gst]" class="form-control gst" readonly></td>
        <td><input type="text" name="items[${index}][cgst]" class="form-control cgst" readonly></td>
        <td><input type="text" name="items[${index}][sgst]" class="form-control sgst" readonly></td>
        <td><input type="text" name="items[${index}][total]" class="form-control total" readonly></td>
        <td></td>
    </tr>`;
    }

    function getServiceOptions(selectedServiceId = '') {
        if (!Array.isArray(hotelAdditionalServices) || !hotelAdditionalServices.length) {
            return '<option value="">No services available</option>';
        }

        return ['<option value="">Select additional service</option>']
            .concat(hotelAdditionalServices.map(function(service) {
                const selected = String(service.id) === String(selectedServiceId) ? 'selected' : '';
                return `<option value="${service.id}" data-cost="${service.cost}" ${selected}>${service.name}</option>`;
            }))
            .join('');
    }

    function bindServiceRowEvents(row) {
        const select = row.find('.additional-service-select');
        const rateInput = row.find('.rate');
        const qtyInput = row.find('.qty');
        const descInput = row.find('.desc');
        const hiddenIdInput = row.find('.additional-service-id');

        const applySelectedService = function(service) {
            if (!service) {
                rateInput.val(0);
                rateInput.prop('disabled', false).prop('readonly', false);
                descInput.val('');
                hiddenIdInput.val('');
                row.find('input[name$="[item_type]"]').val('service');
                return;
            }

            const unitPrice = Number(service.cost || 0);
            rateInput.val(unitPrice.toFixed(2));
            rateInput[0].value = unitPrice.toFixed(2);
            rateInput.prop('readonly', true);
            descInput.val(service.name);
            row.find('input[name$="[item_type]"]').val('service');
            hiddenIdInput.val(service.id);
            rateInput.trigger('change');
            rateInput.trigger('input');
            qtyInput.trigger('change');
            qtyInput.trigger('input');
        };

        select.off('change.serviceRow').on('change.serviceRow', function() {
            const selectedServiceId = $(this).val();
            const service = hotelAdditionalServices.find(function(item) {
                return String(item.id) === String(selectedServiceId);
            });

            applySelectedService(service);
            calculateSummary();
        });

        select.off('change.serviceSelect').on('change.serviceSelect', function() {
            const selectedServiceId = $(this).val();
            const service = hotelAdditionalServices.find(function(item) {
                return String(item.id) === String(selectedServiceId);
            });

            applySelectedService(service);
            calculateSummary();
        });

        if (select.val()) {
            const selectedServiceId = select.val();
            const service = hotelAdditionalServices.find(function(item) {
                return String(item.id) === String(selectedServiceId);
            });
            applySelectedService(service);
            calculateSummary();
        }
    }

    // =====================================
    // GENERATE SERVICE ROW
    // =====================================
    function generateServiceRow(data = {}) {
        const index = invoiceItemIndex++;
        const selectedServiceId = data.service_id || '';
        const selectedService = hotelAdditionalServices.find(function(service) {
            return String(service.id) === String(selectedServiceId);
        });
        const selectedRate = selectedService ? Number(selectedService.cost || 0) : Number(data.rate || 0);

        const row = `
    <tr data-item-index="${index}">
        <input type="hidden" name="items[${index}][item_type]" value="service">
        <input type="hidden" name="items[${index}][additional_service_id]" class="additional-service-id" value="${selectedServiceId}">
        <td>
            <select name="items[${index}][service_select]" class="form-control additional-service-select">
                ${getServiceOptions(selectedServiceId)}
            </select>
            <input type="hidden" name="items[${index}][description]" class="form-control desc" value="${selectedService ? selectedService.name : (data.desc || '')}" readonly>
        </td>
        <td><input type="text" name="items[${index}][hsn]" class="form-control hsn" value="${data.hsn || ''}"></td>
        <td><input type="number" name="items[${index}][rate]" class="form-control rate" value="${selectedRate.toFixed(2)}" ${selectedService ? 'readonly' : ''}></td>
        <td><input type="number" name="items[${index}][qty]" class="form-control qty" value="${data.qty || 1}" min="1"></td>
        <td><input type="text" name="items[${index}][subtotal]" class="form-control subtotal" readonly></td>
        <td><input type="number" name="items[${index}][discount]" class="form-control discount" value="${data.discount ?? 0}"></td>
        <td><input type="text" name="items[${index}][taxable]" class="form-control taxable" readonly></td>
        <td><input type="number" name="items[${index}][gst]" class="form-control gst" readonly></td>
        <td><input type="text" name="items[${index}][cgst]" class="form-control cgst" readonly></td>
        <td><input type="text" name="items[${index}][sgst]" class="form-control sgst" readonly></td>
        <td><input type="text" name="items[${index}][total]" class="form-control total" readonly></td>
        <td>
            <button type="button" class="btn btn-danger btn-sm removeRow">X</button>
        </td>
    </tr>`;

        const $row = $(row);
        bindServiceRowEvents($row);
        return $row;
    }


    // =====================================
    // ADD ROW
    // =====================================
    $('#addRowBtn').on('click', function() {
        const $row = generateServiceRow();
        $('#invoiceItemsBody').append($row);
    });

    $('#invoiceModal').on('hidden.bs.modal', function() {
        invoiceItemIndex = 0;
        $('#invoiceItemsBody').html('');
        $('#CreateForm')[0].reset();
        $('#invoice_booking_id').val('');
        $('#invoiceModalTitle').text('Generate Invoice');
    });


    // =====================================
    // REMOVE ROW
    // =====================================
    $(document).on('click', '.removeRow', function() {
        $(this).closest('tr').remove();
        calculateSummary();
    });


    // =====================================
    // CALCULATION LOGIC (CORE 🔥)
    // =====================================


    function toggleGSTColumns() {
        let gstType = getGSTType();

        if (gstType === 'igst') {
            $('.cgst').closest('td').hide();
            $('.cgst_head').closest('th').hide();
            $('.sgst_head').closest('th').hide();
            $('.sgst').closest('td').hide();
        } else {
            $('.cgst').closest('td').show();
            $('.sgst').closest('td').show();
            $('.cgst_head').closest('th').show();
            $('.sgst_head').closest('th').show();
        }
    }

    // =====================================
    // SUMMARY CALCULATION
    // =====================================
    function calculateSummary() {

        let totalTaxable = 0;
        let totalGST = 0;
        let grandTotal = 0;

        $('#invoiceItemsBody tr').each(function() {

            totalTaxable += parseFloat($(this).find('.taxable').val()) || 0;

            let cgst = parseFloat($(this).find('.cgst').val()) || 0;
            let sgst = parseFloat($(this).find('.sgst').val()) || 0;

            totalGST += cgst + sgst;

            grandTotal += parseFloat($(this).find('.total').val()) || 0;
        });

        $('#total_taxable').val(totalTaxable.toFixed(2));
        $('#total_gst').val(totalGST.toFixed(2));
        $('#grand_total').val(grandTotal.toFixed(2));

        // TODO: subtract advance later
        $('#balance_amount').val(grandTotal.toFixed(2));
    }

    $(document).ready(function() {
        $('#state').on('change', function() {
            guestStateId = $(this).val();
            toggleGSTColumns();

            // Recalculate all rows
            $('#invoiceItemsBody tr').each(function() {
                $(this).find('.rate').trigger('input');
            });
        });

        $(document).on('input', '.rate, .qty, .discount', function() {

            let row = $(this).closest('tr');

            let rate = parseFloat(row.find('.rate').val()) || 0;
            let qty = parseFloat(row.find('.qty').val()) || 0;
            let discount = parseFloat(row.find('.discount').val()) || 0;

            let subtotal = rate * qty;

            let gstType = getGSTType();

            let gstPercent = 0;

            if (gstType === 'cgst_sgst') {
                gstPercent = parseFloat(gstConfig.cgst) + parseFloat(gstConfig.sgst);
            } else if (gstType === 'igst') {
                gstPercent = parseFloat(gstConfig.igst);
            }
            let taxable = 0;
            let gstAmount = 0;
            let total = 0;

            if (!gstConfig.is_applicable) {

                taxable = subtotal - discount;
                total = taxable;

            } else if (gstConfig.type === 'inclusive') {

                let temp = subtotal - discount;

                taxable = (temp * 100) / (100 + gstPercent);
                gstAmount = temp - taxable;

                total = temp;

            } else {

                taxable = subtotal - discount;
                gstAmount = (taxable * gstPercent) / 100;

                total = taxable + gstAmount;
            }

            let cgst = 0,
                sgst = 0,
                igst = 0;

            if (gstType === 'igst') {
                igst = gstAmount;
                cgst = 0;
                sgst = 0;
            } else if (gstType === 'cgst_sgst') {
                cgst = gstAmount / 2;
                sgst = gstAmount / 2;
                igst = 0;
            }
            console.log({
                subtotal,
                discount,
                taxable,
                gstAmount,
                cgst,
                sgst,
                igst,
                total
            });
            row.find('.subtotal').val(subtotal.toFixed(2));
            row.find('.taxable').val(taxable.toFixed(2));
            row.find('.cgst').val(cgst.toFixed(2));
            row.find('.sgst').val(sgst.toFixed(2));
            row.find('.total').val(total.toFixed(2));

            calculateSummary();
        });
    });

    // --- Checkout button handler for generateInvoice ---
    $(document).on('click', '.checkout-btn', function() {
        const bookingId = $(this).data('id');
        const bookingCode = $(this).data('booking-code') || 'N/A';
        
        $.ajax({
            url: `/bookings/${bookingId}`,
            type: 'GET',
            headers: {
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.status && response.data) {
                    const booking = response.data.booking || {};
                    
                    $('#checkout_booking_id').val(bookingId);
                    $('#checkoutBookingCode').text(bookingCode);
                    
                    const checkInAt = booking.actual_check_in_at;
                    if (checkInAt) {
                        const checkInDate = new Date(checkInAt);
                        const formattedCheckIn = checkInDate.toLocaleString('en-IN', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit',
                            hour12: true
                        });
                        $('#displayCheckInDateTime').text(formattedCheckIn);
                    } else {
                        $('#displayCheckInDateTime').text('Not checked in yet');
                    }
                    
                    const today = new Date();
                    const todayFormatted = today.toISOString().split('T')[0];
                    $('#checkoutDate').val(todayFormatted);
                    
                    const hours = String(today.getHours()).padStart(2, '0');
                    const minutes = String(today.getMinutes()).padStart(2, '0');
                    $('#checkoutTime').val(`${hours}:${minutes}`);
                    
                    $('#checkoutModal').modal('show');
                } else {
                    toastr.error('Failed to load booking details');
                }
            },
            error: function() {
                toastr.error('Error loading booking information');
            }
        });
    });

    // --- Confirm checkout ---
    $(document).on('click', '#confirmCheckout', function() {
        const bookingId = $('#checkout_booking_id').val();
        const checkoutDate = $('#checkoutDate').val();
        const checkoutTime = $('#checkoutTime').val();

        if (!bookingId || !checkoutDate || !checkoutTime) {
            toastr.warning('Please fill in all required fields');
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true);
        const originalText = btn.html();
        btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...');

        $.ajax({
            url: `/bookings/${bookingId}/checkout`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            data: {
                checkout_date: checkoutDate,
                checkout_time: checkoutTime
            },
            success: function(response) {
                if (response.status) {
                    toastr.success(response.message || 'Guest checked out successfully');
                    $('#checkoutModal').modal('hide');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    toastr.error(response.message || 'Checkout failed');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'An error occurred during checkout';
                toastr.error(message);
            },
            complete: function() {
                btn.prop('disabled', false);
                btn.html(originalText);
            }
        });
    });
</script>
@endsection