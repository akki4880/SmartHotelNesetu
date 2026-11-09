@extends('layouts.master')

@section('title', 'Bookings')
@section('mainbreadcrumb', 'Bookings')

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

    /* Upgrade room type cards */
    #upgradeRoomModal .upgrade-type-card,
    #upgradeRoomModal .upgrade-room-card {
        width: 100%;
        height: 100%;
        padding: 16px 10px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        background: #fff;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    #upgradeRoomModal .upgrade-type-card:hover,
    #upgradeRoomModal .upgrade-room-card:hover {
        border-color: #f0ad4e;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    #upgradeRoomModal .upgrade-type-card.selected,
    #upgradeRoomModal .upgrade-room-card.selected {
        border: 2px solid #f0ad4e;
        background: #fff8e8;
    }

    #upgradeRoomModal .upgrade-type-card .upgrade-icon,
    #upgradeRoomModal .upgrade-room-card .upgrade-icon {
        font-size: 28px;
        margin-bottom: 10px;
        color: #6c757d;
    }

    #upgradeRoomModal .upgrade-type-card.selected .upgrade-icon,
    #upgradeRoomModal .upgrade-room-card.selected .upgrade-icon {
        color: #d58512;
    }

    #upgradeRoomModal .upgrade-card-name {
        font-weight: 600;
        margin-bottom: 8px;
        overflow-wrap: anywhere;
    }

    #upgradeRoomModal .upgrade-card-status {
        display: inline-block;
        margin-top: 8px;
    }

    /* Booking details modal */
    #showModal .booking-modal {
        border: 0;
        border-radius: 12px;
        overflow: hidden;
        background: #f5f8fd;
        box-shadow: 0 15px 50px rgba(15, 35, 70, 0.22);
        color: #253858;
    }

    #showModal .booking-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 24px;
        border-bottom: 0; 
        color: #fff;
    }

    #showModal .booking-header-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    #showModal .booking-header-icon {
        display: flex;
        width: 42px;
        height: 42px;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.14);
        font-size: 20px;
    }

    #showModal .modal-title {
        margin: 0;
        color: #fff;
        font-size: 22px;
        font-weight: 700;
    }

    #showModal .booking-close {
        padding: 0;
        margin: 0;
        color: #fff;
        font-size: 30px;
        opacity: 0.9;
    }

    #showModal .booking-close:hover {
        opacity: 1;
    }

    #showModal .booking-body {
        padding: 18px;
        background: #f5f8fd;
    }

    #showModal .booking-card {
        background: #fff;
        border: 1px solid #dce7f7;
        border-radius: 9px;
        overflow: hidden;
    }

    #showModal .booking-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 62px;
        padding: 12px 17px;
        background: linear-gradient(100deg, #f0f6ff, #f9fbff);
        border-bottom: 1px solid #e3ecf9;
    }

    #showModal .booking-section-icon {
        display: flex;
        flex: 0 0 38px;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #e0edff;
        color: #1763bb;
        font-size: 17px;
    }

    #showModal .booking-section-title {
        min-width: 0;
    }

    #showModal .booking-section-title h6 {
        margin: 0 0 4px;
        color: #173766;
        font-size: 14px;
        font-weight: 700;
    }

    #showModal .booking-section-title small {
        display: block;
        color: #7c8da8;
        font-size: 11px;
    }

    #showModal .booking-header-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 7px;
        margin-left: auto;
    }

    #showModal .booking-header-actions .btn {
        padding: 8px 11px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        border-radius: 5px;
    }

    #showModal .booking-card-body {
        padding: 10px 17px;
    }

    #showModal .booking-info-column {
        padding-top: 3px;
        padding-bottom: 3px;
    }

    #showModal .booking-info-column + .booking-info-column {
        border-left: 1px solid #e7edf6;
    }

    #showModal .booking-info-row {
        display: grid;
        grid-template-columns: 30px minmax(100px, 135px) minmax(0, 1fr);
        align-items: center;
        gap: 10px;
        min-height: 43px;
        padding: 6px 0;
    }

    #showModal .booking-info-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 29px;
        height: 29px;
        border-radius: 50%;
        background: #edf5ff;
        color: #2165b4;
        font-size: 13px;
    }

    #showModal .booking-label {
        color: #687c9b;
        font-size: 12px;
        font-weight: 500;
    }

    #showModal .booking-value {
        color: #253858;
        font-size: 12px;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    #showModal .booking-status {
        display: inline-block;
        padding: 5px 9px;
        border-radius: 20px;
        background: #e3f8ed;
        color: #12834b;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
    }

    #showModal .booking-payment-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        min-height: 39px;
        padding: 6px 0;
        border-bottom: 1px solid #edf1f7;
    }

    #showModal .booking-payment-row > span {
        color: #687c9b;
        font-size: 12px;
    }

    #showModal .booking-payment-row > strong {
        min-width: 100px;
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        text-align: left;
    }

    #showModal .amount-neutral {
        color: #566983;
        background: #f0f3f8;
    }

    #showModal .amount-success {
        color: #10845b;
        background: #e0f7ed;
    }

    #showModal .amount-danger {
        color: #dc3545;
        background: #ffe6e8;
    }

    #showModal .booking-payment-highlight {
        margin-top: 3px;
        padding: 9px 10px;
        border: 0;
        border-radius: 6px;
        background: #f1f6ff;
    }

    #showModal .booking-payment-highlight > span {
        color: #244b85;
        font-weight: 700;
    }

    #showModal .booking-table-wrap {
        overflow-x: auto;
        width: 100%;
    }

    #showModal .booking-table {
        min-width: 760px;
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    #showModal .booking-table thead th {
        padding: 12px 10px;
        background: #2474c8;
        border: 0;
        border-right: 1px solid rgba(255,255,255,.12);
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        white-space: nowrap;
    }

    #showModal .booking-table tbody td {
        padding: 11px 10px;
        color: #405575;
        border-bottom: 1px solid #edf1f7;
        font-size: 11px;
        vertical-align: middle;
    }

    #showModal .booking-empty {
        padding: 25px 12px !important;
        text-align: center;
        color: #8a9ab2 !important;
    }

    #showModal .booking-services-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    #showModal .booking-service-item {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        padding: 13px;
        border: 1px solid #dce8fa;
        border-radius: 7px;
        background: #f8fbff;
    }

    #showModal .booking-service-name {
        display: block;
        margin-bottom: 5px;
        color: #607493;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
    }

    #showModal .booking-service-price {
        color: #173766;
        font-size: 12px;
        font-weight: 700;
    }

    #showModal .booking-service-empty {
        grid-column: 1 / -1;
        padding: 15px;
        color: #8796ad;
        font-size: 12px;
        text-align: center;
    }

    @media (max-width: 991.98px) {
        #showModal .booking-header-actions {
            width: 100%;
            justify-content: flex-start;
            margin-left: 0;
        }

        #showModal .booking-card-header {
            flex-wrap: wrap;
        }

        #showModal .booking-info-column + .booking-info-column {
            border-left: 0;
            border-top: 1px solid #e7edf6;
        }

        #showModal .booking-services-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        #showModal .modal-dialog {
            width: auto;
            margin: 8px;
        }

        #showModal .booking-header {
            padding: 14px;
        }

        #showModal .modal-title {
            font-size: 16px;
        }

        #showModal .booking-body {
            padding: 10px;
        }

        #showModal .booking-card-header {
            padding: 11px;
            gap: 8px;
        }

        #showModal .booking-card-body {
            padding: 8px 12px;
        }

        #showModal .booking-info-row {
            grid-template-columns: 27px 100px minmax(0, 1fr);
            gap: 7px;
        }

        #showModal .booking-services-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@php
// Get all request parameters except _token or page
$filters = request()->except(['_token', 'page']);

// Check if any filter has a non-empty value
$hasFilter = collect($filters)->filter(fn($value) => !empty($value))->isNotEmpty();
$hotelRateTypes = auth()->user()->hotel?->getBookingRateOptions() ?? ['room'];
$defaultHotelRateType = auth()->user()->hotel?->getDefaultBookingRateType() ?? 'room';
@endphp
<input type="hidden" id="defaultHotelRateType" value="{{ $defaultHotelRateType }}">
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

                        <!-- Booking Code -->
                        <div class="col-3">
                            <div class="form-group">
                                <label>Booking Code</label>
                                <input type="text" name="booking_code" class="form-control"
                                    value="{{ request('booking_code') }}" placeholder="Booking Code">
                            </div>
                        </div>

                        <!-- Guest Name -->
                        <div class="col-3">
                            <div class="form-group">
                                <label>Guest Name</label>
                                <input type="text" name="guest_name" class="form-control"
                                    value="{{ request('guest_name') }}" placeholder="Guest Name">
                            </div>
                        </div>

                        <!-- Check In Date -->
                        <div class="col-3">
                            <div class="form-group">
                                <label>Check In Date</label>
                                <input type="date" name="check_in_date" class="form-control"
                                    value="{{ request('check_in_date') }}">
                            </div>
                        </div>

                        <!-- Check Out Date -->
                        <div class="col-3">
                            <div class="form-group">
                                <label>Check Out Date</label>
                                <input type="date" name="check_out_date" class="form-control"
                                    value="{{ request('check_out_date') }}">
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-3 mt-3">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="">Select Status</option>
                                    <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending
                                    </option>
                                    <option value="confirmed" {{ request('status')=='confirmed' ? 'selected' : '' }}>
                                        Confirmed</option>
                                    <option value="checked_in" {{ request('status')=='checked_in' ? 'selected' : '' }}>
                                        Checked In</option>
                                    <option value="checked_out" {{ request('status')=='checked_out' ? 'selected' : ''
                                        }}>Checked Out</option>
                                    <option value="cancelled" {{ request('status')=='cancelled' ? 'selected' : '' }}>
                                        Cancelled</option>
                                    <option value="no_show" {{ request('status')=='no_show' ? 'selected' : '' }}>No Show
                                    </option>
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
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0 fw-bolder">Bookings</h3>

                <div class="d-flex align-items-center gap-2">

                    <!-- Filters -->
                    <form method="GET" action="{{ url()->current() }}" id="todayFilterForm" class="d-flex">

                        <!-- Check-in Today -->
                        <div class="form-check me-2 mt-1">
                            <input type="checkbox" name="checkin_today" value="1" class="form-check-input mt-1"
                                id="checkinToday" onchange="document.getElementById('todayFilterForm').submit();" {{
                                request('checkin_today') ? 'checked' : '' }}>
                            <label class="form-check-label" for="checkinToday">
                                Check-in Today
                            </label>
                        </div>

                        <!-- Check-out Today -->
                        <div class="form-check mx-2 mt-1">
                            <input type="checkbox" name="checkout_today" value="1" class="form-check-input mt-1"
                                id="checkoutToday" onchange="document.getElementById('todayFilterForm').submit();" {{
                                request('checkout_today') ? 'checked' : '' }}>
                            <label class="form-check-label" for="checkoutToday">
                                Check-out Today
                            </label>
                        </div>

                    </form>

                    <!-- Create -->
                    <button class="btn btn-danger btn-sm mx-2" data-toggle="modal" data-target="#createModal">
                        <i class="fa-solid fa-plus"></i> Create
                    </button>

                    <!-- Filter -->
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
                                <th width="200">Actions</th>
                                <th>Booking ID</th>
                                <th>Guest</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Rooms</th>
                                <th>Room Type</th>
                                <th>Allotted Room</th>
                                <th>Guests</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($bookings as $booking)

                            @php
                            $today = \Carbon\Carbon::today();
                            $checkin = \Carbon\Carbon::parse($booking->check_in_date)->startOfDay();
                            @endphp

                            <tr>

                                {{-- ACTIONS --}}
                                <td>
                                    <div class="d-flex" style="width:200px;gap:4px;">

                                        {{-- Show --}}
                                        <a href="javascript:void(0)"
                                            class="btn btn-outline-primary btn-icon btn-sm showBookingBtn"
                                            data-id="{{ $booking->id }}" title="Show Booking">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        @if($booking->status != 'cancelled')
                                        {{-- EDIT --}}
                                        <a href="javascript:void(0)"
                                            class="btn btn-outline-success btn-icon btn-sm editBookingBtn"
                                            data-id="{{ $booking->id }}" title="Edit Booking">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        @endif

                                        {{-- CHECK IN --}}
                                        @if($today->eq($checkin) && !$booking->actual_check_in_at && $booking->status ==
                                        'confirmed')
                                        {{-- NO SHOW --}}
                                        <a href="javascript:void(0)"
                                            class="btn btn-outline-warning btn-icon btn-sm no-show-btn"
                                            data-id="{{ $booking->id }}" data-title="No Show"
                                            data-message="Mark booking {{ $booking->booking_code }} as No Show?"
                                            data-color="warning" title="No Show">
                                            <i class="fa-solid fa-user-xmark"></i>
                                        </a>

                                        <a href="javascript:void(0)"
                                            class="btn one-btn-outline-success btn-icon btn-sm checkin-btn"
                                            data-id="{{ $booking->id }}" title="Check In">
                                            <i class="fa-solid fa-door-open"></i>
                                        </a>
                                        @endif

                                        @if(
                                            !in_array($booking->status, ['cancelled', 'no_show', 'checked_out'], true) &&
                                            !empty($booking->actual_check_in_at) &&
                                            empty($booking->actual_check_out_at)
                                        )

                                        {{-- CHANGE ROOM --}}
                                        <a href="javascript:void(0)"
                                            class="btn btn-outline-info btn-icon btn-sm changeRoomBtn"
                                            data-id="{{ $booking->id }}"
                                            data-code="{{ $booking->booking_code }}"
                                            title="Change Room">
                                            <i class="fa-solid fa-right-left"></i>
                                        </a>

                                        {{-- UPGRADE / CHANGE ROOM TYPE --}}
                                        <a href="javascript:void(0)"
                                            class="btn btn-outline-warning btn-icon btn-sm upgradeRoomBtn"
                                            data-id="{{ $booking->id }}"
                                            data-code="{{ $booking->booking_code }}"
                                            data-checkin="{{ $booking->check_in_date }}"
                                            data-room-type-id="{{ $booking->roomDistributions->first()?->room_type_id }}"
                                            title="Upgrade Room">
                                            <i class="fa-solid fa-arrow-up"></i>
                                        </a>
                                        @endif


                                        {{-- CHECK OUT --}}
                                        @if((($booking->status === 'checked_in' || $booking->actual_check_in_at) && !$booking->actual_check_out_at))

                                        <a href="javascript:void(0)"
                                            class="btn btn-outline-primary btn-icon btn-sm checkout-btn"
                                            data-id="{{ $booking->id }}"
                                            data-booking-code="{{ $booking->booking_code }}"
                                            title="Check Out">
                                            <i class="fa-solid fa-door-closed"></i>
                                        </a>

                                        @endif


                                        {{-- CANCEL --}}
                                        @if(!$booking->actual_check_in_at && $booking->status != 'cancelled')

                                        <a href="javascript:void(0)"
                                            class="btn one-btn-outline-danger btn-icon btn-sm cancel-btn"
                                            data-id="{{ $booking->id }}" data-title="Cancel Booking"
                                            title="Cancel Booking" data-color="one-bg-danger"
                                            data-message="Are you sure you want to cancel booking {{ $booking->booking_code }}?">
                                            <i class="fa-solid fa-ban"></i>
                                        </a>

                                        @endif

                                    </div>
                                </td>

                                {{-- BOOKING CODE --}}
                                <td>
                                    <strong>{{ $booking->booking_code }}</strong>
                                </td>

                                <td>{{ $booking->primaryGuest->full_name ?? 'N/A' }}</td>

                                @php
                                $checkInDateTime = $booking->actual_check_in_at
                                ? \Carbon\Carbon::parse($booking->actual_check_in_at)->format('d M Y, h:i A')
                                : \Carbon\Carbon::parse($booking->check_in_date)->format('d M Y');

                                $checkOutDateTime = $booking->actual_check_out_at
                                ? \Carbon\Carbon::parse($booking->actual_check_out_at)->format('d M Y, h:i A')
                                : \Carbon\Carbon::parse($booking->check_out_date)->format('d M Y');
                                @endphp

                                <td>
                                    <div>{{ $checkInDateTime }}</div>
                                </td>

                                <td>
                                    <div>{{ $checkOutDateTime }}</div>
                                </td>

                                <td>{{ $booking->no_of_rooms }}</td>

                                <td>
                                    @if($booking->status != 'cancelled')
                                    @php
                                    $roomType = $booking->roomDistributions
                                    ->filter(fn($distribution) => $distribution->roomType && ($distribution->room_number_id || (bool) $distribution->is_room_assigned))
                                    ->unique('room_type_id');
                                    @endphp

                                    @forelse($roomType as $distribution)
                                    <span class="badge badge-primary mb-1">
                                        {{ $distribution->roomType->name }}
                                    </span>
                                    @empty
                                    <span class="badge badge-secondary">Not Assigned</span>
                                    @endforelse
                                    @else
                                    <span class="badge one-badge-danger">Cancelled</span>
                                    @endif
                                </td>
                                <td>
                                    @if($booking->status != 'cancelled')
                                    @php
                                    $allottedRooms = $booking->roomDistributions
                                    ->filter(fn($distribution) => $distribution->room_number_id || (bool) $distribution->is_room_assigned)
                                    ->pluck('roomNumber.room_number')
                                    ->filter()
                                    ->unique()
                                    ->values();
                                    @endphp

                                    @if($allottedRooms->isNotEmpty())
                                    @foreach($allottedRooms as $roomNumber)
                                    <span class="badge badge-info mb-1">
                                        {{ $roomNumber }}
                                    </span>
                                    @endforeach
                                    @else
                                    <span class="badge badge-secondary">Not Allotted</span>
                                    @endif
                                    @else
                                    <span class="badge one-badge-danger">Cancelled</span>
                                    @endif
                                </td>

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
<x-create-booking-modal
    :hotel-rate-types="$hotelRateTypes"
    :default-hotel-rate-type="$defaultHotelRateType"
    :agents="$agents"
    :additional-services="$additional_services"
