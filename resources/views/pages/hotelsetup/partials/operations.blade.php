<form method="POST" action="{{ route('hotel.operations.save', $hotel->id) }}" class="ajax-form" data-reset="0" novalidate>

    @csrf

    <div class="row">

        {{-- Check-in Time --}}
        <div class="col-md-3 mb-3">
            <label>Check-in Time <span class="text-danger">*</span></label>
            <input type="time"
                name="check_in_time"
                class="form-control"
                value="{{ $hotel->check_in_time ?? '' }}">
            <small class="text-danger error-text" data-error="check_in_time"></small>
        </div>

        {{-- Check-out Time --}}
        <div class="col-md-3 mb-3">
            <label>Check-out Time <span class="text-danger">*</span></label>
            <input type="time"
                name="check_out_time"
                class="form-control"
                value="{{ $hotel->check_out_time ?? '' }}">
            <small class="text-danger error-text" data-error="check_out_time"></small>
        </div>

        {{-- Cancellation Policy --}}
        <div class="col-md-12 mb-3">
            <label>Cancellation Policy</label>
            <textarea name="cancellation_policy"
                id="cancellation_policy"
                class="form-control summernote">
                {!! $hotel->cancellation_policy ?? '' !!}
            </textarea>
            <small class="text-danger error-text" data-error="cancellation_policy"></small>
        </div>

        {{-- Terms & Conditions --}}
        <div class="col-md-12 mb-3">
            <label>Terms & Conditions</label>
            <textarea name="terms_conditions"
                id="terms_conditions"
                class="form-control summernote">
                {!! $hotel->terms_conditions ?? '' !!}
            </textarea>
            <small class="text-danger error-text" data-error="terms_conditions"></small>
        </div>

    </div>

    <div class="text-right">
        <button type="submit"
            id="saveOperationsBtn"
            class="btn btn-primary">
            Save Operations
        </button>
    </div>
</form>