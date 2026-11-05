@extends('layouts.master')

@section('title', 'Price Chart')

@php
    $hotelRateTypes = $hotelRateTypes ?? (auth()->user()->hotel?->getBookingRateOptions() ?? ['room']);
    $defaultHotelRateType = $defaultHotelRateType ?? (auth()->user()->hotel?->getDefaultBookingRateType() ?? 'room');
@endphp
<input type="hidden" id="defaultHotelRateType" value="{{ $defaultHotelRateType }}">

@section('content')

<style>
  
</style>
<div class="bookmap"> 
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Room Booking Chart</h4>

            <div class="d-flex align-items-center" style="gap:10px;">
                <button class="btn btn-danger btn-sm bookmap-create-btn" data-toggle="modal" data-target="#createModal">
                    <i class="fa-solid fa-plus" style="font-size: 12px;"></i>
                    <span>Create Booking</span>
                </button>

                <select id="yearSelect" class="form-control w-auto"></select>
                <select id="monthSelect" class="form-control">
                    <option value="1">Jan</option>
                    <option value="2">Feb</option>
                    <option value="3">Mar</option>
                    <option value="4">Apr</option>
                    <option value="5">May</option>
                    <option value="6">Jun</option>
                    <option value="7">Jul</option>
                    <option value="8">Aug</option>
                    <option value="9">Sep</option>
                    <option value="10">Oct</option>
                    <option value="11">Nov</option>
                    <option value="12">Dec</option>
                </select>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered calendar-table">
                    <thead>
                        <tr id="dateRow" class="room-header">
                            <th>Date</th>
                        </tr>
                        <tr id="dayRow" class="room-header">
                            <th>Day</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($roomTypes as $roomType)

                        <tr>
                            <td colspan="32" class="bg-dark text-white text-center">
                                {{ strtoupper($roomType->name) }}
                            </td>
                        </tr>

                        @foreach ($roomType->roomNumbers as $room)
                        <tr class="room-data"
                            data-room="{{ $room->room_number }}">
                            <td>{{ $room->room_number }}</td>
                        </tr>
                        @endforeach

                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- color assign -->
            <div class="container">
                <div class="d-flex align-items-center justify-content-center flex-wrap" style="gap: 9px;">

                    <div class="d-flex align-items-center" style="gap: 3px;">
                        <span class="legend-box one-bg-blue" style="border:1px solid black"></span>
                        Future Booked
                    </div>

                    <div class="d-flex align-items-center" style="gap: 3px;">
                        <span class="legend-box one-bg-danger" style="border:1px solid black"></span>
                        Today Booked
                    </div>

                    <div class="d-flex align-items-center" style="gap: 3px;">
                        <span class="legend-box bg-success" style="border:1px solid black"></span>
                        Available
                    </div>

                    <div class="d-flex align-items-center" style="gap: 3px;">
                        <span class="legend-box bg-secondary" style="border:1px solid black"></span>
                        Past Date
                    </div>
                </div>
            </div>

        </div>
    </div>

    <x-create-booking-modal
        :hotel-rate-types="$hotelRateTypes"
        :default-hotel-rate-type="$defaultHotelRateType"
        :agents="$agents ?? collect()"
        :additional-services="$additional_services ?? collect()"
    />
 

    <x-edit-booking-modal
        :agents="$agents ?? collect()"
        :additional-services="$additional_services ?? collect()"
    />

    <!-- Booking Details Modal -->
    <div class="modal fade" id="bookingDetailsModal" tabindex="-1"
        role="dialog" aria-labelledby="bookingDetailsModalLabel" aria-hidden="true">

        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"
            role="document">

            <div class="modal-content booking-modal">

                <!-- BLUE HEADER -->
                <div class="modal-header booking-header bg-primary">
                    <div class="booking-header-left">
                        <div class="booking-header-icon">
                            <i class="fal fa-calendar-alt"></i>
                        </div>

                        <div>
                            <h4 class="modal-title" id="bookingDetailsModalLabel">
                                BRN Number: <span id="modalBrn">—</span>
                            </h4>
                        </div>
                    </div>

                    <button type="button" class="close booking-close"
                            data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body booking-body">

                    <!-- CLIENT DETAILS -->
                    <div class="booking-card mb-3">

                        <div class="booking-card-header">
                            <div class="booking-section-icon">
                                <i class="fal fa-user"></i>
                            </div>

                            <div class="booking-section-title">
                                <h6>Client Details</h6>
                                <small>Guest contact information</small>
                            </div>

                            <div class="booking-header-actions">
                                <button type="button" class="btn btn-success btn-sm" id="editBookingBtn">
                                    <i class="fal fa-pen-to-square mr-1"></i>
                                    Edit Booking
                                </button>

                                <button type="button" class="btn btn-primary btn-sm"
                                        id="changeRoomBtn">
                                    <i class="fal fa-exchange-alt mr-1"></i>
                                    Change Room
                                </button>

                                <button type="button" class="btn btn-warning btn-sm"
                                        id="upgradeRoomBtn">
                                    <i class="fal fa-arrows-alt-v mr-1"></i>
                                    Upgrade / Downgrade Room
                                </button>
                            </div>
                        </div>

                        <div class="booking-card-body">
                            <div class="row">

                                <div class="col-lg-6 booking-info-column">
                                    <div class="booking-info-row">
                                        <span class="booking-info-icon">
                                            <i class="fal fa-user"></i>
                                        </span>
                                        <span class="booking-label">Guest Name</span>
                                        <span class="booking-value" id="modalGuestName">—</span>
                                    </div>

                                    <div class="booking-info-row">
                                        <span class="booking-info-icon">
                                            <i class="fal fa-envelope"></i>
                                        </span>
                                        <span class="booking-label">Email</span>
                                        <span class="booking-value" id="modalEmail">—</span>
                                    </div>

                                    <div class="booking-info-row">
                                        <span class="booking-info-icon">
                                            <i class="fal fa-id-card"></i>
                                        </span>
                                        <span class="booking-label">Mobile No.</span>
                                        <span class="booking-value" id="modalMobile">—</span>
                                    </div>
                                </div>

                                <div class="col-lg-6 booking-info-column">
                                    <div class="booking-info-row">
                                        <span class="booking-info-icon">
                                            <i class="fal fa-map-marker-alt"></i>
                                        </span>
                                        <span class="booking-label">Address</span>
                                        <span class="booking-value" id="modalAddress">—</span>
                                    </div>

                                    <div class="booking-info-row">
                                        <span class="booking-info-icon">
                                            <i class="fal fa-check-circle"></i>
                                        </span>
                                        <span class="booking-label">Status</span>
                                        <span class="booking-value">
                                            <span id="modalStatus" class="booking-status">—</span>
                                        </span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- ROOM DETAILS + PAYMENT DETAILS -->
                    <div class="row mb-3">

                        <!-- ROOM DETAILS -->
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
                                        <span class="booking-info-icon">
                                            <i class="fal fa-calendar-check"></i>
                                        </span>
                                        <span class="booking-label">Check-in Date</span>
                                        <span class="booking-value" id="modalCheckIn">—</span>
                                    </div>

                                    <div class="booking-info-row">
                                        <span class="booking-info-icon">
                                            <i class="fal fa-calendar-times"></i>
                                        </span>
                                        <span class="booking-label">Check-out Date</span>
                                        <span class="booking-value" id="modalCheckOut">—</span>
                                    </div>

                                    <div class="booking-info-row">
                                        <span class="booking-info-icon">
                                            <i class="fal fa-door-open"></i>
                                        </span>
                                        <span class="booking-label">Room Type</span>
                                        <span class="booking-value" id="modalRoomType">—</span>
                                    </div>

                                    <div class="booking-info-row">
                                        <span class="booking-info-icon">
                                            <i class="fal fa-hashtag"></i>
                                        </span>
                                        <span class="booking-label">Room Number</span>
                                        <span class="booking-value" id="modalRoomNumber">—</span>
                                    </div>

                                    <div class="booking-info-row">
                                        <span class="booking-info-icon">
                                            <i class="fal fa-door-closed"></i>
                                        </span>
                                        <span class="booking-label">No. of Rooms</span>
                                        <span class="booking-value" id="modalRooms">—</span>
                                    </div>

                                    <div class="booking-info-row">
                                        <span class="booking-info-icon">
                                            <i class="fal fa-clock"></i>
                                        </span>
                                        <span class="booking-label">No. of Nights</span>
                                        <span class="booking-value" id="modalNights">—</span>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- PAYMENT DETAILS -->
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
                                        <strong class="amount-neutral">
                                            ₹ <span id="modalTotal">0.00</span>
                                        </strong>
                                    </div>

                                    <div class="booking-payment-row">
                                        <span>Services Amount</span>
                                        <strong class="amount-neutral">
                                            ₹ <span id="modalServices">0.00</span>
                                        </strong>
                                    </div>

                                    <div class="booking-payment-row">
                                        <span>Discount</span>
                                        <strong class="amount-success">
                                            ₹ <span id="modalDiscount">0.00</span>
                                        </strong>
                                    </div>

                                    <div class="booking-payment-row">
                                        <span>Total Paid Amount</span>
                                        <strong class="amount-success">
                                            ₹ <span id="modalPaid">0.00</span>
                                        </strong>
                                    </div>

                                    <div class="booking-payment-row booking-payment-highlight">
                                        <span>Total Outstanding</span>
                                        <strong class="amount-danger">
                                            ₹ <span id="modalOutstanding">0.00</span>
                                        </strong>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- PAYMENT TRAIL -->
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
                                <button type="button" class="btn btn-success btn-sm"
                                        id="addPaymentDetailsBtn">
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

                    <!-- ADDITIONAL SERVICES -->
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

    {{-- CHANGE ROOM MODAL --}}
    <div class="modal fade" id="changeRoomModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-right-left mr-2"></i>
                        Change Room: <span id="changeRoomBookingCode"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="fal fa-times"></i></span>
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
                            <textarea class="form-control" id="change_room_reason" name="reason" rows="2" maxlength="500"></textarea>
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
    <div class="modal fade" id="upgradeRoomModal" tabindex="-1" role="dialog" aria-labelledby="upgradeRoomModalLabel" aria-hidden="true">
        <div class="modal-dialog  modal-lg" role="document">
            <form id="upgradeRoomForm">
                @csrf
                <div class="modal-content">
                    <input type="hidden" id="upgrade_old_room_id" name="old_room_id">

                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title" id="upgradeRoomModalLabel">
                            <i class="fa-solid fa-arrow-up mr-2"></i>
                            Upgrade Room Type:
                            <span id="upgrade_booking_code"></span>
                        </h5>
                        <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"><i class="fal fa-times"></i></span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" id="upgrade_booking_id" name="booking_id">

                        <select class="d-none" id="new_room_type_id" name="new_room_type_id" required>
                            <option value="">Select room type</option>
                            @foreach($roomTypes as $roomType)
                                <option value="{{ $roomType->id }}">{{ $roomType->name }}</option>
                            @endforeach
                        </select>

                        <div class="form-group">
                            <label for="effective_date">
                                <i class="fa-regular fa-calendar mr-1"></i>
                                Effective From
                            </label>
                            <input type="date" class="form-control" id="effective_date" name="effective_date" required>
                            <small class="form-text text-muted">The room type change applies from this date.</small>
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

                        <div class="mb-3">
                            <h6 class="font-weight-bold mb-1">
                                <i class="fa-solid fa-bed mr-1"></i>
                                Select New Room Type
                            </h6>
                            <small class="text-muted">Choose the room type you want to upgrade to.</small>
                        </div>

                        <div class="row" id="upgradeRoomTypesContainer">
                            <div class="col-12 text-muted">Select a room type to continue.</div>
                        </div>

                        <small class="text-danger d-block mb-3" id="upgradeRoomTypeValidation"></small>
                        <hr>

                        <div class="mb-3">
                            <h6 class="font-weight-bold mb-1">
                                <i class="fa-solid fa-door-open mr-1"></i>
                                Select Replacement Room
                            </h6>
                            <small class="text-muted">Select an available room matching the chosen room type.</small>
                        </div>

                        <input type="hidden" id="upgrade_new_room_id" name="new_room_id" value="">

                        <div class="row" id="upgradeRoomsContainer">
                            <div class="col-12 text-muted">Select a room type first to load available rooms.</div>
                        </div>

                        <small class="text-danger d-block mb-3" id="upgradeRoomValidation"></small>

                        <div class="form-group mt-3">
                            <label for="upgrade_reason">Reason (Optional)</label>
                            <textarea class="form-control" id="upgrade_reason" name="reason" rows="2" maxlength="500"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-warning" id="confirmUpgradeRoom">
                            <i class="fa-solid fa-arrow-up mr-1"></i>
                            Update Room Type
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- Pass bookings to JS --}}
<script>
    const bookings = @json($bookings);