/>

<x-edit-booking-modal
    :agents="$agents"
    :additional-services="$additional_services"
/>

<x-checkin-modal />

{{-- Show Modal --}}
<div class="modal fade" id="showModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content booking-modal">

            <div class="modal-header booking-header bg-primary">
                <div class="booking-header-left">
                    <div class="booking-header-icon">
                        <i class="fal fa-calendar-alt"></i>
                    </div>

                    <div>
                        <h6 class="modal-title" id="bookingDetailsModalLabel">
                            BRN Number: <span id="modalBrn">—</span>
                        </h6>
                    </div>
                </div>

                <button type="button" class="close booking-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body booking-body">

                <div class="booking-card mb-3">
                    <div class="booking-card-header">
                        <div class="booking-section-icon">
                            <i class="fal fa-user"></i>
                        </div>

                        <div class="booking-section-title">
                            <h6>Client Details</h6>
                            <small>Guest contact information</small>
                        </div> 
                    </div>

                    <div class="booking-card-body">
                        <div class="row">
                            <div class="col-lg-6 booking-info-column">
                                <div class="booking-info-row">
                                    <span class="booking-info-icon"><i class="fal fa-user"></i></span>
                                    <span class="booking-label">Guest Name</span>
                                    <span class="booking-value" id="modalGuestName">—</span>
                                </div>

                                <div class="booking-info-row">
                                    <span class="booking-info-icon"><i class="fal fa-envelope"></i></span>
                                    <span class="booking-label">Email</span>
                                    <span class="booking-value" id="modalEmail">—</span>
                                </div>

                                <div class="booking-info-row">
                                    <span class="booking-info-icon"><i class="fal fa-id-card"></i></span>
                                    <span class="booking-label">Mobile No.</span>
                                    <span class="booking-value" id="modalMobile">—</span>
                                </div>
                            </div>

                            <div class="col-lg-6 booking-info-column">
                                <div class="booking-info-row">
                                    <span class="booking-info-icon"><i class="fal fa-map-marker-alt"></i></span>
                                    <span class="booking-label">Address</span>
                                    <span class="booking-value" id="modalAddress">—</span>
                                </div>

                                <div class="booking-info-row">
                                    <span class="booking-info-icon"><i class="fal fa-check-circle"></i></span>
                                    <span class="booking-label">Status</span>
                                    <span class="booking-value">
                                        <span id="modalStatus" class="booking-status">—</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-lg-6 mb-3 mb-lg-0">
                        <div class="booking-card h-100">
                            <div class="booking-card-header">
                                <div class="booking-section-icon">
                                    <i class="fal fa-bed"></i>
                                </div>

                                <div class="booking-section-title">
                                    <h6>Room Details</h6>
                                    <small>Stay and accommodation</small>
                                </div>
                            </div>

                            <div class="booking-card-body">
                                <div class="booking-info-row">
                                    <span class="booking-info-icon"><i class="fal fa-calendar-check"></i></span>
                                    <span class="booking-label">Check-in Date</span>
                                    <span class="booking-value" id="modalCheckIn">—</span>
                                </div>

                                <div class="booking-info-row">
                                    <span class="booking-info-icon"><i class="fal fa-calendar-times"></i></span>
                                    <span class="booking-label">Check-out Date</span>
                                    <span class="booking-value" id="modalCheckOut">—</span>
                                </div>

                                <div class="booking-info-row" id="modalTotalHoursRow" style="display:none;">
                                    <span class="booking-info-icon"><i class="fal fa-hourglass-half"></i></span>
                                    <span class="booking-label">Total Hours</span>
                                    <span class="booking-value" id="modalTotalHours">—</span>
                                </div>

                                <div class="booking-info-row">
                                    <span class="booking-info-icon"><i class="fal fa-door-open"></i></span>
                                    <span class="booking-label">Room Type</span>
                                    <span class="booking-value" id="modalRoomType">—</span>
                                </div>

                                <div class="booking-info-row">
                                    <span class="booking-info-icon"><i class="fal fa-hashtag"></i></span>
                                    <span class="booking-label">Room Number</span>
                                    <span class="booking-value" id="modalRoomNumber">—</span>
                                </div>

                                <div class="booking-info-row">
                                    <span class="booking-info-icon"><i class="fal fa-door-closed"></i></span>
                                    <span class="booking-label">No. of Rooms</span>
                                    <span class="booking-value" id="modalRooms">—</span>
                                </div>

                                <div class="booking-info-row">
                                    <span class="booking-info-icon"><i class="fal fa-clock"></i></span>
                                    <span class="booking-label">No. of Nights</span>
                                    <span class="booking-value" id="modalNights">—</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="booking-card h-100">
                            <div class="booking-card-header">
                                <div class="booking-section-icon">
                                    <i class="fal fa-wallet"></i>
                                </div>

                                <div class="booking-section-title">
                                    <h6>Payment Details</h6>
                                    <small>Charges and outstanding balance</small>
                                </div>
                            </div>

                            <div class="booking-card-body">
                                <div class="booking-payment-row">
                                    <span>Total Deal Amount</span>
                                    <strong class="amount-neutral">₹ <span id="modalTotal">0.00</span></strong>
                                </div>

                                <div class="booking-payment-row">
                                    <span>Services Amount</span>
                                    <strong class="amount-neutral">₹ <span id="modalServices">0.00</span></strong>
                                </div>

                                <div class="booking-payment-row">
                                    <span>Discount</span>
                                    <strong class="amount-success">₹ <span id="modalDiscount">0.00</span></strong>
                                </div>

                                <div class="booking-payment-row">
                                    <span>Total Paid Amount</span>
                                    <strong class="amount-success">₹ <span id="modalPaid">0.00</span></strong>
                                </div>

                                <div class="booking-payment-row booking-payment-highlight">
                                    <span>Total Outstanding</span>
                                    <strong class="amount-danger">₹ <span id="modalOutstanding">0.00</span></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="booking-card mb-3">
                    <div class="booking-card-header">
                        <div class="booking-section-icon">
                            <i class="fal fa-receipt"></i>
                        </div>

                        <div class="booking-section-title">
                            <h6>Payment Trail</h6>
                            <small>Transaction-wise payment details</small>
                        </div>

                        <!-- <div class="ml-auto">
                            <button type="button" class="btn btn-success btn-sm" id="addPaymentDetailsBtn">
                                <i class="fal fa-plus mr-1"></i>
                                Add Payment Details
                            </button>
                        </div> -->
                    </div>

                    <div class="booking-table-wrap">
                        <table class="table booking-table mb-0">
                            <thead>
                                <tr>
                                    <th>SR#</th>
                                    <th>Mode</th>
                                    <th>Date</th>
                                    <th>Transaction ID</th>
                                    <th>Amount</th>
                                    <!-- <th>Discount</th>
                                    <th>Due Balance</th> -->
                                    <th>Note</th>
                                </tr>
                            </thead>

                            <tbody id="modalPaymentTrail">
                                <tr>
                                    <td colspan="8" class="booking-empty">
                                        <i class="fal fa-file-invoice-dollar"></i>
                                        <span>No payment records available</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="booking-card">
                    <div class="booking-card-header">
                        <div class="booking-section-icon">
                            <i class="fal fa-concierge-bell"></i>
                        </div>

                        <div class="booking-section-title">
                            <h6>Additional Services</h6>
                            <small>Extra services added to this booking</small>
                        </div>
                    </div>

                    <div class="booking-card-body">
                        <div id="modalServicesList" class="booking-services-grid">
                            <div class="booking-service-empty">
                                <i class="fal fa-info-circle mr-1"></i>
                                No additional services available
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title">Edit</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>

            <div class="modal-body">
                <form id="EditForm">
                    @csrf
                    <div class="container my-3">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Check-in Date</label>
                                <input type="date" id="checkInDate" name="check_in_date" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label>Check-out Date</label>
                                <input type="date" id="checkOutDate" name="check_out_date" class="form-control" required>
                            </div>
                            <div class="col-md-2">
                                <label>No of Guests</label>
                                <input type="number" id="noOfGuests" name="no_of_guests" class="form-control" min="1"
                                    value="1" required>
                            </div>
                            <div class="col-md-2">
                                <label>No of Rooms</label>
                                <input type="number" id="noOfRooms" name="no_of_rooms" class="form-control" min="1"
                                    value="1" required>
                            </div>
                            <div class="col-md-2 align-self-end">
                                <button class="btn btn-primary" id="searchAvailability">Search</button>
                            </div>
                        </div>
                    </div>
                    <div id="selectedRoomContainer" class="container"></div>
                    <div id="availabilityLoader" style="display:none;" class="text-center mt-4">
                        <div class="spinner-border text-primary" role="status" style="width:3rem;height:3rem;">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted" id="availabilityLoaderText">Checking room availability...</p>
                    </div>
                    <!-- Result cards -->
                    <div class="container d-flex justify-content-between" id="availabilityResults">
                        <!-- Room type cards will appear here -->
                    </div>

                    <div id="bookingFormSection" style="display:none;" class="container">

                        <hr>

                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="mb-3">Guest Details</h5>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-primary mb-3 addGuestBtn" id="editAddGuestBtn">
                                    <i class="fa fa-plus"></i> Add Guest
                                </button>
                            </div>
                        </div>
                        <!-- <div class="row mb-2">
                        <div class="col-md-4">
                            <label>Mobile</label>
                            <input type="text" id="guestMobile" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>Name</label>
                            <input type="text" id="guestName" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>Email</label>
                            <input type="email" id="guestEmail" class="form-control">
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-4">
                            <label>Address</label>
                            <input type="text" id="guestAddress" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>DOB</label>
                            <input type="date" id="guestDob" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>Country</label>
                            <select id="country" class="form-control select2">
                                <option value="">Select</option>
                                @foreach($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-4">
                            <label>State</label>
                            <select id="state" class="form-control select2">
                                <option value="">Select</option>
                                @foreach($states as $state)
                                <option value="{{ $state->id }}">{{ $state->state_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div> -->

                        <div id="editGuestContainer" class="guestContainer"></div>
                        <hr>
                        <div class="row">
                            @if($agents->count() > 0)
                            <hr>
                            <div class="col-md-3">
                                <label>Select Agent</label>

                                <select name="agent_id" class="form-control select2">
                                    <option value="">Select Agent</option>

                                    @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}">
                                        {{ $agent->name }}
                                        @if($agent->company_name)
                                        ({{ $agent->company_name }})
                                        @endif
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <div class="col-md-9">
                                <label for="remark">Remark</label>
                                <textarea name="remark" id="remark" class="form-control" rows="3"
                                    placeholder="Enter booking remark...">{{ old('remark') }}</textarea>
                            </div>
                        </div>
                        <!-- <div id="editIdProofContainer" class="form-group">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="mb-0">Guest ID Proofs</label>
                            <button type="button" class="btn btn-sm btn-success" id="addEditIdRow">
                                <i class="fa fa-plus"></i> Add
                            </button>
                        </div>

                    </div> -->
                        <hr>
                        <h5 class="mb-3">Additional Services</h5>
                        <div id="serviceList" class="container row">
                            @foreach($additional_services as $service)
                            <div class="form-check col-4 d-flex align-items-center gap-2 mb-2">

                                <input class="form-check-input serviceCheck" type="checkbox" name="services[]"
                                    value="{{ $service->id }}" data-price="{{ $service->cost }}"
                                    id="service{{ $service->id }}" onchange="toggleQty(this)">

                                <label class="form-check-label me-2" for="service{{ $service->id }}">
                                    {{ $service->service_name }} – ₹{{ $service->cost }}
                                </label>

                                <!-- Quantity Input -->
                                <input type="number" name="service_qty[{{ $service->id }}]"
                                    class="form-control serviceQty mx-2" min="1" value="1" style="width:80px;" disabled
                                    oninput="calculateTotal()">

                            </div>
                            @endforeach
                        </div>

                        <hr>

                        <h5>Payment</h5>

                        <div class="row mb-2">
                            <div class="col-md-3">
                                <label>Method</label>
                                <select id="paymentMethod" name="payment_method" class="form-control">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="upi">UPI</option>
                                    <option value="online">Online</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Paid At</label>
                                <input type="datetime-local" name="paid_at" id="paidAt" class="form-control">
                            </div>

                            <div class="col-md-3">
                                <label>Reference</label>
                                <input type="text" name="reference" id="reference" class="form-control">
                            </div>

                            <div class="col-md-3">
                                <label>Amount</label>
                                <input type="number" name="amount" id="amount" class="form-control">
                            </div>
                        </div>

                        <div id="paymentHistory"></div>

                        <hr>

                        <div class="row">
                            <div class="col">
                                <h4 class="">
                                    Total: ₹<span id="finalAmount">0</span>
                                </h4>
                                <input type="hidden" name="total_amount" id="totalAmountInput">
                            </div>
                            <div class="col d-flex justify-content-end">
                                <!-- <button class="btn btn-success" id="confirmBookingBtn">
                                    Confirm Booking
                                </button> -->
                            </div>
                        </div>

                    </div>
                </form>
            </div>

            <div class="modal-footer border-top">
                <button type="reset" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="updateBtn">Update</button>
            </div>
        </div>
    </div>
</div>

{{-- Check in Modal --}}


{{-- LightBox Preview --}}
<div class="modal fade" id="imagePreviewModal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <img src="" id="previewImage" class="img-fluid">
                <br><br>
                <a href="" download id="modalDownloadBtn" class="btn btn-primary">
                    Download
                </a>
            </div>
        </div>
    </div>
</div>


{{-- Change Room Modal --}}
<div class="modal fade" id="changeRoomModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fa-solid fa-right-left mr-2"></i>
                    Change Room: <span id="changeRoomBookingCode"></span>
                </h5>

                <button type="button" class="close text-white" data-dismiss="modal">
                    <i class="fal fa-times"></i>
                </button>
            </div>

            <div class="modal-body">
                <form id="changeRoomForm">
                    @csrf

                    <input type="hidden" id="change_room_booking_id" name="booking_id">
                    <input type="hidden" id="selected_change_room_id" name="new_room_id">
                    <input type="hidden" id="change_room_old_id" name="old_room_id">

                    <div class="mb-3">
                        <h6 class="font-weight-bold mb-2">
                            <i class="fa-solid fa-door-open mr-1"></i>
                            Selected room to change
                        </h6>
                        <div class="row" id="currentChangeRoomsContainer">
                            <div class="col-12 text-muted">Loading current rooms...</div>
                        </div>
                    </div>

                    <hr>

                    <div class="row" id="changeRoomsContainer">
                        <!-- Room cards will come here -->
                    </div>

                    <small class="text-danger d-block" id="changeRoomValidation"></small>

                    <div class="form-group mt-3">
                        <label>Reason (Optional)</label>
                        <textarea class="form-control" id="change_room_reason"
                            name="reason" rows="2" maxlength="500"></textarea>
                    </div>
                </form>
            </div>

            <div class="modal-footer border-top">
                <button class="btn btn-secondary" data-dismiss="modal">Close</button>

                <button class="btn btn-info" id="confirmChangeRoom">
                    <i class="fa-solid fa-right-left mr-1"></i>
                    Confirm Room Change
                </button>
            </div>

        </div>
    </div>
</div>

{{-- UPGRADE / CHANGE ROOM TYPE MODAL --}}
<div class="modal fade" id="upgradeRoomModal" tabindex="-1"
    role="dialog" aria-labelledby="upgradeRoomModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <form id="upgradeRoomForm">
            @csrf

            <div class="modal-content">
                <input type="hidden" id="upgrade_old_room_id" name="old_room_id">
                {{-- HEADER --}}
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="upgradeRoomModalLabel">
                        <i class="fa-solid fa-arrow-up mr-2"></i>
                        Upgrade Room Type:
                        <span id="upgrade_booking_code"></span>
                    </h5>

                    <button type="button" class="close text-dark"
                        data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">
                            <i class="fal fa-times"></i>
                        </span>
                    </button>
                </div>

                <div class="modal-body">

                    <input type="hidden" id="upgrade_booking_id"
                        name="booking_id">

                    {{-- KEEP EXISTING FIELD FOR BACKEND COMPATIBILITY --}}
                    <select class="d-none" id="new_room_type_id"
                        name="new_room_type_id" required>
                        <option value="">Select room type</option>
                        @foreach($roomTypes as $roomType)
                        <option value="{{ $roomType->id }}">
                            {{ $roomType->name }}
                        </option>
                        @endforeach
                    </select>

                    {{-- EFFECTIVE DATE --}}
                    <div class="form-group">
                        <label for="effective_date">
                            <i class="fa-regular fa-calendar mr-1"></i>
                            Effective From
                        </label>

                        <input type="date" class="form-control"
                            id="effective_date" name="effective_date" required>

                        <small class="form-text text-muted">
                            The room type change applies from this date.
                        </small>
                    </div>

                    <div class="mt-3 mb-3">
                        <h6 class="font-weight-bold mb-2">
                            <i class="fa-solid fa-door-open mr-1"></i>
                            Select room to upgrade
                        </h6>
                        <div class="row" id="upgradeCurrentRoomsContainer">
                            <div class="col-12 text-muted">Loading current rooms...</div>
                        </div>
                    </div>

                    <hr>

                    {{-- ROOM TYPE CARDS --}}
                    <div class="mb-3">
                        <h6 class="font-weight-bold mb-1">
                            <i class="fa-solid fa-bed mr-1"></i>
                            Select New Room Type
                        </h6>

                        <small class="text-muted">
                            Choose the room type you want to upgrade to.
                        </small>
                    </div>

                    <div class="row" id="upgradeRoomTypesContainer">
                        <div class="col-12 text-muted">
                            Select a room type to continue.
                        </div>
                    </div>

                    <small class="text-danger d-block mb-3"
                        id="upgradeRoomTypeValidation"></small>

                    <hr>

                    {{-- REPLACEMENT ROOM CARDS --}}
                    <div class="mb-3">
                        <h6 class="font-weight-bold mb-1">
                            <i class="fa-solid fa-door-open mr-1"></i>
                            Select Replacement Room
                        </h6>

                        <small class="text-muted">
                            Select an available room matching the chosen room type.
                            Leave unselected if automatic assignment is supported.
                        </small>
                    </div>

                    {{-- Keep the existing field name and ID --}}
                    <input type="hidden" id="upgrade_new_room_id"
                        name="new_room_id" value="">

                    <div class="row" id="upgradeRoomsContainer">
                        <div class="col-12 text-muted">
                            Select a room type first to load available rooms.
                        </div>
                    </div>

                    <small class="text-danger d-block mb-3"
                        id="upgradeRoomValidation"></small>

                    {{-- REASON --}}
                    <div class="form-group mt-3">
                        <label for="upgrade_reason">
                            Reason (Optional)
                        </label>

                        <textarea class="form-control" id="upgrade_reason"
                            name="reason" rows="2" maxlength="500"
                            placeholder="Enter reason for room type change..."></textarea>
                    </div>

                </div>

                {{-- FOOTER --}}
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary"
                        data-dismiss="modal">
                        Close
                    </button>

                    <button type="submit" class="btn btn-warning"
                        id="confirmUpgradeRoom">
                        <i class="fa-solid fa-arrow-up mr-1"></i>
                        Update Room Type
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- CHECKOUT MODAL --}}
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

