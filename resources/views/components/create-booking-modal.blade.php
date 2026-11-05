@props([
    'hotelRateTypes' => [],
    'defaultHotelRateType' => 'room',
    'agents' => null,
    'additionalServices' => null,
    'modalId' => 'createModal',
])

@php
    $allAgents = $agents ?? collect();
    $allServices = $additionalServices ?? collect();
    $agentCount = method_exists($allAgents, 'count') ? $allAgents->count() : (is_array($allAgents) ? count($allAgents) : 0);
    $serviceCount = method_exists($allServices, 'count') ? $allServices->count() : (is_array($allServices) ? count($allServices) : 0);
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h4 class="modal-title">Create Booking</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container my-3">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Check-in Date</label>
                            <input type="date" id="checkInDate" name="check_in_date" class="form-control" min="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label>Check-out Date</label>
                            <input type="date" id="checkOutDate" name="check_out_date" class="form-control" min="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-2">
                            <label>No of Guests</label>
                            <input type="number" id="noOfGuests" name="no_of_guests" class="form-control" min="1" value="1" required>
                        </div>
                        <div class="col-md-2">
                            <label>No of Rooms</label>
                            <input type="number" id="noOfRooms" name="no_of_rooms" class="form-control" min="1" value="1" required>
                        </div>
                        <div class="col-md-2 align-self-end">
                            <button class="btn btn-primary" id="searchAvailability">Search</button>
                        </div>
                    </div>

                    <div class="row mt-3 hourly-time-row" style="display:none;">
                        <div class="col-md-3">
                            <label>Check-in Time</label>
                            <input type="time" id="checkInTime" name="check_in_time" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label>Check-out Time</label>
                            <input type="time" id="checkOutTime" name="check_out_time" class="form-control">
                        </div>
                    </div>

                    @if(isset($hotelRateTypes) && is_countable($hotelRateTypes) && count($hotelRateTypes) > 1)
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label>Pricing Mode</label>
                            <div class="d-flex gap-3 mt-1">
                                @foreach($hotelRateTypes as $hotelRateType)
                                <label class="d-flex align-items-center gap-2" style="padding-right:8px;">
                                    <input type="radio" name="rate_type" value="{{ $hotelRateType }}" {{ $hotelRateType === $defaultHotelRateType ? 'checked' : '' }}>
                                    <span style="padding-left:3px;">{{ $hotelRateType === 'hourly' ? 'Hourly Rate' : 'Room Rate' }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <div id="selectedRoomContainer" class="mb-3 container"></div>
                <div id="availabilityLoader" style="display:none;" class="text-center mt-4">
                    <div class="spinner-border text-primary" role="status" style="width:3rem;height:3rem;">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted" id="availabilityLoaderText">Checking room availability...</p>
                </div>

                <div class="container mt-4 d-flex justify-content-between" id="availabilityResults"></div>

                <div id="bookingFormSection" style="display:none;" class="container">
                    <hr>

                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="mb-3">Guest Details</h5>
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-primary mb-3 addGuestBtn" id="createAddGuestBtn">
                                <i class="fa fa-plus"></i> Add Guest
                            </button>
                        </div>
                    </div>

                    <div id="createGuestContainer" class="guestContainer"></div>
                    <hr>

                    <div class="row">
                        @if($agentCount > 0)
                        <div class="col-md-3">
                            <label>Select Agent</label>
                            <select name="agent_id" class="form-control select2">
                                <option value="">Select Agent</option>
                                @foreach($allAgents as $agent)
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

                        <div class="col-md-{{ $agentCount > 0 ? '9' : '12' }}">
                            <label for="remark">Remark</label>
                            <textarea name="remark" id="remark" class="form-control" rows="3" placeholder="Enter booking remark...">{{ old('remark') }}</textarea>
                        </div>
                    </div>

                    <hr>
                    <h5 class="mb-3">Additional Services</h5>
                    <div id="serviceList" class="container row">
                        @if($serviceCount > 0)
                            @foreach($allServices as $service)
                            <div class="form-check col-4 d-flex align-items-center gap-2 mb-2">
                                <input class="form-check-input serviceCheck" type="checkbox" name="services[]" value="{{ $service->id }}" data-price="{{ $service->cost }}" id="service{{ $service->id }}" onchange="toggleQty(this)">
                                <label class="form-check-label me-2" for="service{{ $service->id }}">
                                    {{ $service->service_name }} – ₹{{ $service->cost }}
                                </label>
                                <input type="number" name="service_qty[{{ $service->id }}]" class="form-control serviceQty mx-2" min="1" value="1" style="width:80px;" disabled oninput="calculateTotal()">
                            </div>
                            @endforeach
                        @else
                            <div class="col-12 text-muted">No additional services available</div>
                        @endif
                    </div>

                    <hr>

                    <h5>Payment</h5>

                    <div class="row">
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

                    <hr>

                    <div class="row">
                        <div class="col">
                            <h4 class="">
                                Total: ₹<span id="finalAmount">0</span>
                            </h4>
                            <input type="hidden" name="total_amount" id="totalAmountInput">
                        </div>
                        <div class="col d-flex justify-content-end">
                            <button class="btn btn-success" id="confirmBookingBtn">
                                Confirm Booking
                            </button>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer border-top"></div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const formatTime = (date) => {
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${hours}:${minutes}`;
        };

        const setHourlyTimeDefaults = () => {
            const selectedRateType = document.querySelector('input[name="rate_type"]:checked')?.value || 'room';
            const checkInTimeInput = document.getElementById('checkInTime');
            const checkOutTimeInput = document.getElementById('checkOutTime');

            if (selectedRateType !== 'hourly' || !checkInTimeInput || !checkOutTimeInput) {
                return;
            }

            const now = new Date();
            const checkInTime = formatTime(now);
            const checkoutTime = formatTime(new Date(now.getTime() + 60 * 60 * 1000));

            if (!checkInTimeInput.value) {
                checkInTimeInput.value = checkInTime;
            }

            if (!checkOutTimeInput.value) {
                checkOutTimeInput.value = checkoutTime;
            }
        };

        const setDefaultCreateBookingDates = () => {
            const checkInInput = document.getElementById('checkInDate');
            const checkOutInput = document.getElementById('checkOutDate');

            if (!checkInInput || !checkOutInput) return;

            const today = new Date();
            const todayStr = today.toISOString().split('T')[0];

            const tomorrow = new Date(today);
            tomorrow.setDate(today.getDate() + 1);
            const tomorrowStr = tomorrow.toISOString().split('T')[0];

            if (!checkInInput.value) {
                checkInInput.value = todayStr;
            }

            if (!checkOutInput.value || checkOutInput.value <= checkInInput.value) {
                checkOutInput.value = tomorrowStr;
            }

            checkInInput.min = todayStr;
            checkOutInput.min = todayStr;

            const selectedRateType = document.querySelector('input[name="rate_type"]:checked')?.value || 'room';
            if (selectedRateType === 'hourly') {
                setHourlyTimeDefaults();
            }
        };

        document.addEventListener('change', function(event) {
            if (event.target && event.target.name === 'rate_type') {
                setHourlyTimeDefaults();
            }
        });

        const createModal = document.getElementById('createModal');
        if (createModal) {
            createModal.addEventListener('shown.bs.modal', setDefaultCreateBookingDates);
        }

        setDefaultCreateBookingDates();
    });
</script>
