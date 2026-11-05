@props([
    'modalId' => 'checkinModal',
])

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header one-bg-success text-white">
                <h5 class="modal-title">
                    <i class="fa-solid fa-door-open mr-2"></i>Check In for Booking: <span id="checkinBookingCode"></span>
                </h5>

                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <i class="fal fa-times"></i>
                </button>
            </div>

            <div class="modal-body">

                <form id="checkinForm">
                    @csrf

                    <input type="hidden" name="booking_id" id="checkin_booking_id">
                    <input type="hidden" id="required_rooms">

                    {{-- Check-in Date and Time Section --}}
                    <div class="mb-4">
                        <h6 class="mb-3">
                            <i class="fa-regular fa-clock mr-2"></i>Check-In Date & Time
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="checkinDate" class="font-weight-bold">Date</label>
                                    <input type="date" class="form-control" id="checkinDate" name="checkin_date">
                                    <small class="form-text text-muted">Date of check-in</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="checkinTime" class="font-weight-bold">Time</label>
                                    <input type="time" class="form-control" id="checkinTime" name="checkin_time">
                                    <small class="form-text text-muted">Time of check-in</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Room Selection Section --}}
                    <div class="mb-4">
                        <h6 class="mb-3">
                            <i class="fa-solid fa-door-open mr-2"></i>Select Rooms
                        </h6>
                        <div class="row" id="availableRoomsContainer">
                            <!-- rooms will come here -->
                        </div>

                        <small class="text-danger d-block" id="roomValidation"></small>
                    </div>

                </form>

            </div>

            <div class="modal-footer border-top">
                <button class="btn btn-secondary" data-dismiss="modal">Close</button>

                <button class="btn one-btn-success" id="confirmCheckin">
                    <i class="fa-solid fa-door-open mr-1"></i> Confirm Check In
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const setDefaultCheckInValues = () => {
            const dateInput = document.getElementById('checkinDate');
            const timeInput = document.getElementById('checkinTime');

            if (!dateInput || !timeInput) return;

            const now = new Date();
            const pad = (value) => String(value).padStart(2, '0');

            const today = [
                now.getFullYear(),
                pad(now.getMonth() + 1),
                pad(now.getDate())
            ].join('-');

            const currentTime = [
                pad(now.getHours()),
                pad(now.getMinutes())
            ].join(':');

            dateInput.value = today;
            timeInput.value = currentTime;
        };

        const checkinModal = document.getElementById('{{ $modalId }}');

        if (checkinModal) {
            checkinModal.addEventListener('shown.bs.modal', setDefaultCheckInValues);
        }

        setDefaultCheckInValues();
    });
</script>