@include('layouts.confirmationModal')
<script>
    // Elements
    const checkInInput = document.getElementById('checkInDate');
    const checkOutInput = document.getElementById('checkOutDate');
    const guestsInput = document.getElementById('noOfGuests');
    const roomsInput = document.getElementById('noOfRooms');
    const availabilityContainer = document.getElementById('availabilityResults');

    // Function to clear results
    function clearAvailabilityResults() {
        availabilityContainer.innerHTML = '';
        availabilityContainer.style.display = 'none';
    }

    checkInInput.addEventListener('change', function() {

        const checkInDate = this.value;
        const selectedRateType = document.querySelector('input[name="rate_type"]:checked')?.value || document.getElementById('defaultHotelRateType')?.value || 'room';

        if (!checkInDate) return;

        checkOutInput.min = checkInDate;

        if (selectedRateType === 'hourly') {
            checkOutInput.value = checkInDate;
            return;
        }

        if (checkOutInput.value && checkOutInput.value < checkInDate) {
            checkOutInput.value = '';
        }

        const nextDate = new Date(checkInDate);
        nextDate.setDate(nextDate.getDate() + 1);
        const nextDay = nextDate.toISOString().split('T')[0];

        if (!checkOutInput.value || checkOutInput.value <= checkInDate) {
            checkOutInput.value = nextDay;
        }
    });

    // Add event listeners
    checkInInput.addEventListener('change', clearAvailabilityResults);
    checkOutInput.addEventListener('change', clearAvailabilityResults);
    guestsInput.addEventListener('change', clearAvailabilityResults);
    roomsInput.addEventListener('change', clearAvailabilityResults);

    document.addEventListener("DOMContentLoaded", function() {

        let today = new Date().toISOString().split('T')[0];

        document.getElementById("checkInDate").setAttribute("min", today);
        document.getElementById("checkOutDate").setAttribute("min", today);

    });
</script>