</script>

<script>
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const yearSelect = document.getElementById('yearSelect');
    const monthSelect = document.getElementById('monthSelect');

    // Populate Years
    function populateYears() {
        let currentYear = new Date().getFullYear();
        for (let y = currentYear - 5; y <= currentYear + 5; y++) {
            let opt = new Option(y, y);
            if (y === currentYear) opt.selected = true;
            yearSelect.add(opt);
        }
    }


    function getBookingsForRoomDate(roomNumber, date) {
        return (bookings || []).filter(b => {
            if (!['confirmed', 'checked_in', 'checked_out', 'no_show']
                .includes(b.status)) {
                return false;
            }

            const checkIn = String(b.check_in_date).substring(0, 10);
            const checkOut = String(b.check_out_date).substring(0, 10);
            const isSameDayBooking = checkIn === checkOut;
            const isDateInRange = isSameDayBooking
                ? date === checkIn
                : (date >= checkIn && date < checkOut);

            if (!isDateInRange) {
                return false;
            }

            return b.room_distributions?.some(rd => {
                return String(rd.room_number?.room_number) ===
                    String(roomNumber);
            });
        });
    }

    function getBookingForRoomDate(roomNumber, date) {
        const bookingsForDate = getBookingsForRoomDate(roomNumber, date);
        return bookingsForDate.length ? bookingsForDate[0] : null;
    }

    function isBooked(roomNumber, date) {
        return getBookingsForRoomDate(roomNumber, date).length > 0;
    }


    function generateCalendar() {
        const year = parseInt(yearSelect.value);
        const month = parseInt(monthSelect.value);

        const dateRow = document.getElementById('dateRow');
        const dayRow = document.getElementById('dayRow');

        dateRow.innerHTML = '<th>Date</th>';
        dayRow.innerHTML = '<th>Day</th>';

        let days = new Date(year, month, 0).getDate();

        for (let d = 1; d <= days; d++) {
            let date = new Date(year, month - 1, d);

            dateRow.innerHTML += `<th>${d}</th>`;
            dayRow.innerHTML += `<th>${dayNames[date.getDay()]}</th>`;
        }

        updateRows(days, year, month);
    }


    function updateRows(days, year, month) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        document.querySelectorAll('.room-data').forEach(row => {
            const room = row.dataset.room;
            row.innerHTML = `<td>${room}</td>`;

            for (let d = 1; d <= days; d++) {
                const dateStr =
                    `${year}-${String(month).padStart(2, '0')}-${String(d).padStart(2, '0')}`;

                const currentDate = new Date(year, month - 1, d);
                currentDate.setHours(0, 0, 0, 0);

                const bookingsForDate = getBookingsForRoomDate(room, dateStr);
                const booking = bookingsForDate[0] || null;

                let cell;

                if (booking) {
                    const bookingLines = bookingsForDate.map((item) => {
                        const guest = item.primary_guest?.full_name || 'Guest details unavailable';
                        const brn = item.booking_code || 'N/A';
                        const noOfGuests = item.no_of_guests ?? 'N/A';
                        const noOfRooms = item.no_of_rooms ?? item.room_distributions?.length ?? 1;
                        const number = item.primary_guest?.mobile ?? 'N/A';

                        const rawCheckInDate = item.actual_check_in_at || item.check_in_date || item.checkin_date || '';
                        const rawCheckOutDate = item.actual_check_out_at || item.check_out_date || item.checkout_date || '';
                        const checkInDate = String(rawCheckInDate).substring(0, 10);
                        const checkOutDate = String(rawCheckOutDate).substring(0, 10);

                        const normalizeTime = (value) => {
                            if (!value || value === 'null' || value === 'N/A') {
                                return 'N/A';
                            }

                            const rawText = String(value).includes('T')
                                ? String(value).split('T')[1]
                                : String(value).includes(' ')
                                    ? String(value).split(' ')[1]
                                    : String(value);

                            const timeText = rawText ? rawText.slice(0, 5) : 'N/A';
                            if (timeText === 'N/A') return 'N/A';

                            const [hours, minutes] = timeText.split(':').map(Number);
                            if (Number.isNaN(hours) || Number.isNaN(minutes)) return timeText;

                            const suffix = hours >= 12 ? 'PM' : 'AM';
                            const hour12 = hours % 12 === 0 ? 12 : hours % 12;
                            return `${hour12}:${String(minutes).padStart(2, '0')} ${suffix}`;
                        };

                        const checkInTime = normalizeTime(item.actual_check_in_at || item.check_in_time || item.checkin_time || item.checkInTime);
                        const checkOutTime = normalizeTime(item.actual_check_out_at || item.check_out_time || item.checkout_time || item.checkOutTime);

                        return [
                            `BRN: ${brn}`,
                            `Guest: ${guest}`,
                            `Room: ${room}`,
                            `No of Guests: ${noOfGuests}`,
                            `No of Rooms: ${noOfRooms}`,
                            `Mobile: ${number}`,
                            `Check-in: ${checkInDate} ${checkInTime}`,
                            `Check-out: ${checkOutDate} ${checkOutTime}`,
                            `Status: ${item.status}`
                        ].join('\n');
                    });

                    const tooltip = bookingLines.join('\n\n');

                    cell = document.createElement('td');
                    cell.className = currentDate < today ?
                        'past-booked' :
                        (currentDate.getTime() === today.getTime() ? 'today-booked' : 'booked-cell');

                    if (bookingsForDate.length > 1) {
                        cell.classList.add('multi-booked');
                    }

                    cell.textContent = '';
                    cell.title = tooltip;
                    cell.dataset.bookingId = booking.id ?? '';
                    cell.dataset.bookingDate = dateStr;
                    cell.setAttribute('aria-label', tooltip);
                    cell.setAttribute('tabindex', '0');
                    cell.setAttribute('role', 'button');

                } else {
                    cell = document.createElement('td');

                    if (currentDate < today) {
                        cell.className = 'past-date';
                        cell.title = 'Past Date';
                    } else {
                        cell.className = 'bg-success text-white';
                        cell.title = 'Available';
                    }
                }

                row.appendChild(cell);
            }
        });
    }


    let selectedRoom = null;
    let baseAmount = 0;
    let guestIndex = 0;

    const countriesOptions = `
        @foreach($countries as $country)
        <option value="{{ $country->id }}">{{ $country->name }}</option>
        @endforeach
    `;

    function toggleHourlyBookingFields() {
        const rateType = document.querySelector('input[name="rate_type"]:checked')?.value || document.getElementById('defaultHotelRateType')?.value || 'room';
        const hourlyFields = document.querySelectorAll('.hourly-time-row');
        const showHourly = rateType === 'hourly';

        hourlyFields.forEach(row => {
            row.style.display = showHourly ? 'flex' : 'none';
        });
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
            checkOutInput.value = checkInDate;
            return;
        }

        const currentCheckout = checkOutInput.value;
        const nextDate = new Date(checkInDate);
        nextDate.setDate(nextDate.getDate() + 1);
        const formattedNextDate = nextDate.toISOString().split('T')[0];

        if (!currentCheckout || currentCheckout <= checkInDate) {
            checkOutInput.value = formattedNextDate;
        }
    }

    document.addEventListener('change', function (event) {
        if (event.target && event.target.id === 'checkInDate') {
            applyCheckinCheckoutDefaults();
        }

        if (event.target && event.target.name === 'rate_type') {
            toggleHourlyBookingFields();
            applyCheckinCheckoutDefaults();
        }
    });

    toggleHourlyBookingFields();
    applyCheckinCheckoutDefaults();

    document.getElementById('searchAvailability')?.addEventListener('click', async function() {
        const checkIn = document.getElementById('checkInDate').value;
        const checkOut = document.getElementById('checkOutDate').value;
        const noOfGuests = document.getElementById('noOfGuests').value;
        const noOfRooms = document.getElementById('noOfRooms').value;
        const selectedRateType = document.querySelector('input[name="rate_type"]:checked')?.value || document.getElementById('defaultHotelRateType')?.value || 'room';
        const checkInTime = document.getElementById('checkInTime')?.value || '';
        const checkOutTime = document.getElementById('checkOutTime')?.value || '';

        if (!checkIn || !checkOut) {
            toastr.warning('Select both check-in and check-out dates.');
            return;
        }

        if (selectedRateType === 'hourly' && (!checkInTime || !checkOutTime)) {
            toastr.warning('Please select both check-in time and check-out time for hourly bookings.');
            return;
        }

        if (checkOut <= checkIn && selectedRateType !== 'hourly') {
            toastr.warning('Check-out date must be after check-in date.');
            return;
        }

        const container = document.getElementById('availabilityResults');
        const loader = document.getElementById('availabilityLoader');

        container.style.display = 'none';
        container.innerHTML = '';
        loader.style.display = 'block';

        this.disabled = true;
        this.innerHTML = 'Searching...';

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
                        ? `${room.dirty_rooms} (${dirtyRoomNumbers.slice(0, 3).join(', ')}${dirtyRoomNumbers.length > 3 ? '...' : ''})`
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
                            ${room.available > 0
                                ? `<button class="btn btn-success btn-sm bookNowBtn" data-room='${JSON.stringify(room)}'>Book Now</button>`
                                : `<span class="badge bg-danger">Sold Out</span>`}
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
            this.disabled = false;
            this.innerHTML = 'Search';
        }
    });

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
                           data-bs-placement="top"
                           style="font-size:12px;margin-bottom:2px;"
                           title="Guests: ${room.base_guests} ${room.extra_allowed > 0 ? `(+${room.extra_allowed} extra allowed)` : ''}">
                        </i>
                        <input type="hidden" name="base_amount" id="baseAmount" value="${room.total_price}">
                    </div>
                    <button class="btn btn-danger btn-sm mx-1" onclick="clearSelectedRoom()">Change</button>
                </div>
            </div>
        `;
    }

    document.addEventListener('click', function(e) {
        const bookNowBtn = e.target.closest('.bookNowBtn');

        if (bookNowBtn) {
            const roomData = JSON.parse(bookNowBtn.getAttribute('data-room'));
            selectedRoom = roomData;
            baseAmount = parseFloat(selectedRoom.total_price);

            document.getElementById('availabilityResults').style.display = 'none';
            showSelectedRoom(roomData);

            document.getElementById('finalAmount').innerText = baseAmount.toFixed(2);
            document.getElementById('totalAmountInput').value = baseAmount.toFixed(2);
            document.getElementById('bookingFormSection').style.display = 'block';
            document.querySelectorAll('.serviceCheck').forEach(cb => cb.checked = false);
            document.querySelectorAll('#checkInDate, #checkOutDate, #noOfGuests, #noOfRooms, #searchAvailability').forEach(el => el.disabled = true);
        }

        const addBtn = e.target.closest('.addGuestBtn');
        if (addBtn) {
            const guestInput = document.getElementById('noOfGuests');
            const maxGuests = parseInt(guestInput?.value || 1, 10);
            const currentGuests = document.querySelectorAll('.guest-card').length;

            if (currentGuests >= maxGuests) {
                toastr.warning('You can only add ' + maxGuests + ' guest(s).');
                return;
            }

            const guestContainer = addBtn.closest('.modal').querySelector('.guestContainer');
            if (guestContainer) {
                guestContainer.insertAdjacentHTML('beforeend', createGuestForm(currentGuests));
            }
            return;
        }

        const removeBtn = e.target.closest('.removeGuestBtn');
        if (removeBtn) {
            const guestCard = removeBtn.closest('.guest-card');
            const guestContainer = guestCard.closest('.guestContainer');
            if (!guestCard || !guestContainer) return;
            guestCard.remove();
            guestContainer.querySelectorAll('.guest-card').forEach((card, i) => {
                card.setAttribute('data-index', i);
                const heading = card.querySelector('h6');
                if (heading) heading.textContent = 'Guest ' + (i + 1);
                card.querySelectorAll('input, select, textarea').forEach(function(field) {
                    let name = field.getAttribute('name');
                    if (name) {
                        name = name.replace(/guests\[\d+\]/, 'guests[' + i + ']');
                        field.setAttribute('name', name);
                    }
                });
            });
        }

        const addProofBtn = e.target.closest('.addProofBtn');
        if (addProofBtn) {
            const guestIndexValue = Number(addProofBtn.dataset.guest || 0);
            const proofContainer = addProofBtn.closest('.idProofSection').querySelector('.proofContainer');
            const proofCount = proofContainer.querySelectorAll('.proof-row').length;
            proofContainer.insertAdjacentHTML('beforeend', getProofRow(guestIndexValue, proofCount, false));
        }

        const removeProofBtn = e.target.closest('.removeProofBtn');
        if (removeProofBtn) {
            const proofRow = removeProofBtn.closest('.proof-row');
            if (proofRow) proofRow.remove();
        }

        if (e.target.closest('#confirmBookingBtn')) {
            confirmBooking();
        }
    });

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

    function validatePaymentFields(root = document) {
        const paidAtField = root.querySelector ? root.querySelector('#paidAt') : null;
        const referenceField = root.querySelector ? root.querySelector('#reference') : null;
        const amountField = root.querySelector ? root.querySelector('#amount') : null;

        if (!paidAtField || !referenceField || !amountField) return true;

        const hasPaidAt = (paidAtField.value || '').trim() !== '';
        const hasReference = (referenceField.value || '').trim() !== '';
        const hasAmount = (amountField.value || '').trim() !== '' && Number(amountField.value) > 0;

        referenceField.required = hasPaidAt;
        amountField.required = hasPaidAt;

        if (hasPaidAt && (!hasReference || !hasAmount)) {
            toastr.error('When Paid At is selected, Reference and Amount are required.');
            if (!hasReference) referenceField.focus();
            else amountField.focus();
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

    async function confirmBooking() {
        if (!selectedRoom) {
            toastr.error('Room not selected');
            return;
        }

        if (!validatePaymentFields(document.getElementById('bookingFormSection') || document)) {
            return;
        }

        const selectedRateType = document.querySelector('input[name="rate_type"]:checked')?.value || document.getElementById('defaultHotelRateType')?.value || 'room';
        const checkInTime = document.getElementById('checkInTime')?.value || '';
        const checkOutTime = document.getElementById('checkOutTime')?.value || '';

        const formData = new FormData();
        formData.append('room_type_id', selectedRoom.room_type_id);
        formData.append('check_in_date', document.getElementById('checkInDate').value);
        formData.append('check_out_date', document.getElementById('checkOutDate').value);
        formData.append('rate_type', selectedRateType);
        formData.append('remark', document.getElementById('remark').value);
        formData.append('no_of_guests', document.getElementById('noOfGuests').value);
        formData.append('no_of_rooms', document.getElementById('noOfRooms').value);
        formData.append('total_amount', document.getElementById('totalAmountInput').value);
        formData.append('base_amount', document.getElementById('baseAmount').value);

        if (selectedRateType === 'hourly') {
            formData.append('check_in_time', checkInTime);
            formData.append('check_out_time', checkOutTime);
        }

        const agentSelect = document.querySelector('select[name="agent_id"]');
        if (agentSelect && agentSelect.value) formData.append('agent_id', agentSelect.value);

        formData.append('payment_method', document.getElementById('paymentMethod').value);
        formData.append('paid_at', document.getElementById('paidAt').value);
        formData.append('reference', document.getElementById('reference').value);
        formData.append('amount_paid', document.getElementById('amount').value);

        document.querySelectorAll('.serviceCheck:checked').forEach((checkbox) => {
            const qtyInput = checkbox.closest('.form-check').querySelector('.serviceQty');
            formData.append('services[]', checkbox.value);
            formData.append('service_qty[' + checkbox.value + ']', qtyInput.value);
        });

        document.querySelectorAll('.guest-card').forEach((card) => {
            card.querySelectorAll('input, select, textarea').forEach(input => {
                if (!input.name) return;
                if (input.type === 'file') {
                    if (input.files[0]) formData.append(input.name, input.files[0]);
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
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(json) {
                    if (json.status) {
                        toastr.success(json.message ?? 'Booking Confirmed');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        toastr.error(json.message ?? 'Booking failed');
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        Object.values(xhr.responseJSON.errors).forEach(errorArr => toastr.error(errorArr[0]));
                    } else {
                        toastr.error(xhr.responseJSON?.message ?? 'Server error occurred');
                    }
                }
            });
        } catch (err) {
            toastr.error('Server error occurred');
        }
    }

    function clearSelectedRoom() {
        const loader = document.getElementById('availabilityLoader');
        const loaderText = document.getElementById('availabilityLoaderText');
        const results = document.getElementById('availabilityResults');

        loaderText.innerText = 'Loading available rooms...';
        loader.style.display = 'block';
        results.style.display = 'none';
        document.getElementById('selectedRoomContainer').innerHTML = '';
        document.getElementById('bookingFormSection').style.display = 'none';

        setTimeout(() => {
            results.style.display = 'block';
            document.querySelectorAll('#checkInDate, #checkOutDate, #noOfGuests, #noOfRooms, #searchAvailability').forEach(el => el.disabled = false);
            selectedRoom = null;
            baseAmount = 0;
            document.getElementById('finalAmount').innerText = '0';
            document.querySelectorAll('.serviceCheck').forEach(cb => {
                cb.checked = false;
                const qtyInput = cb.closest('.form-check').querySelector('.serviceQty');
                qtyInput.disabled = true;
                qtyInput.value = 1;
            });
            loader.style.display = 'none';
            loaderText.innerText = 'Checking room availability...';
        }, 600);
    }

    function createGuestForm(index) {
        return `
            <div class="card p-3 mb-3 guest-card" data-index="${index}">
                <div class="d-flex justify-content-between">
                    <div class="mb-2"><h6 class="mb-0">Guest ${index + 1}</h6></div>
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="mb-0 ${index > 0 ? 'mx-2' : ''}">
                            <input type="radio" name="primary_guest" value="${index}" ${index === 0 ? 'checked' : ''}> Lead Guest
                        </label>
                        ${index > 0 ? `<button type="button" class="btn btn-sm btn-danger removeGuestBtn">Remove Guest</button>` : ''}
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3"><label>Mobile</label><input type="text" name="guests[${index}][mobile]" class="form-control guestMobile"></div>
                    <div class="col-md-3"><label>Name</label><input type="text" name="guests[${index}][name]" class="form-control"></div>
                    <div class="col-md-3"><label>Email</label><input type="email" name="guests[${index}][email]" class="form-control"></div>
                    <div class="col-md-3"><label>Date of Birth</label><input type="date" name="guests[${index}][dob]" class="form-control"></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3">
                        <label>Country</label>
                        <select name="guests[${index}][country_id]" class="form-control select2 country-dropdown" data-target="#state_${index}" onchange="loadStatesByCountry(this)">
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
                    <div class="col-md-6"><label>Address</label><textarea name="guests[${index}][address]" class="form-control"></textarea></div>
                </div>
                <hr>
                <div class="idProofSection">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="mb-0">ID Proofs</label>
                        <button type="button" class="btn btn-sm btn-success addProofBtn" data-guest="${index}"><i class="fa fa-plus"></i> Add</button>
                    </div>
                    <div class="proofContainer">
                        ${getProofRow(index, 0, true)}
                    </div>
                </div>
            </div>
        `;
    }

    function getProofRow(guestIndex, proofIndex, isDefault = false) {
        return `
            <div class="row mt-2 proof-row">
                <div class="col-md-4">
                    <input type="text" name="guests[${guestIndex}][id_proofs][${proofIndex}][id_type]" class="form-control" placeholder="ID Type (Aadhar, Passport)" required>
                </div>
                <div class="col-md-4">
                    <input type="file" name="guests[${guestIndex}][id_proofs][${proofIndex}][id_file]" class="form-control" required>
                </div>
                <div class="col-md-2">
                    ${!isDefault ? `<button type="button" class="btn btn-sm btn-danger removeProofBtn"><i class="fa fa-trash"></i></button>` : ``}
                </div>
            </div>
        `;
    }

    function clearGuestCard(guestCard, guestIndex) {
        guestCard.find('input[type="text"], input[type="email"], input[type="date"]').not('.guestMobile').val('');
        guestCard.find('textarea[name="guests[' + guestIndex + '][address]"]').val('');
        guestCard.find('select[name="guests[' + guestIndex + '][country_id]"]').val('');
        guestCard.find('select[name="guests[' + guestIndex + '][state_id]"]').html('<option value="">Select State</option>');
        loadGuestProofs(guestCard, [], guestIndex);
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
            const fileLabel = (proof.id_type || 'ID Proof').replace(/_/g, ' ');
            const isImage = proofUrl && /\.(png|jpe?g|gif|webp|bmp|svg)(\?.*)?$/i.test(proofUrl);

            const proofHtml = `
                <div class="row mt-2 proof-row align-items-end" data-proof-id="${proof.id ?? ''}">
                    <input type="hidden" name="guests[${guestIndex}][id_proofs][${index}][proof_id]" value="${proof.id ?? ''}">
                    <input type="hidden" name="guests[${guestIndex}][id_proofs][${index}][id_type]" value="${proof.id_type || ''}">
                    <input type="hidden" name="guests[${guestIndex}][id_proofs][${index}][id_number]" value="${proof.id_number || ''}">
                    <div class="col-md-4">
                        <label class="small text-muted d-block mb-1">ID Type</label>
                        <input type="text" value="${proof.id_type || ''}" class="form-control" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted d-block mb-1">ID File</label>
                        ${proofUrl ? `<a href="${proofUrl}" target="_blank" class="btn btn-sm btn-outline-secondary ${isImage ? 'd-block' : ''}">${fileLabel}</a>` : '<span class="text-muted">No file</span>'}
                    </div>
                    <div class="col-md-2">
                        ${proof.id ? `<button type="button" class="btn btn-sm btn-danger removeProofBtn"><i class="fa fa-trash"></i></button>` : ''}
                    </div>
                </div>
            `;
            container.append(proofHtml);
        });
    }

    function hydrateGuestStateSelection(countrySelect, stateSelect, selectedStateId = null) {
        const countryId = $(countrySelect).val();
        if (!countryId) {
            $(stateSelect).html('<option value="">Select State</option>');
            return;
        }

        $.ajax({
            url: '{{ route('get.states.by.country') }}',
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

    function loadStatesByCountry(selectElement) {
        const target = selectElement.dataset.target;
        const stateSelect = document.querySelector(target);
        hydrateGuestStateSelection(selectElement, stateSelect);
    }

    const createGuestContainer = document.getElementById('createGuestContainer');
    if (createGuestContainer) {
        createGuestContainer.innerHTML = createGuestForm(guestIndex);
    }

    $(document).on('blur', '.guestMobile', function() {
        const mobile = $(this).val().trim();
        if (!mobile) return;

        const guestCard = $(this).closest('.guest-card');
        const guestIndex = guestCard.data('index');

        $.ajax({
            url: '/guest/find',
            type: 'GET',
            data: { mobile: mobile },
            success: function(response) {
                const guest = response.data;
                if (!guest || !guest.id) {
                    clearGuestCard(guestCard, guestIndex);
                    return;
                }

                guestCard.find('input[name="guests[' + guestIndex + '][name]"]').val(guest.full_name ?? '');
                guestCard.find('input[name="guests[' + guestIndex + '][email]"]').val(guest.email ?? '');
                guestCard.find('textarea[name="guests[' + guestIndex + '][address]"]').val(guest.address ?? '');
                guestCard.find('input[name="guests[' + guestIndex + '][dob]"]').val(guest.dob ?? '');

                const countrySelect = guestCard.find('select[name="guests[' + guestIndex + '][country_id]"]');
                const stateSelect = guestCard.find('select[name="guests[' + guestIndex + '][state_id]"]');

                countrySelect.val(guest.country_id || '');
                if (guest.country_id) {
                    hydrateGuestStateSelection(countrySelect[0], stateSelect[0], guest.state_id ?? null);
                }

                loadGuestProofs(guestCard, guest.id_proofs ?? [], guestIndex);
            },
            error: function(err) {
                console.log(err);
            }
        });
    });

    // Init
    populateYears();
    yearSelect.value = new Date().getFullYear();
    monthSelect.value = new Date().getMonth() + 1;

    yearSelect.addEventListener('change', generateCalendar);
    monthSelect.addEventListener('change', generateCalendar);

    generateCalendar();


    function setModalText(id, value) {
        document.getElementById(id).textContent =
            value === null || value === undefined || value === '' ?
            'N/A' :
            value;
    }

    function formatAmount(value) {
        return Number(value ?? 0).toFixed(2);
    }

    let activeBookingForDetails = null;
    let activeRoomNumberForDetails = null;

    function openBookingModal(booking, roomNumber) {
        if (!booking) return;

        activeBookingForDetails = booking;
        activeRoomNumberForDetails = roomNumber;

        console.log('Opening modal for booking:', booking, 'Room number:', roomNumber);

        const distribution = booking.room_distributions?.find(rd =>
            String(rd.room_number?.room_number) === String(roomNumber)
        );

        const guest =
            booking.guest ||
            booking.customer ||
            booking.user || {};

        const roomType =
            distribution?.room_number?.room_type?.name ||
            booking.room_type?.name ||
            booking.roomType?.name ||
            'N/A';

        const checkIn = String(booking.check_in_date ?? '').substring(0, 10);
        const checkOut = String(booking.check_out_date ?? '').substring(0, 10);

        const date1 = new Date(`${checkIn}T00:00:00`);
        const date2 = new Date(`${checkOut}T00:00:00`);

        const nights = checkIn && checkOut &&
            !Number.isNaN(date1.getTime()) &&
            !Number.isNaN(date2.getTime()) ?
            Math.max(0, Math.round((date2 - date1) / 86400000)) :
            'N/A';

        const total = Number(
            booking.total_deal_amount ?? booking.total_amount ??
            booking.grand_total ?? booking.total_price ?? 0
        );

        const services = Number(
            booking.services_amount ?? booking.total_services ?? 0
        );

        const discount = Number(booking.discount ?? booking.discount_amount ?? 0);

        const paid = Number(
            booking.total_paid_amount ?? booking.paid_amount ??
            booking.total_paid ?? 0
        );

        const outstanding = Number(
            booking.total_outstanding ??
            booking.outstanding_amount ??
            Math.max(0, total - discount - paid)
        );

        const changeRoomBtn = document.getElementById('changeRoomBtn');
        const upgradeRoomBtn = document.getElementById('upgradeRoomBtn');
        const normalizedStatus = String(booking.status ?? '').toLowerCase();
        const shouldShowRoomActions =
            !['cancelled', 'no_show', 'checked_out'].includes(normalizedStatus) &&
            Boolean(booking.actual_check_in_at) &&
            !booking.actual_check_out_at;

        const selectedDistribution = booking.room_distributions?.find(rd =>
            String(rd.room_number?.room_number) === String(roomNumber)
        ) || booking.room_distributions?.[0] || null;

        if (changeRoomBtn) {
            changeRoomBtn.dataset.id = booking.id;
            changeRoomBtn.dataset.code = booking.booking_code;
            changeRoomBtn.dataset.roomNumberId = selectedDistribution?.room_number_id ?? '';
            changeRoomBtn.dataset.roomTypeId = selectedDistribution?.room_type_id ?? '';
            changeRoomBtn.style.display = shouldShowRoomActions ? 'inline-flex' : 'none';
        }

        if (upgradeRoomBtn) {
            upgradeRoomBtn.dataset.id = booking.id;
            upgradeRoomBtn.dataset.code = booking.booking_code;
            upgradeRoomBtn.dataset.checkin = booking.check_in_date;
            upgradeRoomBtn.dataset.roomTypeId = selectedDistribution?.room_type_id ?? booking.room_distributions?.[0]?.room_type_id ?? '';
            upgradeRoomBtn.dataset.roomNumberId = selectedDistribution?.room_number_id ?? booking.room_distributions?.[0]?.room_number_id ?? '';
            upgradeRoomBtn.style.display = shouldShowRoomActions ? 'inline-flex' : 'none';
        }

        setModalText(
            'modalBrn',
            booking.booking_code
        );

        setModalText(
            'modalGuestName', 
             booking.primary_guest?.full_name 
        );

        setModalText('modalEmail', booking.primary_guest?.email );
        setModalText(
            'modalMobile',
            booking.primary_guest?.mobile 
        );

        setModalText(
            'modalAddress',
            booking.primary_guest?.address
        );

        setModalText('modalStatus', booking.status);
        setModalText('modalCheckIn', checkIn);
        setModalText('modalCheckOut', checkOut);
        setModalText('modalRoomType', booking.room_distributions?.[0]?.room_type?.name ?? 'N/A');
        setModalText('modalRoomNumber', roomNumber);
        setModalText('modalRooms', booking.no_of_rooms ?? booking.room_distributions?.length ?? 1);
        setModalText('modalNights', nights);

        setModalText('modalTotal', formatAmount(total));
        setModalText('modalServices', formatAmount(services));
        setModalText('modalDiscount', formatAmount(discount));
        setModalText('modalPaid', formatAmount(paid));
        setModalText('modalOutstanding', formatAmount(outstanding));

        // Payment trail
        const paymentBody = document.getElementById('modalPaymentTrail');
        paymentBody.replaceChildren();

        const payments = booking.payments ?? [];

        if (!payments.length) {
            const tr = document.createElement('tr');
            const td = document.createElement('td');

            td.colSpan = 8;
            td.className = 'text-center';
            td.textContent = 'No payment details available';
            tr.appendChild(td);
            paymentBody.appendChild(tr);
        } else {
            payments.forEach((payment, index) => {
                const tr = document.createElement('tr');

                const values = [
                    index + 1,
                    payment.method ?? 'N/A',
                    payment.paid_at ?? 'N/A',
                    payment.transaction_reference ?? 'N/A',
                    formatAmount(payment.amount),
                    payment.note ?? ''
                ];

                values.forEach(value => {
                    const td = document.createElement('td');
                    td.textContent = value;
                    tr.appendChild(td);
                });

                paymentBody.appendChild(tr);
            });
        }

        // Additional services
        const servicesContainer = document.getElementById('modalServicesList');
        servicesContainer.replaceChildren();

        const additionalServices =
            booking.additional_services ?? [];
        if (!additionalServices.length) {
            servicesContainer.textContent = 'No additional services available';
        } else {
            additionalServices.forEach(service => {
                const label = document.createElement('label');
                label.className = 'me-3';

                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.disabled = true;
                checkbox.checked = true;

                const name = service.name ?? service.service_name ?? 'Service';
                const amount = Number(service.pivot.amount ?? 0);
                const quantity = Number(service.pivot.quantity ?? 0);

                label.append(
                    checkbox,
                    document.createTextNode(
                        ` ${name} - ₹ ${formatAmount(amount)} x ${quantity}`
                    )
                );

                servicesContainer.appendChild(label);
            });
        }

        // Compatible with Bootstrap 5 and Bootstrap 4.

        const modalElement = document.getElementById('bookingDetailsModal');

        if (window.jQuery && jQuery.fn.modal) {
            // Bootstrap 4 or Bootstrap 5 with jQuery plugin
            jQuery(modalElement).modal('show');
        } else if (window.bootstrap && window.bootstrap.Modal) {
            // Bootstrap 5 without jQuery
            const modal = new window.bootstrap.Modal(modalElement);
            modal.show();
        } else {
            console.error('Bootstrap modal JavaScript is not loaded.');
        }

    }

    let editBookingId = null;

    document.getElementById('editBookingBtn')?.addEventListener('click', async function() {
        const bookingId = activeBookingForDetails?.id;

        if (!bookingId) {
            toastr.error('Booking not selected.');
            return;
        }

        try {
            const res = await fetch(`/bookings/${bookingId}/edit`);
            const json = await res.json();

            if (!json.status) {
                toastr.error('Failed to load booking details.');
                return;
            }

            editBookingId = bookingId;
            fillEditModal(json.data);

            setTimeout(() => {
                $('#editModal').modal('show');
            }, 150);
        } catch (error) {
            console.error(error);
            toastr.error('Something went wrong while loading the booking.');
        }
    });

    function fillEditModal(data) {
        const modal = $('#editModal');
        const booking = data.booking;
        const room = data.room_type;
        const guests = data.guests ?? [];
        const services = data.services ?? [];
        const payments = data.payments ?? [];

        modal.find('#checkInDate').val(booking.check_in_date);
        modal.find('#checkOutDate').val(booking.check_out_date);
        modal.find('#noOfGuests').val(booking.no_of_guests);
        modal.find('#noOfRooms').val(booking.no_of_rooms);

        modal.find('#checkInDate').prop('disabled', true);
        modal.find('#checkOutDate').prop('disabled', true);
        modal.find('#noOfGuests').prop('disabled', true);
        modal.find('#noOfRooms').prop('disabled', true);
        modal.find('#searchAvailability').hide();

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

        const baseTotal = parseFloat(booking.base_amount ?? booking.total_amount ?? 0);
        modal.find('#finalAmount').text(baseTotal.toFixed(2));
        modal.find('#totalAmountInput').val(baseTotal.toFixed(2));

        if (booking.agent_id) {
            modal.find('select[name="agent_id"]').val(booking.agent_id).trigger('change');
        }

        modal.find('#remark').val(booking.remark);

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

        modal.find('.serviceCheck').prop('checked', false);
        modal.find('.serviceQty').prop('disabled', true).val(1);

        services.forEach(service => {
            const checkbox = modal.find(`.serviceCheck[value="${service.id}"]`);
            if (!checkbox.length) return;

            checkbox.prop('checked', true);
            const qtyInput = checkbox.closest('.form-check').find('.serviceQty');
            qtyInput.prop('disabled', false).val(service.pivot.quantity);
        });

        calculateTotal();

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
        modal.find('#paymentMethod').closest('.row').after(`<div id="paymentHistory">${paymentTable}</div>`);
    }

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
        }

        if (checkOutValue) {
            formData.set('check_out_date', checkOutValue);
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
                    toastr.success('Booking Updated Successfully');
                    location.reload();
                } else {
                    toastr.error(res.message || 'Update failed');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    Object.values(xhr.responseJSON.errors).forEach(err => toastr.error(err[0]));
                } else {
                    toastr.error('Server error');
                }
            }
        });
    });

    const changeRoomUrl = @json(route('booking.changeRoom', ['bookingId' => '__ID__']));
    const changeRoomTypeUrl = @json(route('booking.changeRoomType', ['bookingId' => '__ID__']));
    const replacementRoomsUrl = @json(route('booking.replacementRooms', ['bookingId' => '__ID__']));

    function bookingUrl(url, id) {
        return url.replace('__ID__', id);
    }

    function showChangeRoomError(message) {
        $('#changeRoomValidation').text(message || 'Something went wrong.');
    }

    document.getElementById('changeRoomBtn')?.addEventListener('click', function() {
        const bookingId = activeBookingForDetails?.id;

        if (!bookingId) {
            toastr.error('Booking not selected.');
            return;
        }

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
        const bookingId = $('#change_room_booking_id').val();
        const roomId = $('#selected_change_room_id').val();
        const oldRoomId = $('#change_room_old_id').val();

        if (!roomId) {
            showChangeRoomError('Please select a room.');
            return;
        }

        $.ajax({
            url: bookingUrl(changeRoomUrl, bookingId),
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content') || $('#changeRoomForm input[name="_token"]').val(),
                new_room_id: roomId,
                old_room_id: oldRoomId || null,
                reason: $('#change_room_reason').val()
            },
            success: function(res) {
                if (res.success === false || res.status === false) {
                    showChangeRoomError(res.message || 'Unable to change room.');
                    return;
                }

                $('#changeRoomModal').modal('hide');
                toastr.success(res.message || 'Room changed successfully.');
                window.location.reload();
            },
            error: function(xhr) {
                showChangeRoomError(xhr.responseJSON?.message || 'Failed to change room.');
            }
        });
    });

    $(document).on('click', '#upgradeRoomBtn', function() {
        const bookingId = $(this).data('id');
        const bookingCode = $(this).data('code');
        const checkinDate = $(this).data('checkin');
        const currentRoomTypeId = String($(this).data('roomTypeId') || '');
        const oldRoomId = $(this).data('roomNumberId') || '';

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
                    const isSelected = String(room.id) === String(oldRoomId || '');
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
            }
        });

        let roomTypesHtml = '';
        @foreach($roomTypes as $roomType)
            roomTypesHtml += `
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="upgrade-type-card" data-room-type-id="{{ $roomType->id }}">
                        <div class="upgrade-icon"><i class="fa-solid fa-bed"></i></div>
                        <div class="upgrade-card-name">{{ $roomType->name }}</div>
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
            loadUpgradeReplacementRooms($('#upgrade_booking_id').val(), roomTypeId, roomId);
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
        loadUpgradeReplacementRooms($('#upgrade_booking_id').val(), roomTypeId, $('#upgrade_old_room_id').val() || null);
    });

    function loadUpgradeReplacementRooms(bookingId, roomTypeId, oldRoomId = null) {
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
                                <div class="upgrade-icon"><i class="fa-solid fa-door-open"></i></div>
                                <div class="upgrade-card-name">Room ${$('<div>').text(room.room_number ?? room.id).html()}</div>
                                <span class="badge badge-secondary upgrade-card-status">Available</span>
                            </div>
                        </div>`;
                });

                $('#upgradeRoomsContainer').html(html);
            },
            error: function(xhr) {
                $('#upgradeRoomsContainer').html('<div class="col-12 text-danger">Unable to load available rooms.</div>');
                toastr.error(xhr.responseJSON?.message || 'Failed to fetch available rooms.');
            }
        });
    }

    $(document).on('click', '#upgradeRoomModal .upgrade-room-card', function() {
        const roomId = $(this).attr('data-room-id');
        const roomNumber = $(this).attr('data-room-number');

        if (!roomId) return;

        $('#upgradeRoomModal .upgrade-room-card').removeClass('selected');
        $('#upgradeRoomModal .upgrade-card-status').removeClass('badge-warning').addClass('badge-secondary').text('Available');

        $(this).addClass('selected');
        $(this).find('.upgrade-card-status').removeClass('badge-secondary').addClass('badge-warning').text('Selected');
        $('#upgrade_new_room_id').val(roomId);
        $('#upgradeRoomValidation').text('Room ' + roomNumber + ' selected.');
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
                    toastr.error(response.message || 'Room type change failed.');
                    return;
                }

                toastr.success(response.message || 'Room type updated successfully.');
                $('#upgradeRoomModal').modal('hide');
                window.location.reload();
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Failed to update room type.');
            }
        });
    });

    // Event delegation works even when calendar cells are regenerated.
    document.querySelector('.calendar-table tbody')
        .addEventListener('click', function(event) {
            const cell = event.target.closest('td.booked-cell, td.past-booked, td.today-booked');

            if (!cell) return;

            const row = cell.closest('tr');
            const roomNumber = row.dataset.room;
            const date = cell.dataset.bookingDate;

            const booking = getBookingForRoomDate(roomNumber, date);

            if (booking) {
                openBookingModal(booking, roomNumber);
            }
        });

    document.querySelector('.calendar-table tbody')
        .addEventListener('keydown', function(event) {
            if (event.key !== 'Enter' && event.key !== ' ') return;

            const cell = event.target.closest('td.booked-cell, td.past-booked, td.today-booked');

            if (!cell) return;

            event.preventDefault();

            const booking = getBookingForRoomDate(
                cell.closest('tr').dataset.room,
                cell.dataset.bookingDate
            );

            if (booking) {
                openBookingModal(booking, cell.closest('tr').dataset.room);
            }
        });
</script>

@endsection