<form method="POST" action="{{ route('hotel.pricing.save', $hotel->id) }}" class="ajax-form" data-reset="0" novalidate>
    @csrf

    <div class="row">

        {{-- Rate Type --}}
        <div class="col-md-4 mb-3">
            <label>Pricing Type</label>

            <div class="custom-control custom-checkbox">
                <input type="checkbox"
                    class="custom-control-input"
                    id="room_rate"
                    name="room_rate_type[]"
                    value="room"
                    {{ in_array('room', $hotel->room_rate_type ?? []) ? 'checked' : '' }}>
                <label class="custom-control-label" for="room_rate">
                    Room Rate
                </label>
            </div>

            <div class="custom-control custom-checkbox mt-2">
                <input type="checkbox"
                    class="custom-control-input"
                    id="hourly_rate"
                    name="room_rate_type[]"
                    value="hourly"
                    {{ in_array('hourly', $hotel->room_rate_type ?? []) ? 'checked' : '' }}>
                <label class="custom-control-label" for="hourly_rate">
                    Hourly Rate
                </label>
            </div>

            <small class="text-danger error-text" data-error="room_rate_type"></small>
        </div>


        {{-- Currency --}}
        <div class="col-md-4 mb-3">
            <label>Currency <span class="text-danger">*</span></label>
            <select name="currency" class="form-control select2">
                <option value="">Select Currency</option>
                @foreach($currencies as $currency)
                <option value="{{$currency->currency_code}}" {{ ($hotel->currency ?? '') == $currency->currency_code ? 'selected' : '' }}>{{$currency->currency_code}}</option>
                @endforeach
            </select>
            <small class="text-danger error-text" data-error="currency"></small>
        </div>

    </div>

    <div class="text-right">
        <button type="submit"
            id="savePricingBtn"
            class="btn btn-primary">
            Save Pricing Setup
        </button>
    </div>
</form>