<script>
    $('#checkInDate').on('change', function() {

        let checkIn = $(this).val();
        if (!checkIn) return;

        const selectedRateType = document.querySelector('input[name="rate_type"]:checked')?.value || document.getElementById('defaultHotelRateType')?.value || 'room';

        if (selectedRateType === 'hourly') {
            $('#checkOutDate').val(checkIn);
            return;
        }

        let date = new Date(checkIn);
        date.setDate(date.getDate() + 1);

        let nextDay = date.toISOString().split('T')[0];

        $('#checkOutDate').val(nextDay);
    });


    // document.getElementById('searchAvailability').addEventListener('click', async function() {
    //     const containers = document.getElementById('availabilityResults');
    //     containers.style.display = 'none';
    //     const checkIn = document.getElementById('checkInDate').value;
    //     const checkOut = document.getElementById('checkOutDate').value;
    //     const noOfGuests = document.getElementById('noOfGuests').value;
    //     const noOfRooms = document.getElementById('noOfRooms').value;

    //     if (!checkIn || !checkOut) {
    //         toastr.warning("Select both check-in and check-out dates.");
    //         return;
    //     }

    //     const loader = document.getElementById('availabilityLoader');


    //     // 🔹 Show Loader
    //     loader.style.display = 'block';

    //     // 🔹 Disable button while loading
    //     this.disabled = true;
    //     this.innerHTML = 'Searching...';

    //     try {
    //         const res = await fetch(
    //             `/availability/search?check_in_date=${checkIn}&check_out_date=${checkOut}&no_of_guests=${noOfGuests}&no_of_rooms=${noOfRooms}`
    //         );

    //         const json = await res.json();

    //         const container = document.getElementById('availabilityResults');
    //         container.innerHTML = '';

    //         if (!json.status || !json.data.length) {
    //             container.innerHTML = '<p class="text-danger">No rooms available for selected dates.</p>';
    //             return;
    //         }

    //         // ✅ Create ONE table
    //         const table = document.createElement('table');
    //         table.className = 'table m-0 table-bordered table-sm';

    //         table.innerHTML = `
    //         <thead class="bg-primary text-white">
    //             <tr>
    //                 <th>Room Type</th>
    //                 <th>Available</th>
    //                 <th>Guests</th>
    //                 <th>Total Price</th>
    //                 <th>Action</th>
    //             </tr>
    //         </thead>
    //         <tbody></tbody>
    //         `;

    //         const tbody = table.querySelector('tbody');

    //         // ✅ Add rows
    //         json.data.forEach(room => {

    //             const row = document.createElement('tr');

    //             row.innerHTML = `
    //         <td>${room.room_type}</td>
    //         <td>${room.available} / ${room.total_rooms}</td>
    //         <td>
    //             ${room.base_guests}
    //             ${room.extra_allowed > 0 ? `(+${room.extra_allowed} extra)` : ''}
    //         </td>
    //         <td>₹${room.total_price}</td>
    //         <td>
    //             ${
    //                 room.available > 0
    //                 ? `<button class="btn btn-success btn-sm bookNowBtn"
    //                     data-room='${JSON.stringify(room)}'>
    //                     Book Now
    //                 </button>`
    //                 : `<span class="badge btn-sm bg-danger">Sold Out</span>`
    //             }
    //         </td>
    //     `;

    //             tbody.appendChild(row);
    //         });

    //         container.appendChild(table);

    //         setTimeout(() => {
    //             table.classList.add('fade-enter-active');
    //         }, 50);

    //         loader.style.display = 'none';
    //         container.style.display = 'block';

    //         // 🔹 Enable button again
    //         this.disabled = false;
    //         this.innerHTML = 'Search';

    //     } catch (error) {
    //         toastr.error("Failed to fetch availability");
    //     }

    //     // 🔹 Hide Loader
    //     loader.style.display = 'none';
    //     container.style.display = 'block';

    //     // 🔹 Enable button again
    //     this.disabled = false;
    //     this.innerHTML = 'Search';
    // });

    function formatDateForInput(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function formatTimeForInput(date) {
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${hours}:${minutes}`;
    }

    function setHourlyTimeDefaults() {
        const rateType = document.querySelector('input[name="rate_type"]:checked')?.value || document.getElementById('defaultHotelRateType')?.value || 'room';
        if (rateType !== 'hourly') return;

        const checkInTimeInput = document.getElementById('checkInTime');
        const checkOutTimeInput = document.getElementById('checkOutTime');

        if (!checkInTimeInput || !checkOutTimeInput) return;

        const now = new Date();
        const checkInTime = formatTimeForInput(now);
        const checkOutTime = formatTimeForInput(new Date(now.getTime() + 120 * 60 * 1000));

        if (!checkInTimeInput.value) {
            checkInTimeInput.value = checkInTime;
        }

        if (!checkOutTimeInput.value) {
            checkOutTimeInput.value = checkOutTime;
        }
    }

    function toggleHourlyBookingFields() {
        const rateType = document.querySelector('input[name="rate_type"]:checked')?.value || document.getElementById('defaultHotelRateType')?.value || 'room';
        const hourlyFields = document.querySelectorAll('.hourly-time-row');
        const showHourly = rateType === 'hourly';

        hourlyFields.forEach(row => {
            row.style.display = showHourly ? 'flex' : 'none';
        });

        if (showHourly) {
            setHourlyTimeDefaults();
        }
    }

    function applyCheckinCheckoutDefaults() {
        const checkInInput = document.getElementById('checkInDate');
        const checkOutInput = document.getElementById('checkOutDate');
        const selectedRateType = document.querySelector('input[name="rate_type"]:checked')?.value || document.getElementById('defaultHotelRateType')?.value || 'room';
        const checkInDate = checkInInput?.value;

        if (!checkInInput || !checkOutInput || !checkInDate) {
            return;
        }

        checkOutInput.min = checkInDate;

        if (selectedRateType === 'hourly') {
            const checkInTime = document.getElementById('checkInTime')?.value;
            const checkOutTime = document.getElementById('checkOutTime')?.value;

            if (checkInTime && checkOutTime) {
                const parseTimeToMinutes = (time) => {
                    const [hours, minutes] = time.split(':').map(Number);
                    return hours * 60 + minutes;
                };

                const checkInMinutes = parseTimeToMinutes(checkInTime);
                const checkOutMinutes = parseTimeToMinutes(checkOutTime);

                if (checkInMinutes >= 12 * 60 && checkOutMinutes < 12 * 60) {
                    const nextDate = new Date(checkInDate + 'T00:00:00');
                    nextDate.setDate(nextDate.getDate() + 1);
                    checkOutInput.value = formatDateForInput(nextDate);
                    return;
                }

                if (checkInMinutes >= checkOutMinutes && checkOutMinutes >= 12 * 60) {
                    checkOutInput.value = checkInDate;
                    return;
                }
            }

            checkOutInput.value = checkInDate;
            return;
        }

        const currentCheckout = checkOutInput.value;
        const nextDate = new Date(checkInDate + 'T00:00:00');
        nextDate.setDate(nextDate.getDate() + 1);
        const formattedNextDate = formatDateForInput(nextDate);

        if (!currentCheckout || currentCheckout <= checkInDate) {
            checkOutInput.value = formattedNextDate;
        }
    }

    async function searchAvailability() {
        const container = document.getElementById('availabilityResults');
        const loader = document.getElementById('availabilityLoader');
        const searchBtn = document.getElementById('searchAvailability');

        const checkIn = document.getElementById('checkInDate').value;
        const checkOut = document.getElementById('checkOutDate').value;
        const noOfGuests = document.getElementById('noOfGuests').value;
        const noOfRooms = document.getElementById('noOfRooms').value;
        const selectedRateType = document.querySelector('input[name="rate_type"]:checked')?.value || document.getElementById('defaultHotelRateType')?.value || 'room';
        const checkInTime = document.getElementById('checkInTime')?.value || '';
        const checkOutTime = document.getElementById('checkOutTime')?.value || '';

        if (!checkIn || !checkOut) {
            clearAvailabilityResults();
            return;
        }

        if (selectedRateType === 'hourly' && (!checkInTime || !checkOutTime)) {
            clearAvailabilityResults();
            return;
        }

        container.style.display = 'none';
        container.innerHTML = '';
        loader.style.display = 'block';

        if (searchBtn) {
            searchBtn.disabled = true;
            searchBtn.innerHTML = 'Searching...';
        }

        try {
            const params = new URLSearchParams({
                check_in_date: checkIn,
                check_out_date: checkOut,
                no_of_guests: noOfGuests,
                no_of_rooms: noOfRooms,
                rate_type: selectedRateType,
            });

            if (selectedRateType === 'hourly') {
                params.set('check_in_time', checkInTime);
                params.set('check_out_time', checkOutTime);
            }

            const res = await fetch(`/availability/search?${params.toString()}`);

            if (!res.ok) {
                const json = await res.json().catch(() => ({}));
                const message = json.message || 'Unable to fetch availability.';
                container.innerHTML = `<p class="text-danger">${message}</p>`;
                toastr.error(message);
                return;
            }

            const json = await res.json();

            if (!json.status || !json.data.length) {
                container.innerHTML = '<p class="text-danger">No rooms available for selected dates.</p>';
            } else {
                const table = document.createElement('table');
                table.className = 'table m-0 table-bordered table-sm';

                table.innerHTML = `
            <thead class="bg-primary text-white">
                <tr>
                    <th>Room Type</th>
                    <th>Available</th>
                    <th>Dirty</th>
                    <th>Guests</th>
                    <th>Total Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
            `;

                const tbody = table.querySelector('tbody');

                json.data.forEach(room => {
                    const dirtyRoomNumbers = Array.isArray(room.dirty_room_numbers) ? room.dirty_room_numbers : [];
                    const dirtyText = dirtyRoomNumbers.length
                        ? `${room.dirty_rooms || 0} (${dirtyRoomNumbers.slice(0, 3).join(', ')}${dirtyRoomNumbers.length > 3 ? '...' : ''})`
                        : `${room.dirty_rooms || 0}`;

                    const row = document.createElement('tr');

                    row.innerHTML = `
                <td>${room.room_type}</td>
                <td>${room.available} / ${room.total_rooms}</td>
                <td>${dirtyText}</td>
                <td>
                    ${room.base_guests}
                    ${room.extra_allowed > 0 ? `(+${room.extra_allowed} extra)` : ''}
                </td>
                <td>₹${room.total_price}</td>
                <td>
                    ${room.available >= (room.rooms_required || 1)
                            ? `<button class="btn btn-success btn-sm bookNowBtn"
                            data-room='${JSON.stringify(room)}'>
                            Book Now
                        </button>`
                            : `<span class="badge bg-danger">Not Available</span>`
                        }
                </td>
                `;

                    tbody.appendChild(row);
                });

                container.appendChild(table);
            }
        } catch (error) {
            toastr.error('Failed to fetch availability');
        } finally {
            loader.style.display = 'none';
            container.style.display = 'block';

            if (searchBtn) {
                searchBtn.disabled = false;
                searchBtn.innerHTML = 'Search';
            }
        }
    }

    document.addEventListener('change', function (event) {
        if (event.target && (event.target.id === 'checkInDate' || event.target.id === 'checkInTime' || event.target.id === 'checkOutTime')) {
            applyCheckinCheckoutDefaults();
        }

        if (event.target && event.target.name === 'rate_type') {
            toggleHourlyBookingFields();
            applyCheckinCheckoutDefaults();
            clearAvailabilityResults();

            const checkIn = document.getElementById('checkInDate')?.value;
            const checkOut = document.getElementById('checkOutDate')?.value;
            if (checkIn && checkOut) {
                searchAvailability();
            }
        }
    });

    toggleHourlyBookingFields();
    applyCheckinCheckoutDefaults();

    document.getElementById('searchAvailability').addEventListener('click', searchAvailability);

    let selectedRoom = null;

    document.getElementById('confirmBookingBtn').addEventListener('click', confirmBooking);


    /* =========================================================
       GLOBAL STATE
    ========================================================= */
    let baseAmount = 0;


    /* =========================================================
       BOOK NOW CLICK
    ========================================================= */
    document.addEventListener('click', function(e) {

        if (!e.target.classList.contains('bookNowBtn')) return;
        const availability = document.getElementById('availabilityResults');
        availability.classList.remove('d-flex');
        availability.style.display = 'none';
        const loader = document.getElementById('availabilityLoader');
        const loaderText = document.getElementById('availabilityLoaderText');


        // Change loader text
        loaderText.innerText = "Preparing booking form...";

        // Show loader
        loader.style.display = 'block';

        setTimeout(() => {

            if (e.target.classList.contains('bookNowBtn')) {

                const roomData = JSON.parse(e.target.getAttribute('data-room'));

                showSelectedRoom(roomData);

            }

            selectedRoom = JSON.parse(e.target.dataset.room);
            baseAmount = parseFloat(selectedRoom.total_price);

            // hide availability
            // availability.style.display = 'none';
            // availability.classList.remove('d-flex');

            // show booking form
            document.getElementById('bookingFormSection').style.display = 'block';

            // set amount
            document.getElementById('finalAmount').innerText = baseAmount.toFixed(2);
            document.getElementById('totalAmountInput').value = baseAmount.toFixed(2);

            // uncheck previous selections
            document.querySelectorAll('.serviceCheck').forEach(cb => cb.checked = false);

            const availabilityFormInputs = document.querySelectorAll(
                '#checkInDate, #checkOutDate, #noOfGuests, #noOfRooms, #searchAvailability, #checkInTime, #checkOutTime, input[name="rate_type"]'
            );

            availabilityFormInputs.forEach(el => el.disabled = true);

            // Hide loader
            loader.style.display = 'none';

        }, 500); // smooth transition
    });


    /* =========================================================
       SERVICE PRICE CALCULATION (MULTI SELECT)
    ========================================================= */
    document.querySelectorAll('.serviceCheck').forEach(cb => {
        cb.addEventListener('change', calculateTotal);
    });

    function calculateTotal() {

        let total = baseAmount;

        document.querySelectorAll('.serviceCheck:checked').forEach(cb => {

            const price = parseFloat(cb.dataset.price);

            const qtyInput = cb.closest('.form-check').querySelector('.serviceQty');
            const quantity = parseInt(qtyInput.value) || 1;

            total += price * quantity;
        });

        document.getElementById('totalAmountInput').value = total;

        document.getElementById('finalAmount').innerText = total.toFixed(2);
    }

    function toggleQty(checkbox) {
        const qtyInput = checkbox.closest('.form-check').querySelector('.serviceQty');

        if (checkbox.checked) {
            qtyInput.disabled = false;
        } else {
            qtyInput.disabled = true;
            qtyInput.value = 1; // reset
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

    document.addEventListener('change', function(e) {
        if (!e.target) return;

        if (['paidAt', 'reference', 'amount'].includes(e.target.id)) {
            const root = e.target.closest('form') || e.target.closest('.modal') || document;
            validatePaymentFields(root);
        }
    });

    /* =========================================================
       CONFIRM BOOKING
    ========================================================= */

    async function confirmBooking() {

        if (!selectedRoom) {
            toastr.error("Room not selected");
            return;
        }

        const paymentRoot = document.getElementById('bookingFormSection') || document;
        if (!validatePaymentFields(paymentRoot)) {
            return;
        }

        const selectedRateType = document.querySelector('input[name="rate_type"]:checked')?.value || document.getElementById('defaultHotelRateType')?.value || 'room';

        const formData = new FormData();

        // ==============================
        // ROOM DETAILS
        // ==============================
        formData.append('room_type_id', selectedRoom.room_type_id);
        formData.append('check_in_date', document.getElementById('checkInDate').value);
        formData.append('check_out_date', document.getElementById('checkOutDate').value);
        formData.append('rate_type', selectedRateType);
        formData.append('remark', document.getElementById('remark').value);
        // ==============================
        // BASIC BOOKING FIELDS
        // ==============================
        formData.append('no_of_guests', document.getElementById('noOfGuests').value);
        formData.append('no_of_rooms', document.getElementById('noOfRooms').value);
        formData.append('total_amount', document.getElementById('totalAmountInput').value);
        formData.append('base_amount', document.getElementById('baseAmount').value);

        // ==============================
        // AGENT
        // ==============================
        const agentSelect = document.querySelector('select[name="agent_id"]');
        if (agentSelect && agentSelect.value) {
            formData.append('agent_id', agentSelect.value);
        }

        // ==============================
        // PAYMENT
        // ==============================
        formData.append('payment_method', document.getElementById('paymentMethod').value);
        formData.append('paid_at', document.getElementById('paidAt').value);
        formData.append('reference', document.getElementById('reference').value);
        formData.append('amount_paid', document.getElementById('amount').value);

        // ==============================
        // SERVICES
        // ==============================
        document.querySelectorAll('.serviceCheck:checked').forEach((checkbox) => {

            const qtyInput = checkbox.closest('.form-check').querySelector('.serviceQty');

            formData.append('services[]', checkbox.value);
            formData.append('service_qty[' + checkbox.value + ']', qtyInput.value);
        });

        // ==============================
        // GUESTS + FILES
        // ==============================
        document.querySelectorAll('.guest-card').forEach((card) => {

            card.querySelectorAll('input, select, textarea').forEach(input => {

                if (!input.name) return;

                if (input.type === "file") {

                    if (input.files[0]) {
                        formData.append(input.name, input.files[0]);
                    }

                } else {
                    formData.append(input.name, input.value);
                }

            });
        });


        try {
            $.ajax({
                url: '/bookings',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },

                success: function(json) {
                    if (json.status) {
                        toastr.success(json.message ?? 'Booking Confirmed');

                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr.error(json.message ?? 'Booking failed');
                    }
                },

                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        Object.values(xhr.responseJSON.errors).forEach(function(errorArr) {
                            toastr.error(errorArr[0]);
                        });
                    } else {
                        console.error('Booking AJAX error:', xhr.status, xhr.responseText);

                        toastr.error(
                            xhr.responseJSON?.message ?? 'Server error occurred'
                        );
                    }
                }
            });

        } catch (err) {
            console.error('JavaScript error:', err);
            toastr.error('Server error occurred');
        }

    }

    function showSelectedRoom(room) {

        const container = document.getElementById('selectedRoomContainer');

        container.innerHTML = `
    <div class="card bg-warning shadow-sm">
    <div class="card-body d-flex justify-content-between align-items-center">

        <div class="d-flex align-items-center">
            <h4 class="mb-1 text-dark fw-bold">
                Selected Room: ${room.room_type} (₹${room.total_price})
            </h4>

            <i class="fa-solid fa-circle-info text-dark mx-1"
                   data-bs-toggle="tooltip"
                   data-bs-placement="top" style="font-size:12px;margin-bottom:2px;"
                   title="Guests: ${room.base_guests} ${room.extra_allowed > 0 ? `(+${room.extra_allowed} extra allowed)` : ''}">
                </i>

            <input type="hidden" name="base_amount" id="baseAmount" value="${room.total_price}">
        </div>

        <button class="btn btn-danger btn-sm mx-1" onclick="clearSelectedRoom()">
            Change
        </button>

    </div>
    </div>
    `;

    }

    function clearSelectedRoom() {

        const loader = document.getElementById('availabilityLoader');
        const loaderText = document.getElementById('availabilityLoaderText');
        const containers = document.getElementById('availabilityResults');
        loaderText.innerText = "Loading available rooms...";

        // Show loader
        loader.style.display = 'block';
        containers.style.display = 'none';
        document.getElementById('selectedRoomContainer').innerHTML = '';
        // 3️⃣ Hide booking form section
        document.getElementById('bookingFormSection').style.display = 'none';

        setTimeout(() => {

            // 1️⃣ Remove selected card

            // 2️⃣ Show availability results again
            const availability = document.getElementById('availabilityResults');
            availability.style.display = 'block';
            availability.classList.add('d-flex');



            // 4️⃣ Enable search inputs again
            const availabilityFormInputs = document.querySelectorAll(
                '#checkInDate, #checkOutDate, #noOfGuests, #noOfRooms, #searchAvailability, #checkInTime, #checkOutTime, input[name="rate_type"]'
            );

            availabilityFormInputs.forEach(el => el.disabled = false);

            // 5️⃣ Reset selected room & amount
            selectedRoom = null;
            baseAmount = 0;
            document.getElementById('finalAmount').innerText = '0';

            // 6️⃣ Reset services
            document.querySelectorAll('.serviceCheck').forEach(cb => {

                cb.checked = false;

                const qtyInput = cb.closest('.form-check').querySelector('.serviceQty');
                qtyInput.disabled = true;
                qtyInput.value = 1;

            });

            // Hide loader
            loader.style.display = 'none';
            loaderText.innerText = "Checking room availability...";

        }, 600); // smooth loader effect
    }


    //add new guest 

    let guestIndex = 0;

    function createGuestForm(index) {

        return `
    <div class="card p-3 mb-3 guest-card" data-index="${index}">

    <div class="d-flex justify-content-between">
    <div class="mb-2">
        <h6 class="mb-0">Guest ${index + 1}</h6>
    </div>

    <!-- BOTTOM ACTIONS -->
    <div class="d-flex justify-content-between align-items-center">

        <label class="mb-0 ${index > 0 ? 'mx-2' : ''}">

            <input type="radio" name="primary_guest" value="${index}" ${index == 0 ? 'checked' : ''}>
            Lead Guest
        </label>

        ${index > 0 ? `
            <button type="button" class="btn btn-sm btn-danger removeGuestBtn">
                Remove Guest
            </button>` : ''}

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
            <select name="guests[${index}][country_id]" 
                    class="form-control select2 country-dropdown"
                    data-target="#state_${index}"
                    onchange="loadStatesByCountry(this)">
                <option value="">Select Country</option>
                ${countriesOptions}
            </select>
        </div>

        <div class="col-md-3">
            <label>State</label>
            <select id="state_${index}" 
                    name="guests[${index}][state_id]" 
                    class="form-control select2 state-dropdown">
                <option value="">Select State</option>
            </select>
        </div>

        <div class="col-md-6">
            <label>Address</label>
            <textarea name="guests[${index}][address]" class="form-control"></textarea>
        </div>

    </div>

    <hr>

    <!-- ID PROOFS -->
    <div class="idProofSection">

        <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="mb-0">ID Proofs</label>

            <button type="button" 
                    class="btn btn-sm btn-success addProofBtn"
                    data-guest="${index}">
                <i class="fa fa-plus"></i> Add
            </button>
        </div>

        <div class="proofContainer">
            ${getProofRow(index, 0, true)}
        </div>

    </div>

    </div>
    `;
    }


    let countriesOptions = `
    @foreach($countries as $country)
    <option value="{{ $country->id }}">{{ $country->name }}</option>
    @endforeach
    `;

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

    function getProofRow(guestIndex, proofIndex, isDefault = false) {

        return `
            <div class="row mt-2 proof-row">
                <div class="col-md-4">
                    <label class="small text-muted d-block mb-1">ID Type</label>
                    <input type="text"
                        name="guests[${guestIndex}][id_proofs][${proofIndex}][id_type]"
                        class="form-control"
                        placeholder="ID Type (Aadhar, Passport)"
                        ${!isDefault ? 'required' : ''}>
                </div>

                <div class="col-md-4">
                    <label class="small text-muted d-block mb-1">ID Proof File</label>
                    <input type="file"
                        name="guests[${guestIndex}][id_proofs][${proofIndex}][id_file]"
                        class="form-control"
                        ${!isDefault ? 'required' : ''}>
                </div>

                <div class="col-md-2">
                    ${!isDefault
                ? `<button type="button" class="btn btn-sm btn-danger removeProofBtn">
                                <i class="fa fa-trash"></i>
                        </button>`
                : ``
            }
                </div>
            </div>
            `;
    }

    const createGuestContainer = document.getElementById('createGuestContainer');
    if (createGuestContainer) {
        createGuestContainer.innerHTML = createGuestForm(guestIndex);
    }

    document.addEventListener('click', function(e) {

        const addBtn = e.target.closest('.addGuestBtn');
        if (addBtn) {
            const modal = addBtn.closest('.modal');
            const guestContainer = modal ? modal.querySelector('.guestContainer') : null;

            if (!guestContainer) return;

            const guestInput = modal.querySelector('input[name="no_of_guests"], #noOfGuests');
            const maxGuests = parseInt(guestInput?.value || 1, 10);
            const currentGuests = guestContainer.querySelectorAll('.guest-card').length;

            if (currentGuests >= maxGuests) {
                toastr.warning('You can only add ' + maxGuests + ' guest(s).');
                return;
            }

            guestContainer.insertAdjacentHTML('beforeend', createGuestForm(currentGuests));
            return;
        }

        const removeBtn = e.target.closest('.removeGuestBtn');
        if (removeBtn) {
            const guestCard = removeBtn.closest('.guest-card');
            const guestContainer = guestCard.closest('.guestContainer');

            if (!guestCard || !guestContainer) return;

            guestCard.remove();

            guestContainer.querySelectorAll('.guest-card').forEach(function(card, i) {
                card.setAttribute('data-index', i);
                const heading = card.querySelector('h6');
                if (heading) {
                    heading.textContent = 'Guest ' + (i + 1);
                }

                card.querySelectorAll('input, select, textarea').forEach(function(field) {
                    let name = field.getAttribute('name');

                    if (name) {
                        name = name.replace(/guests\[\d+\]/, 'guests[' + i + ']');
                        field.setAttribute('name', name);
                    }
                });
            });
        }

    });

    document.addEventListener('click', function(e) {

        // ADD PROOF
        if (e.target.closest('.addProofBtn')) {

            const btn = e.target.closest('.addProofBtn');
            const guestIndex = btn.dataset.guest;

            const guestCard = btn.closest('.guest-card');
            const container = guestCard.querySelector('.proofContainer');

            const proofCount = container.querySelectorAll('.proof-row').length;

            container.insertAdjacentHTML(
                'beforeend',
                getProofRow(guestIndex, proofCount, false)
            );
        }

            // REMOVE PROOF
            if (e.target.closest('.removeProofBtn')) {

                const guestCard = e.target.closest('.guest-card');
                const proofRows = guestCard.querySelectorAll('.proof-row');

                if (proofRows.length <= 1) {
                    toastr.warning('At least one ID proof is required');
                    return;
                }

                const proofRow = e.target.closest('.proof-row');
                const proofId = proofRow?.dataset?.proofId || proofRow?.querySelector('[name$="[proof_id]"]')?.value;

                if (proofId) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = `guests[${guestCard.dataset.index}][deleted_proof_ids][]`;
                    hiddenInput.value = proofId;
                    guestCard.appendChild(hiddenInput);
                }

                proofRow.remove();
            }

    });

    $(document).on('blur', '.guestMobile', function() {

        let mobile = $(this).val().trim();
        if (!mobile) return;

        let guestCard = $(this).closest('.guest-card');
        let guestIndex = guestCard.data('index');

        $.ajax({
            url: '/guest/find',
            type: 'GET',
            data: {
                mobile: mobile
            },
            success: function(response) {

                let guest = response.data;

                if (!guest || !guest.id) {

                    clearGuestCard(guestCard, guestIndex);
                    return;
                }

                // console.log(guest.address);
                // Name
                guestCard.find('input[name="guests[' + guestIndex + '][name]"]')
                    .val(guest.full_name ?? '');

                // Email
                guestCard.find('input[name="guests[' + guestIndex + '][email]"]')
                    .val(guest.email ?? '');

                // Address
                guestCard.find('textarea[name="guests[' + guestIndex + '][address]"]')
                    .val(guest.address ?? '');

                // DOB
                guestCard.find('input[name="guests[' + guestIndex + '][dob]"]')
                    .val(guest.dob ?? '');

                // COUNTRY
                let countrySelect = guestCard.find(
                    'select[name="guests[' + guestIndex + '][country_id]"]'
                );
                let stateSelect = guestCard.find(
                    'select[name="guests[' + guestIndex + '][state_id]"]'
                );

                countrySelect.val(guest.country_id);

                if (guest.country_id) {
                    hydrateGuestStateSelection(countrySelect[0], stateSelect[0], guest.state_id ?? null);
                }

                // ✅ Load ID proofs dynamically
                loadGuestProofs(guestCard, guest.id_proofs ?? [], guestIndex);
            },
            error: function(err) {
                console.log(err);
            }
        });
    });

    function clearGuestCard(guestCard, guestIndex) {

        guestCard.find('input[type="text"], input[type="email"], input[type="date"]')
            .not('.guestMobile')
            .val('');

        loadGuestProofs(guestCard, [], guestIndex);
    }

    function loadGuestProofs(guestCard, proofs = [], guestIndex) {

        let container = guestCard.find('.proofContainer');
        container.html('');

        // If no proofs → create 1 mandatory empty row
        if (!proofs || proofs.length === 0) {

            container.append(getProofRow(guestIndex, 0, true));
            return;
        }

        proofs.forEach(function(proof, index) {
            const proofPath = ((proof.id_file_path || '').replace(/^\/+/, ''));
            const proofUrl = proofPath ? '/storage/' + proofPath : '';
            const fileLabel = (proof.id_type || 'ID Proof').replace(/_/g, ' ');
            const isImage = proofUrl && /\.(png|jpe?g|gif|webp|bmp|svg)(\?.*)?$/i.test(proofUrl);

            container.append(`
            <div class="row mt-2 proof-row align-items-end" data-proof-id="${proof.id ?? ''}">
                <input type="hidden" name="guests[${guestIndex}][id_proofs][${index}][proof_id]" value="${proof.id ?? ''}">
                <input type="hidden" name="guests[${guestIndex}][id_proofs][${index}][id_type]" value="${proof.id_type || ''}">
                <input type="hidden" name="guests[${guestIndex}][id_proofs][${index}][id_number]" value="${proof.id_number || ''}">
                <div class="col-md-4">
                    <label class="small text-muted d-block mb-1">ID Type</label>
                    <input type="text"
                        class="form-control"
                        name="guests[${guestIndex}][id_proofs][${index}][id_type_display]"
                        value="${proof.id_type || ''}"
                        readonly>
                </div>

                <div class="col-md-4">
                    <input type="file"
                        name="guests[${guestIndex}][id_proofs][${index}][id_file]"
                        class="form-control replaceFile">
                </div>

                <div class="col-md-2">
                    ${proofUrl ? `
                        <a href="${proofUrl}" target="_blank" class="btn btn-sm btn-info">
                            <i class="fa fa-eye"></i>
                        </a>
                    ` : ''}

                    <button type="button"
                        class="btn btn-sm btn-danger removeProofBtn">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
        `);
        });
    }
</script>

<script>
    // CHECK IN
    // $(document).on('click', '.checkin-btn', function() {

    //     let bookingId = $(this).data('id');

    //     $.ajax({
    //         url: "/booking/get-available-rooms/" + bookingId,
    //         type: "GET",

    //         success: function(res) {

    //             $('#checkin_booking_id').val(res.booking_id);
    //             $('#checkinBookingCode').html(res.booking_code);
    //             $('#required_rooms').val(res.rooms_required);

    //             let html = '';

    //             res.rooms.forEach(room => {

    //                 html += `
    //             <div class="col-md-3 mb-3">
    //                 <div class="border p-2 rounded text-center">
    //                     <label style="cursor:pointer;">
    //                         <input type="checkbox"
    //                             name="rooms[]"
    //                             value="${room.id}"
    //                             class="room-checkbox mr-1">

    //                         <strong>Room ${room.room_number}</strong>
    //                     </label>
    //                 </div>
    //             </div>`;
    //             });

    //             $('#availableRoomsContainer').html(html);
    //             $('#roomValidation').text("Please select atleast " + res.rooms_required + " room(s)");

    //             $('#checkinModal').modal('show');
    //         }
    //     });

    // });

    // OPEN CHECK-IN MODAL AND LOAD AVAILABLE ROOMS
    $(document)
        .off('click.checkin', '.checkin-btn')
        .on('click.checkin', '.checkin-btn', function() {

            let bookingId = $(this).data('id');

            $('#checkin_booking_id').val('');
            $('#checkinBookingCode').text('');
            $('#required_rooms').val(1);
            $('#roomValidation').text('');
            $('#confirmCheckin').prop('disabled', true);

            $('#availableRoomsContainer').html(`
            <div class="col-12 text-center py-3">
                Loading available rooms...
            </div>
        `);

            $.ajax({
                url: "/booking/get-available-rooms/" + bookingId,
                type: "GET",

                success: function(res) {

                    $('#checkin_booking_id').val(res.booking_id);
                    $('#checkinBookingCode').text(res.booking_code);

                    let requiredRooms = parseInt(res.rooms_required, 10) || 1;
                    let rooms = res.rooms || [];

                    $('#required_rooms').val(requiredRooms);

                    if (rooms.length === 0) {
                        $('#availableRoomsContainer').html(`
                        <div class="col-12">
                            <div class="alert alert-warning">
                                No available rooms found.
                            </div>
                        </div>
                    `);

                        $('#roomValidation').text('No available rooms found.');
                        $('#checkinModal').modal('show');
                        return;
                    }

                    // Check whether enough rooms are available
                    if (rooms.length < requiredRooms) {
                        $('#roomValidation').text(
                            'This booking requires exactly ' + requiredRooms +
                            ' room(s), but only ' + rooms.length +
                            ' room(s) are available.'
                        );
                    }

                    let html = '';

                    // Auto-select only when exactly one room is required
                    // and exactly one room is available.
                    let autoSelectSingleRoom =
                        requiredRooms === 1 && rooms.length === 1;

                    rooms.forEach(function(room) {

                        let selected = autoSelectSingleRoom;

                        html += `
                        <div class="col-md-3 col-sm-6 mb-3">
                            <label class="checkin-room-card w-100 mb-0"
                                   style="cursor:pointer;">

                                <input
                                    type="checkbox"
                                    name="rooms[]"
                                    value="${room.id}"
                                    class="room-checkbox d-none"
                                    ${selected ? 'checked' : ''}
                                >

                                <div
                                    class="border rounded p-3 text-center h-100 room-card-inner"
                                    style="
                                        border: ${selected
                                            ? '2px solid #28a745'
                                            : '1px solid #dee2e6'} !important;
                                        background: ${selected
                                            ? '#eaf8ee'
                                            : '#ffffff'};
                                    ">

                                    <div class="room-icon mb-2">
                                        <i class="fa-solid fa-door-open fa-2x
                                            ${selected
                                                ? 'text-success'
                                                : 'text-muted'}">
                                        </i>
                                    </div>

                                    <h6 class="mb-2 font-weight-bold">
                                        Room ${room.room_number}
                                    </h6>

                                    <span class="badge
                                        ${selected
                                            ? 'badge-success'
                                            : 'badge-secondary'}
                                        room-status">
                                        ${selected ? 'Selected' : 'Available'}
                                    </span>

                                </div>
                            </label>
                        </div>
                    `;
                    });

                    $('#availableRoomsContainer').html(html);

                    updateRoomSelectionValidation();

                    $('#checkinModal').modal('show');
                },

                error: function() {

                    $('#availableRoomsContainer').html(`
                    <div class="col-12">
                        <div class="alert alert-danger">
                            Failed to fetch available rooms.
                        </div>
                    </div>
                `);

                    $('#roomValidation').text(
                        'Failed to fetch available rooms.'
                    );

                    $('#confirmCheckin').prop('disabled', true);
                    $('#checkinModal').modal('show');
                }
            });
        });


    // UPDATE ROOM CARD APPEARANCE
    function updateRoomCard($checkbox) {

        let $card = $checkbox.closest('.checkin-room-card');
        let $inner = $card.find('.room-card-inner');
        let $icon = $card.find('.room-icon i');
        let $status = $card.find('.room-status');

        if ($checkbox.prop('checked')) {

            $inner.css({
                'border': '2px solid #28a745',
                'background': '#eaf8ee'
            });

            $icon
                .removeClass('text-muted')
                .addClass('text-success');

            $status
                .removeClass('badge-secondary')
                .addClass('badge-success')
                .text('Selected');

        } else {

            $inner.css({
                'border': '1px solid #dee2e6',
                'background': '#ffffff'
            });

            $icon
                .removeClass('text-success')
                .addClass('text-muted');

            $status
                .removeClass('badge-success')
                .addClass('badge-secondary')
                .text('Available');
        }
    }


    // SELECT / DESELECT ROOMS WITH MAXIMUM LIMIT
    $(document)
        .off('change.checkinRooms', '#availableRoomsContainer .room-checkbox')
        .on(
            'change.checkinRooms',
            '#availableRoomsContainer .room-checkbox',
            function() {

                let requiredRooms =
                    parseInt($('#required_rooms').val(), 10) || 1;

                let $checkbox = $(this);

                // Count selections only in this Check In modal
                let selectedCount = $(
                    '#availableRoomsContainer .room-checkbox:checked'
                ).length;

                // Prevent selecting more than the required number
                if ($checkbox.prop('checked') &&
                    selectedCount > requiredRooms) {


                    // Undo the extra selection
                    $checkbox.prop('checked', false);

                    updateRoomCard($checkbox);

                    let warningMessage = requiredRooms === 1 ?
                        'Maximum allowed selection is 1 room only.' :
                        'Maximum allowed selection is ' + requiredRooms + ' rooms only.';

                    // Show warning notification
                    toastr.warning(warningMessage);

                    $('#roomValidation').text(warningMessage);

                    updateRoomSelectionValidation();
                    return;
                }

                updateRoomCard($checkbox);
                updateRoomSelectionValidation();
            }
        );


    // VALIDATE EXACT ROOM COUNT
    function updateRoomSelectionValidation() {

        let requiredRooms = parseInt($('#required_rooms').val(), 10) || 1;

        let availableRooms = $(
            '#availableRoomsContainer .room-checkbox'
        ).length;

        let selectedRooms = $(
            '#availableRoomsContainer .room-checkbox:checked'
        ).length;

        // Disable confirmation by default
        $('#confirmCheckin').prop('disabled', true);

        // Not enough rooms available
        if (availableRooms < requiredRooms) {
            $('#roomValidation').text(
                'Exactly ' + requiredRooms + ' room(s) required, but only ' +
                availableRooms + ' available.'
            );
            return false;
        }

        // Requirement not yet fulfilled
        if (selectedRooms < requiredRooms) {
            $('#roomValidation').text(
                'Please select exactly ' + requiredRooms +
                ' room(s). Selected: ' + selectedRooms
            );
            return false;
        }

        // Too many rooms selected
        if (selectedRooms > requiredRooms) {
            $('#roomValidation').text(
                'Maximum allowed selection is ' + requiredRooms + ' room(s).'
            );
            return false;
        }

        // Exact requirement fulfilled
        $('#roomValidation').text(
            'Exactly ' + requiredRooms + ' room(s) selected.'
        );

        $('#confirmCheckin').prop('disabled', false);

        return true;
    }

    // CONFIRM CHECK-IN AND SUBMIT SELECTED ROOM IDS
    $(document)
        .off('click.checkinSubmit', '#confirmCheckin')
        .on('click.checkinSubmit', '#confirmCheckin', function(e) {

            e.preventDefault();

            // Validate again before sending
            if (!updateRoomSelectionValidation()) {
                return;
            }

            let requiredRooms =
                parseInt($('#required_rooms').val(), 10) || 1;

            // Collect ONLY selected rooms in this modal
            let selectedRooms = $(
                '#availableRoomsContainer .room-checkbox:checked'
            ).map(function() {
                return this.value;
            }).get();

            // Require an exact match
            if (selectedRooms.length !== requiredRooms) {

                $('#roomValidation').text(
                    'Please select exactly ' + requiredRooms + ' room(s).'
                );

                return;
            }

            let bookingId = $('#checkin_booking_id').val();
            let checkinDate = $('#checkinDate').val();
            let checkinTime = $('#checkinTime').val();

            if (!bookingId) {
                toastr.error('Booking ID is missing.');
                return;
            }

            // Prevent duplicate submissions
            let $button = $(this);
            $button.prop('disabled', true);

            $.ajax({
                url: "/booking/check-in",
                type: "POST",

                data: {
                    booking_id: bookingId,
                    rooms: selectedRooms,
                    checkin_date: checkinDate,
                    checkin_time: checkinTime,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },

                success: function(res) {

                    if (res.status === false) {
                        toastr.error(res.message || 'Check-in failed.');
                        updateRoomSelectionValidation();
                        return;
                    }

                    $('#checkinModal').modal('hide');

                    toastr.success(
                        res.message || 'Guest checked in successfully.'
                    );

                    location.reload();
                },

                error: function(xhr) {

                    let message = 'Failed to check in guest.';

                    if (xhr.responseJSON) {
                        message = xhr.responseJSON.message || message;

                        if (xhr.responseJSON.errors) {
                            let errors = Object.values(
                                xhr.responseJSON.errors
                            ).flat();

                            message = errors.join('\n');
                        }
                    }

                    toastr.error(message);
                    updateRoomSelectionValidation();
                },

                complete: function() {
                    $button.prop('disabled', false);
                }
            });
        });

    $('#confirmCheckin').prop('disabled', true);

    $(document).on('change', '.room-checkbox', function() {

        let required = parseInt($('#required_rooms').val());
        let selected = $('.room-checkbox:checked').length;

        if (selected > required) {

            $(this).prop('checked', false);

            $('#roomValidation').text("You can select only " + required + " room(s)");

        } else {

            $('#roomValidation').text('');
        }

        if (selected == required) {
            $('#confirmCheckin').prop('disabled', false);
        } else {
            $('#roomValidation').text("Please select atleast " + required + " room(s)");
            $('#confirmCheckin').prop('disabled', true);
        }

    });

    // $('#confirmCheckin').click(function() {

    //     if (!updateRoomSelectionValidation()) {
    //         return;
    //     }

    //     let selectedRooms = $('#availableRoomsContainer .room-checkbox:checked')
    //         .map(function() {
    //             return this.value;
    //         })
    //         .get();

    //     if (selectedRooms.length === 0) {
    //         return;
    //     }
    //     let bookingId = $('#checkin_booking_id').val();
    //     let rooms = [];

    //     $('.room-checkbox:checked').each(function() {
    //         rooms.push($(this).val());
    //     });

    //     let required = parseInt($('#required_rooms').val());

    //     if (rooms.length != required) {

    //         $('#roomValidation').text("Please select exactly " + required + " room(s)");
    //         return;
    //     }

    //     $.ajax({

    //         url: "/booking/check-in",
    //         type: "POST",

    //         data: {
    //             booking_id: bookingId,
    //             rooms: rooms,
    //             _token: $('meta[name="csrf-token"]').attr('content')
    //         },

    //         success: function(res) {

    //             $('#checkinModal').modal('hide');

    //             toastr.success('Guest Checked In Successfully');

    //             location.reload();
    //         }

    //     });

    // });

    $(document).ready(function() {
        let actionUrl = '';
        let modalButton = null; // store confirm button reference

        // --- Modal open function ---
        function openConfirmationModal(url, title = 'Confirm Action', message = 'Are you sure you want to proceed?') {
            actionUrl = url;
            $('#confirmationModalTitle').text(title);
            $('#confirmationModalBody').html('<p>' + message + '</p>');
            $('#confirmationModal').modal('show');
            modalButton = $('#confirmationModalConfirmBtn')[0]; // store DOM button reference
        }

        // --- Loader functions ---
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

        // --- Confirm button inside modal ---
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
                        toastr.success(response.message);
                        location.reload(); // reload table or page
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

        // --- Cancel booking ---
        $(document).on('click', '.cancel-btn', function() {
            const bookingId = $(this).data('id');
            const title = $(this).data('title') || 'Confirm Cancellation';
            const message = $(this).data('message') || 'Are you sure you want to cancel this booking?';
            const color = $(this).data('color') || 'primary';
            $('#confirmationModal .modal-header').removeClass('bg-primary bg-warning bg-danger bg-success one-bg-danger').addClass(color);
            $('#confirmationModalConfirmBtn').removeClass('btn-primary btn-warning btn-danger btn-success one-btn-danger').addClass('one-btn-danger');
            openConfirmationModal('/bookings/' + bookingId + '/cancel', title, message);
        });

        // --- No-show ---
        $(document).on('click', '.no-show-btn', function() {
            const bookingId = $(this).data('id');
            const title = $(this).data('title') || 'Mark as No Show';
            const message = $(this).data('message') || 'Are you sure you want to mark this booking as No Show?';
            const color = $(this).data('color') || 'primary';
            $('#confirmationModal .modal-header').removeClass('bg-primary bg-warning bg-danger bg-success one-bg-danger').addClass('bg-' + color);
            $('#confirmationModalConfirmBtn').removeClass('btn-primary btn-warning btn-danger btn-success one-btn-danger').addClass('btn-' + color).addClass('text-white');

            openConfirmationModal('/bookings/' + bookingId + '/noshow', title, message);

        });

        // --- Check-out ---
        $(document).on('click', '.checkout-btn', function() {
            const bookingId = $(this).data('id');
            const bookingCode = $(this).data('booking-code') || 'N/A';
            
            // Fetch booking details to pre-fill the modal
            $.ajax({
                url: `/bookings/${bookingId}`,
                type: 'GET',
                headers: {
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.status && response.data) {
                        const booking = response.data.booking || {};
                        
                        // Set booking ID and code
                        $('#checkout_booking_id').val(bookingId);
                        $('#checkoutBookingCode').text(bookingCode);
                        
                        // Display check-in date and time
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
                        
                        // Set default checkout date to today
                        const today = new Date();
                        const todayFormatted = today.toISOString().split('T')[0];
                        $('#checkoutDate').val(todayFormatted);
                        
                        // Set default checkout time to current time
                        const hours = String(today.getHours()).padStart(2, '0');
                        const minutes = String(today.getMinutes()).padStart(2, '0');
                        $('#checkoutTime').val(`${hours}:${minutes}`);
                        
                        // Show the modal
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
    });
</script>

<!-- Edit modal work  -->
<script>
    let editBookingId = null;

    document.addEventListener('click', async function(e) {

        if (!e.target.closest('.editBookingBtn')) return;

        const id = e.target.closest('.editBookingBtn').dataset.id;

        try {

            const res = await fetch(`/bookings/${id}/edit`);
            const json = await res.json();

            if (!json.status) {
                toastr.error("Failed to load booking");
                return;
            }

            editBookingId = id;

            fillEditModal(json.data);

            $('#editModal').modal('show');

        } catch (err) {

            console.error(err);
            toastr.error("Something went wrong");

        }

    });


    function fillEditModal(data) {
        const modal = $('#editModal');

        const booking = data.booking;
        const room = data.room_type;
        const guests = data.guests ?? [];
        const services = data.services ?? [];
        const payments = data.payments ?? [];

        /* -----------------------------
        BASIC DETAILS
        ------------------------------*/

        modal.find('#checkInDate').val(booking.check_in_date);
        modal.find('#checkOutDate').val(booking.check_out_date);
        modal.find('#noOfGuests').val(booking.no_of_guests);
        modal.find('#noOfRooms').val(booking.no_of_rooms);

        // Keep these booking details locked in edit mode, but still send their values on update.
        modal.find('#checkInDate').prop('disabled', true);
        modal.find('#checkOutDate').prop('disabled', true);
        modal.find('#noOfGuests').prop('disabled', true);
        modal.find('#noOfRooms').prop('disabled', true);

        modal.find('#searchAvailability').hide();


        /* -----------------------------
        SELECTED ROOM
        ------------------------------*/

        const selectedRoomHtml = `
        <div class="card bg-warning shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    Selected Room : ${room?.name ?? 'N/A'}
                    (₹${booking.base_amount})
                </h5>
            </div>
        </div>
    `;

        modal.find('#selectedRoomContainer').html(selectedRoomHtml);

        modal.find('#bookingFormSection').show();


        /* -----------------------------
        TOTAL
        ------------------------------*/

        const baseTotal = parseFloat(booking.base_amount ?? booking.total_amount ?? 0);
        modal.find('#finalAmount').text(baseTotal.toFixed(2));
        modal.find('#totalAmountInput').val(baseTotal.toFixed(2));


        /* -----------------------------
        AGENT
        ------------------------------*/

        if (booking.agent_id) {

            modal.find('select[name="agent_id"]')
                .val(booking.agent_id)
                .trigger('change');

        }

        modal.find('#remark').val(booking.remark);


        /* -----------------------------
        GUESTS
        ------------------------------*/

        let guestContainer = modal.find('#editGuestContainer');

        guestContainer.html('');

        guests.forEach(function(guest, index) {

            guestContainer.append(createGuestForm(index));

            const card = guestContainer.find('.guest-card').last();

            card.find(`input[name="guests[${index}][mobile]"]`).val(guest.mobile);
            card.find(`input[name="guests[${index}][name]"]`).val(guest.full_name);
            card.find(`input[name="guests[${index}][email]"]`).val(guest.email);
            card.find(`textarea[name="guests[${index}][address]"]`).val(guest.address);
            card.find(`input[name="guests[${index}][dob]"]`).val(guest.dob);

            const countrySelect = card.find(`select[name="guests[${index}][country_id]"]`);
            const stateSelect = card.find(`select[name="guests[${index}][state_id]"]`);

            countrySelect.val(guest.country_id);

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


        /* -----------------------------
        SERVICES
        ------------------------------*/

        modal.find('.serviceCheck').prop('checked', false);
        modal.find('.serviceQty').prop('disabled', true).val(1);

        services.forEach(service => {

            const checkbox = modal.find(`.serviceCheck[value="${service.id}"]`);

            checkbox.prop('checked', true);

            const qtyInput = checkbox.closest('.form-check').find('.serviceQty');

            qtyInput.prop('disabled', false);

            qtyInput.val(service.pivot.quantity);

        });

        calculateTotal();


        /* -----------------------------
        PAYMENTS
        ------------------------------*/

        let paymentTable = `
        <table class="table m-0 table-bordered table-sm" id="example">
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

        payments.forEach(payment => {

            paymentTable += `
            <tr>
                <td>${payment.paid_at ?? '-'}</td>
                <td>${payment.method}</td>
                <td>${payment.transaction_reference ?? '-'}</td>
                <td>₹${payment.amount}</td>
            </tr>
        `;

        });

        paymentTable += `</tbody></table>`;

        modal.find('#paymentHistory').remove();

        modal.find('#paymentMethod')
            .closest('.row')
            .after(`<div id="paymentHistory">${paymentTable}</div>`);

    }



    /* -----------------------------
    UPDATE BOOKING
    ------------------------------*/

    $('#updateBtn').click(function(e) {

        e.preventDefault();

        const $editForm = $('#EditForm');
        const $editCheckIn = $editForm.find('#checkInDate');
        const $editCheckOut = $editForm.find('#checkOutDate');
        const $editGuests = $editForm.find('#noOfGuests');
        const $editRooms = $editForm.find('#noOfRooms');

        if (!validatePaymentFields($editForm[0])) {
            return;
        }

        let formData = new FormData($editForm[0]);

        const checkInValue = $editCheckIn.val();
        const checkOutValue = $editCheckOut.val();
        const noOfGuestsValue = $editGuests.val();
        const noOfRoomsValue = $editRooms.val();

        if (checkInValue) {
            formData.set('check_in_date', checkInValue);
            formData.delete('check_in');
        }

        if (checkOutValue) {
            formData.set('check_out_date', checkOutValue);
            formData.delete('check_out');
        }

        if (noOfGuestsValue) {
            formData.set('no_of_guests', noOfGuestsValue);
        }

        if (noOfRoomsValue) {
            formData.set('no_of_rooms', noOfRoomsValue);
        }

        formData.append('_method', 'PUT');

        $.ajax({

            url: `/bookings/${editBookingId}`,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,

            success: function(res) {

                if (res.status) {

                    toastr.success("Booking Updated Successfully");

                    location.reload();

                } else {

                    toastr.error(res.message || "Update failed");

                }

            },

            error: function(xhr) {

                if (xhr.status === 422) {

                    Object.values(xhr.responseJSON.errors).forEach(err => {

                        toastr.error(err[0]);

                    });

                } else {

                    toastr.error("Server error");

                }

            }

        });

    });
</script>

<!-- show modal work  -->
<script>
    document.addEventListener('click', async function(e) {
        const btn = e.target.closest('.showBookingBtn');
        if (!btn) return;
        const id = btn.dataset.id;
        const modal = $('#showModal');

        const formatDateJS = (dateStr) => {
            if (!dateStr) return 'N/A';
            const date = new Date(dateStr);
            const d = String(date.getDate()).padStart(2, '0');
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const y = date.getFullYear();
            return `${d}-${m}-${y}`;
        };

        const formatDateTimeJS = (dateStr) => {
            if (!dateStr) return 'N/A';
            const date = new Date(dateStr);
            const d = String(date.getDate()).padStart(2, '0');
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const y = date.getFullYear();
            const hh = String(date.getHours()).padStart(2, '0');
            const mm = String(date.getMinutes()).padStart(2, '0');
            return `${d}-${m}-${y}, ${hh}:${mm}`;
        };

        const formatCurrency = (value) => {
            const n = Number(value || 0);
            return isNaN(n) ? '0.00' : n.toFixed(2);
        };

        const safeText = (value, fallback = '—') => {
            if (value === null || value === undefined || value === '') return fallback;
            return value;
        };

        try {
            const res = await fetch(`/bookings/${id}`, {
                headers: {
                    'Accept': 'application/json'
                }
            });
            const json = await res.json();
            if (!json.status) throw new Error(json.message);
            const data = json.data;

            const booking = data.booking || {};
            const roomType = data.room_type || {};
            const guests = data.guests || [];
            const payments = data.booking.payments || [];
            const services = data.booking.additional_services || [];
            const leadGuest = guests[0] || {};

            const totalDealAmount = Number(booking.base_amount || booking.total_amount || 0);
            const serviceTotal = services.reduce((sum, service) => {
                const price = Number(service?.pivot?.amount || service?.cost || 0);
                const qty = Number(service?.pivot?.quantity || 1);
                return sum + (price * qty);
            }, 0);
            const discountAmount = Number(booking.discount_amount || 0);
            const paidSum = payments.reduce((sum, payment) => sum + Number(payment?.amount || 0), 0);
            const outstandingAmount = Math.max(totalDealAmount + serviceTotal - discountAmount - paidSum, 0);

            const nights = (() => {
                if (!booking.check_in_date || !booking.check_out_date) return 0;
                const start = new Date(booking.check_in_date);
                const end = new Date(booking.check_out_date);
                const diff = (end - start) / (1000 * 60 * 60 * 24);
                return Number.isFinite(diff) ? Math.max(diff, 0) : 0;
            })();

            const statusText = safeText(booking.status || 'pending', 'pending')
                .toString()
                .replace(/_/g, ' ')
                .toUpperCase();

            modal.find('#modalBrn').text(booking.booking_code || booking.id || '—');
            modal.find('#modalGuestName').text(safeText(leadGuest.full_name, '—'));
            modal.find('#modalEmail').text(safeText(leadGuest.email, '—'));
            modal.find('#modalMobile').text(safeText(leadGuest.mobile, '—'));
            modal.find('#modalAddress').text(safeText(leadGuest.address, '—'));
            modal.find('#modalStatus').text(statusText);

            const checkInValue = booking.actual_check_in_at ? formatDateTimeJS(booking.actual_check_in_at) : formatDateJS(booking.check_in_date);
            const checkOutValue = booking.actual_check_out_at ? formatDateTimeJS(booking.actual_check_out_at) : formatDateJS(booking.check_out_date);
            const totalHoursValue = (() => {
                if (!booking.actual_check_in_at || !booking.actual_check_out_at) return null;
                const start = new Date(booking.actual_check_in_at);
                const end = new Date(booking.actual_check_out_at);
                const diffHours = (end - start) / (1000 * 60 * 60);
                return Number.isFinite(diffHours) ? Math.max(diffHours, 0) : null;
            })();

            modal.find('#modalCheckIn').text(checkInValue);
            modal.find('#modalCheckOut').text(checkOutValue);
            if (totalHoursValue !== null) {
                modal.find('#modalTotalHours').text(`${Number(totalHoursValue).toFixed(1)} hrs`);
                modal.find('#modalTotalHoursRow').show();
            } else {
                modal.find('#modalTotalHoursRow').hide();
            }
            modal.find('#modalRoomType').text(safeText(roomType.name, '—'));
            modal.find('#modalRoomNumber').text(safeText(booking.room_number || booking.room_no || '—', '—'));
            modal.find('#modalRooms').text(safeText(booking.no_of_rooms, '—'));
            modal.find('#modalNights').text(`${nights} ${nights === 1 ? 'Night' : 'Nights'}`);

            modal.find('#modalTotal').text(formatCurrency(totalDealAmount));
            modal.find('#modalServices').text(formatCurrency(serviceTotal));
            modal.find('#modalDiscount').text(formatCurrency(discountAmount));
            modal.find('#modalPaid').text(formatCurrency(paidSum));
            modal.find('#modalOutstanding').text(formatCurrency(outstandingAmount));

            let paymentTrailHtml = '';
            if (payments.length > 0) {
                payments.forEach((payment, index) => {
                    const dueBalance = Math.max(outstandingAmount - Number(payment.amount || 0), 0);
                    paymentTrailHtml += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${safeText(payment.method || payment.payment_method || 'Cash', '—')}</td>
                            <td>${formatDateJS(payment.paid_at || payment.date)}</td>
                            <td>${safeText(payment.transaction_reference || payment.reference || payment.txn_id || '—', '—')}</td>
                            <td>₹ ${formatCurrency(payment.amount || 0)}</td>
                            <!-- <td>₹ ${formatCurrency(payment.discount || 0)}</td>
                            <td>₹ ${formatCurrency(dueBalance)}</td> -->
                            <td>${safeText(payment.note || payment.remark || '-', '-')}</td>
                        </tr>`;
                });
            } else {
                paymentTrailHtml = `
                    <tr>
                        <td colspan="6" class="booking-empty">
                            <i class="fal fa-file-invoice-dollar"></i>
                            <span>No payment records available</span>
                        </td>
                    </tr>`;
            }
            modal.find('#modalPaymentTrail').html(paymentTrailHtml);

            if (services.length > 0) {
                const serviceMarkup = services.map((service) => {
                    const qty = Number(service?.pivot?.quantity || 1);
                    const amount = Number(service?.pivot?.amount || service?.cost || 0) * qty;
                    return `
                        <div class="booking-service-item">
                            <div class="booking-info-icon" style="width: 28px; height: 28px; font-size: 12px;">
                                <i class="fal fa-concierge-bell"></i>
                            </div>
                            <div>
                                <span class="booking-service-name">${safeText(service.service_name || service.name, 'Service')}</span>
                                <div class="booking-service-price">₹ ${formatCurrency(amount)} <small class="text-muted">x${qty}</small></div>
                            </div>
                        </div>`;
                }).join('');
                modal.find('#modalServicesList').html(serviceMarkup);
            } else {
                modal.find('#modalServicesList').html(`
                    <div class="booking-service-empty">
                        <i class="fal fa-info-circle mr-1"></i>
                        No additional services available
                    </div>`);
            }

            modal.modal('show');
        } catch (err) {
            console.error(err);
            toastr.error("Error loading booking data.");
        }
    });


    $(document).ready(function() {

        const replacementRoomsUrl =
            @json(route('booking.replacementRooms', ['bookingId' => '__ID__']));

        const changeRoomUrl =
            @json(route('booking.changeRoom', ['bookingId' => '__ID__']));

        const changeRoomTypeUrl =
            @json(route('booking.changeRoomType', ['bookingId' => '__ID__']));

        function bookingUrl(url, id) {
            return url.replace('__ID__', id);
        }

        function showError(message) {
            toastr.error(message || 'Something went wrong.');
        }

        // OPEN CHANGE ROOM MODAL
        $(document).on('click', '.changeRoomBtn', function() {
            let bookingId = $(this).data('id');

            $('#changeRoomsContainer').html('<div class="col-12">Loading rooms...</div>');
            $('#currentChangeRoomsContainer').html('<div class="col-12">Loading current rooms...</div>');
            $('#changeRoomValidation').text('');
            $('#selected_change_room_id').val('');
            $('#change_room_old_id').val('');
            $('#change_room_reason').val('');

            $.ajax({
                url: bookingUrl(replacementRoomsUrl, bookingId),
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.status !== true) {
                        $('#changeRoomsContainer').html('<div class="col-12 text-danger">Unable to load rooms.</div>');
                        return;
                    }

                    $('#change_room_booking_id').val(res.booking_id);
                    $('#changeRoomBookingCode').text(res.booking_code);

                    let currentRooms = res.assigned_rooms || [];
                    let currentRoom = res.old_room;

                    if (!currentRooms.length && currentRoom) {
                        currentRooms = [{
                            id: currentRoom.id,
                            room_number: currentRoom.room_number,
                            room_type_id: currentRoom.room_type_id,
                            room_type_name: currentRoom.room_type_name || null
                        }];
                    }

                    let currentHtml = '';
                    currentRooms.forEach(room => {
                        const isSelected = currentRoom && String(room.id) === String(currentRoom.id);
                        currentHtml += `
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="border p-3 rounded text-center current-room-card ${isSelected ? 'border-success bg-light' : ''}"
                                     data-room-id="${room.id}"
                                     data-room-type-id="${room.room_type_id || ''}"
                                     style="cursor:pointer;">
                                    <div class="mb-2">
                                        <i class="fa-solid fa-door-open fa-2x ${isSelected ? 'text-success' : 'text-muted'}"></i>
                                    </div>
                                    <strong>Room ${room.room_number}</strong>
                                    <div class="mt-2">
                                        <span class="badge ${isSelected ? 'badge-success' : 'badge-secondary'}">
                                            ${isSelected ? 'Selected' : 'Current Room'}
                                        </span>
                                    </div>
                                </div>
                            </div>`;
                    });
                    $('#currentChangeRoomsContainer').html(currentHtml || '<div class="col-12 text-muted">No current rooms available.</div>');

                    const selectedOldId = currentRoom ? currentRoom.id : (currentRooms[0] ? currentRooms[0].id : '');
                    if (selectedOldId) {
                        $('#change_room_old_id').val(selectedOldId);
                    }

                    let html = '';
                    let rooms = res.available_rooms || [];

                    if (currentRoom && !rooms.some(room => String(room.id) === String(currentRoom.id))) {
                        rooms.unshift(currentRoom);
                    }

                    rooms.forEach(room => {
                        let isCurrent = currentRoom && String(room.id) === String(currentRoom.id);
                        html += `
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="border p-3 rounded text-center room-change-card ${isCurrent ? 'border-success bg-light' : ''}"
                                     data-room-id="${room.id}"
                                     style="cursor:pointer;">
                                    <div class="mb-2">
                                        <i class="fa-solid fa-door-open fa-2x ${isCurrent ? 'text-success' : 'text-muted'}"></i>
                                    </div>
                                    <strong>Room ${room.room_number}</strong>
                                    <div class="mt-2">
                                        <span class="badge ${isCurrent ? 'badge-success' : 'badge-secondary'}">
                                            ${isCurrent ? 'Currently Assigned' : 'Available'}
                                        </span>
                                    </div>
                                </div>
                            </div>`;
                    });

                    $('#changeRoomsContainer').html(html || '<div class="col-12 text-muted">No replacement rooms available.</div>');

                    if (currentRoom) {
                        $('#selected_change_room_id').val(currentRoom.id);
                        $('#changeRoomValidation').text('Currently assigned: Room ' + currentRoom.room_number + '. Select another room if you want to change it.');
                    }

                    $('#changeRoomModal').modal('show');
                },
                error: function() {
                    $('#changeRoomsContainer').html('<div class="col-12 text-danger">Failed to fetch rooms.</div>');
                }
            });
        });

        $(document).on('click', '.room-change-card', function() {
            $('.room-change-card').removeClass('border-primary bg-light').css('border-width', '1px');
            $(this).addClass('border-primary').css('border-width', '2px');
            $('#selected_change_room_id').val($(this).data('room-id'));
            $('#changeRoomValidation').text('Room selected successfully.');
        });

        $(document).on('click', '.current-room-card', function() {
            const roomId = $(this).data('room-id');
            const roomTypeId = $(this).data('room-type-id');
            const bookingId = $('#change_room_booking_id').val();

            $('#change_room_old_id').val(roomId);
            $('#currentChangeRoomsContainer .current-room-card')
                .removeClass('border-success bg-light')
                .find('.badge')
                .removeClass('badge-success')
                .addClass('badge-secondary')
                .text('Current Room');

            $(this).addClass('border-success bg-light')
                .find('.badge')
                .removeClass('badge-secondary')
                .addClass('badge-success')
                .text('Selected');

            if (!bookingId || !roomId) return;

            $.ajax({
                url: bookingUrl(replacementRoomsUrl, bookingId),
                type: 'GET',
                dataType: 'json',
                data: {
                    old_room_id: roomId,
                    room_type_id: roomTypeId || null
                },
                success: function(res) {
                    if (res.status !== true) {
                        $('#changeRoomsContainer').html('<div class="col-12 text-danger">Unable to load rooms.</div>');
                        return;
                    }

                    let html = '';
                    let rooms = res.available_rooms || [];
                    const currentRoom = res.old_room;

                    if (currentRoom && !rooms.some(room => String(room.id) === String(currentRoom.id))) {
                        rooms.unshift(currentRoom);
                    }

                    rooms.forEach(room => {
                        const isCurrent = currentRoom && String(room.id) === String(currentRoom.id);
                        html += `
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="border p-3 rounded text-center room-change-card ${isCurrent ? 'border-success bg-light' : ''}"
                                     data-room-id="${room.id}"
                                     style="cursor:pointer;">
                                    <div class="mb-2">
                                        <i class="fa-solid fa-door-open fa-2x ${isCurrent ? 'text-success' : 'text-muted'}"></i>
                                    </div>
                                    <strong>Room ${room.room_number}</strong>
                                    <div class="mt-2">
                                        <span class="badge ${isCurrent ? 'badge-success' : 'badge-secondary'}">
                                            ${isCurrent ? 'Currently Assigned' : 'Available'}
                                        </span>
                                    </div>
                                </div>
                            </div>`;
                    });

                    $('#changeRoomsContainer').html(html || '<div class="col-12 text-muted">No replacement rooms available for this room type.</div>');
                    $('#changeRoomValidation').text('Selected room: Room ' + (currentRoom ? currentRoom.room_number : roomId) + '. Choose a replacement room.');
                }
            });
        });

        $('#confirmChangeRoom').on('click', function() {
            let bookingId = $('#change_room_booking_id').val();
            let roomId = $('#selected_change_room_id').val();
            let oldRoomId = $('#change_room_old_id').val();

            if (!roomId) {
                $('#changeRoomValidation').text('Please select a room.');
                return;
            }

            $.ajax({
                url: bookingUrl(changeRoomUrl, bookingId),
                type: 'POST',
                data: {
                    _token: $('#changeRoomForm input[name="_token"]').val(),
                    new_room_id: roomId,
                    old_room_id: oldRoomId || null,
                    reason: $('#change_room_reason').val()
                },
                success: function(res) {
                    if (res.success === false || res.status === false) {
                        $('#changeRoomValidation').text(res.message || 'Unable to change room.');
                        return;
                    }

                    $('#changeRoomModal').modal('hide');
                    toastr.success(res.message || 'Room changed successfully.');
                    window.location.reload();
                },
                error: function(xhr) {
                    $('#changeRoomValidation').text(xhr.responseJSON?.message || 'Failed to change room.');
                }
            });
        });

        $('#changeRoomForm').on('submit', function(e) {
            e.preventDefault();

            const bookingId = $('#change_room_booking_id').val();
            const newRoomId = $('#selected_change_room_id').val();
            const oldRoomId = $('#change_room_old_id').val();

            if (!newRoomId) {
                showError('Please select a replacement room.');
                return;
            }

            $.ajax({
                url: bookingUrl(changeRoomUrl, bookingId),
                type: 'POST',
                dataType: 'json',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content') || $('#changeRoomForm input[name="_token"]').val(),
                    new_room_id: newRoomId,
                    old_room_id: oldRoomId || null,
                    reason: $('#change_room_reason').val()
                },
                success: function(response) {
                    if (response.success === false || response.status === false) {
                        showError(response.message || 'Room change failed.');
                        return;
                    }

                    toastr.success(response.message || 'Room changed successfully.');
                    $('#changeRoomModal').modal('hide');
                    window.location.reload();
                },
                error: function(xhr) {
                    showError(xhr.responseJSON?.message || 'Failed to change room.');
                }
            });
        });

        // OPEN UPGRADE ROOM TYPE MODAL
        $(document).on('click', '.upgradeRoomBtn', function() {
            const bookingId = $(this).data('id');
            const bookingCode = $(this).data('code');
            const checkinDate = $(this).data('checkin');
            const currentRoomTypeId = String($(this).attr('data-room-type-id') || '');
            const oldRoomId = $(this).attr('data-room-number-id') || '';

            $('#upgrade_booking_id').val(bookingId);
            $('#upgrade_booking_code').text(bookingCode);
            $('#effective_date').val(checkinDate);
            $('#upgrade_reason').val('');
            $('#new_room_type_id').val('');
            $('#upgrade_new_room_id').val('');
            $('#upgrade_old_room_id').val(oldRoomId);
            $('#upgradeCurrentRoomsContainer').html('<div class="col-12 text-muted">Loading current rooms...</div>');
            $('#upgradeRoomTypeValidation').text('');
            $('#upgradeRoomValidation').text('');

            $.ajax({
                url: bookingUrl(replacementRoomsUrl, bookingId),
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.status !== true) {
                        $('#upgradeCurrentRoomsContainer').html('<div class="col-12 text-danger">Unable to load current rooms.</div>');
                        return;
                    }

                    const assignedRooms = res.assigned_rooms || (res.old_room ? [{
                        id: res.old_room.id,
                        room_number: res.old_room.room_number,
                        room_type_id: res.old_room.room_type_id,
                        room_type_name: res.old_room.room_type_name || null
                    }] : []);

                    let currentHtml = '';
                    assignedRooms.forEach(function(room) {
                        const isSelected = String(room.id) === String($('#upgrade_old_room_id').val() || '');
                        currentHtml += `
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="upgrade-current-room-card border p-3 rounded text-center ${isSelected ? 'border-warning bg-light' : ''}"
                                     data-room-id="${room.id}"
                                     data-room-type-id="${room.room_type_id || ''}"
                                     style="cursor:pointer;">
                                    <div class="mb-2">
                                        <i class="fa-solid fa-door-open fa-2x ${isSelected ? 'text-warning' : 'text-muted'}"></i>
                                    </div>
                                    <strong>Room ${room.room_number}</strong>
                                    <div class="mt-2">
                                        <span class="badge ${isSelected ? 'badge-warning' : 'badge-secondary'}">
                                            ${isSelected ? 'Selected' : 'Current Room'}
                                        </span>
                                    </div>
                                </div>
                            </div>`;
                    });

                    $('#upgradeCurrentRoomsContainer').html(currentHtml || '<div class="col-12 text-muted">No assigned room found.</div>');

                    if (!$('#upgrade_old_room_id').val() && assignedRooms[0]) {
                        $('#upgrade_old_room_id').val(assignedRooms[0].id);
                    }

                    if (!assignedRooms.length) {
                        $('#upgradeRoomTypeValidation').text('No room is assigned for this booking yet.');
                    }
                }
            });

            let roomTypesHtml = '';
            @foreach($roomTypes as $roomType)
            roomTypesHtml += `
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="upgrade-type-card"
                         data-room-type-id="{{ $roomType->id }}">
                        <div class="upgrade-icon">
                            <i class="fa-solid fa-bed"></i>
                        </div>
                        <div class="upgrade-card-name">
                            {{ $roomType->name }}
                        </div>
                        <span class="badge badge-secondary upgrade-card-status">Select</span>
                    </div>
                </div>`;
            @endforeach

            $('#upgradeRoomTypesContainer').html(roomTypesHtml);
            $('#upgradeRoomsContainer').html('<div class="col-12 text-muted">Loading available rooms...</div>');
            $('#upgradeRoomModal').modal('show');

            const $currentCard = $('#upgradeRoomTypesContainer .upgrade-type-card').filter(function() {
                return String($(this).attr('data-room-type-id')) === currentRoomTypeId;
            });

            if (currentRoomTypeId && $currentCard.length) {
                $currentCard.trigger('click');
            } else {
                $('#upgradeRoomsContainer').html('<div class="col-12 text-muted">Select a room type to view available rooms.</div>');
                $('#upgradeRoomTypeValidation').text('Could not match the current room type. Check the room type IDs.');
            }
        });

        $('#new_room_type_id').on('change', function() {
            const bookingId = $('#upgrade_booking_id').val();
            const roomTypeId = $(this).val();
            const oldRoomId = $('#upgrade_old_room_id').val();
            $('#upgrade_new_room_id').val('');
            $('#upgradeRoomValidation').text('');

            if (!roomTypeId || !bookingId) {
                $('#upgradeRoomsContainer').html('<div class="col-12 text-muted">Select a room type first to load available rooms.</div>');
                return;
            }

            loadReplacementRooms(bookingId, roomTypeId, oldRoomId);
        });

        $(document).on('click', '.upgrade-current-room-card', function() {
            const roomId = $(this).data('room-id');
            const roomTypeId = $(this).data('room-type-id');
            $('#upgrade_old_room_id').val(roomId);
            $('#upgradeCurrentRoomsContainer .upgrade-current-room-card')
                .removeClass('border-warning bg-light')
                .find('.badge')
                .removeClass('badge-warning')
                .addClass('badge-secondary')
                .text('Current Room');

            $(this).addClass('border-warning bg-light')
                .find('.badge')
                .removeClass('badge-secondary')
                .addClass('badge-warning')
                .text('Selected');

            if (roomTypeId) {
                $('#new_room_type_id').val(roomTypeId);
                $('#upgradeRoomTypeValidation').text('');
                $('#upgradeRoomValidation').text('');
                loadReplacementRooms($('#upgrade_booking_id').val(), roomTypeId, roomId);
            }
        });

        $(document).on('click', '.upgrade-type-card', function() {
            const roomTypeId = $(this).data('room-type-id');
            if (!roomTypeId) return;

            $('#upgradeRoomTypesContainer .upgrade-type-card')
                .removeClass('selected')
                .find('.upgrade-card-status')
                .removeClass('badge-warning')
                .addClass('badge-secondary')
                .text('Select');

            $(this).addClass('selected');
            $(this).find('.upgrade-card-status')
                .removeClass('badge-secondary')
                .addClass('badge-warning')
                .text('Selected');

            $('#new_room_type_id').val(roomTypeId);
            $('#upgradeRoomTypeValidation').text('');
            $('#upgradeRoomValidation').text('');
            loadReplacementRooms($('#upgrade_booking_id').val(), roomTypeId, $('#upgrade_old_room_id').val() || null);
        });

        $('#upgradeRoomForm').on('submit', function(e) {
            e.preventDefault();

            const bookingId = $('#upgrade_booking_id').val();
            const roomTypeId = $('#new_room_type_id').val();
            const newRoomId = $('#upgrade_new_room_id').val();
            const oldRoomId = $('#upgrade_old_room_id').val();
            const effectiveDate = $('#effective_date').val();

            if (!roomTypeId) {
                $('#upgradeRoomTypeValidation').text('Please select a room type.');
                return;
            }

            if (!effectiveDate) {
                $('#upgradeRoomTypeValidation').text('Please select an effective date.');
                return;
            }

            if (!oldRoomId) {
                $('#upgradeRoomTypeValidation').text('Please select the room to upgrade.');
                return;
            }

            $.ajax({
                url: bookingUrl(changeRoomTypeUrl, bookingId),
                type: 'POST',
                dataType: 'json',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content') || $('#upgradeRoomForm input[name="_token"]').val(),
                    new_room_type_id: roomTypeId,
                    new_room_id: newRoomId || null,
                    old_room_id: oldRoomId,
                    effective_date: effectiveDate,
                    reason: $('#upgrade_reason').val()
                },
                success: function(response) {
                    if (response.success === false || response.status === false) {
                        showError(response.message || 'Room type change failed.');
                        return;
                    }

                    toastr.success(response.message || 'Room type updated successfully.');
                    $('#upgradeRoomModal').modal('hide');
                    window.location.reload();
                },
                error: function(xhr) {
                    showError(xhr.responseJSON?.message || 'Failed to update room type.');
                }
            });
        });

        function loadReplacementRooms(bookingId, roomTypeId, oldRoomId = null) {
            $('#upgrade_new_room_id').val('');
            $('#upgradeRoomsContainer').html('<div class="col-12 text-muted">Loading available rooms...</div>');

            $.ajax({
                url: bookingUrl(replacementRoomsUrl, bookingId),
                type: 'GET',
                dataType: 'json',
                data: {
                    room_type_id: roomTypeId,
                    old_room_id: oldRoomId || null
                },
                success: function(response) {
                    if (response.status !== true) {
                        $('#upgradeRoomsContainer').html('<div class="col-12 text-danger">' + (response.message || 'Failed to fetch rooms.') + '</div>');
                        return;
                    }

                    const rooms = response.available_rooms || [];

                    if (!rooms.length) {
                        $('#upgradeRoomsContainer').html('<div class="col-12 text-muted">No available rooms for this room type.</div>');
                        return;
                    }

                    let html = '';
                    rooms.forEach(function(room) {
                        html += `
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="upgrade-room-card"
                                     data-room-id="${room.id}"
                                     data-room-number="${$('<div>').text(room.room_number ?? room.id).html()}">
                                    <div class="upgrade-icon">
                                        <i class="fa-solid fa-door-open"></i>
                                    </div>
                                    <div class="upgrade-card-name">
                                        Room ${$('<div>').text(room.room_number ?? room.id).html()}
                                    </div>
                                    <span class="badge badge-secondary upgrade-card-status">Available</span>
                                </div>
                            </div>`;
                    });

                    $('#upgradeRoomsContainer').html(html);
                },
                error: function(xhr) {
                    $('#upgradeRoomsContainer').html('<div class="col-12 text-danger">Unable to load available rooms.</div>');
                    showError(xhr.responseJSON?.message || 'Failed to fetch available rooms.');
                }
            });
        }

        $(document).on('click', '#upgradeRoomModal .upgrade-room-card', function() {
            const roomId = $(this).attr('data-room-id');
            const roomNumber = $(this).attr('data-room-number');

            if (!roomId) {
                return;
            }

            $('#upgradeRoomModal .upgrade-room-card').removeClass('selected');
            $('#upgradeRoomModal .upgrade-card-status').removeClass('badge-warning').addClass('badge-secondary').text('Available');

            $(this).addClass('selected');
            $(this).find('.upgrade-card-status').removeClass('badge-secondary').addClass('badge-warning').text('Selected');
            $('#upgrade_new_room_id').val(roomId);
            $('#upgradeRoomValidation').text('Room ' + roomNumber + ' selected.');
        });

    });
</script>
@endsection