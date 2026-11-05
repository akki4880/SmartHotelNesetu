<form method="POST" action="{{ route('hotel.setup.booking-settings', $hotel->id) }}" class="ajax-form" data-reset="0" novalidate>
    @csrf
    <small class="one-badge-danger">
        You can edit these fields only until the first booking is created.
    </small>
    <div class="row mt-2">
        <div class="col-md-3 mb-3">
            <label>Booking Prefix</label>
            <input type="text"
                name="booking_prefix"
                class="form-control"
                value="{{ $hotel->booking_prefix }}"
                {{ $hotel->bookings()->exists() ? 'readonly' : '' }}>
            <small class="text-danger error-text" data-error="booking_prefix"></small>
        </div>

        <div class="col-md-3 mb-3">
            <label>Booking Start Number</label>
            <input type="number"
                name="booking_sequence"
                class="form-control"
                value="{{ $hotel->booking_sequence }}"
                {{ $hotel->bookings()->exists() ? 'readonly' : '' }}>
            <small class="text-danger error-text" data-error="booking_sequence"></small>
        </div>

    </div>

    <div class="text-right">
        <button type="submit"
            class="btn btn-primary"
            id="saveBasicInfoBtn">
            Save Booking Settings
        </button>
    </div>
</form>