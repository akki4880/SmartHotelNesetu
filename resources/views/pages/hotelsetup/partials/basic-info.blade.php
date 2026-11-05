<form method="POST" action="{{ route('hotel.setup.basic-info') }}" class="ajax-form" data-reset="0" enctype="multipart/form-data" novalidate>
    @csrf

    <div class="row">

        {{-- Hotel Code --}}
        <div class="col-md-4 mb-3">
            <label>Hotel Code</label>
            <input type="text"
                class="form-control"
                value="{{ $hotel->hotel_code ?? '' }}"
                readonly>
        </div>

        {{-- Hotel Name --}}
        <div class="col-md-4 mb-3">
            <label>Hotel Name <span class="text-danger">*</span></label>
            <input type="text"
                name="hotel_name"
                class="form-control"
                value="{{ $hotel->hotel_name ?? '' }}">
            <small class="text-danger error-text" data-error="hotel_name"></small>
        </div>

        {{-- Legal Name --}}
        <div class="col-md-4 mb-3">
            <label>Legal Name</label>
            <input type="text"
                name="legal_name"
                class="form-control"
                value="{{ $hotel->legal_name ?? '' }}">
            <small class="text-danger error-text" data-error="legal_name"></small>
        </div>

        {{-- Phone --}}
        <div class="col-md-4 mb-3">
            <label>Phone <span class="text-danger">*</span></label>
            <input type="text"
                name="phone"
                class="form-control"
                value="{{ $hotel->phone ?? '' }}">
            <small class="text-danger error-text" data-error="phone"></small>
        </div>

        {{-- Alternate Phone --}}
        <div class="col-md-4 mb-3">
            <label>Alternate Phone</label>
            <input type="text"
                name="alternate_phone"
                class="form-control"
                value="{{ $hotel->alternate_phone ?? '' }}">
            <small class="text-danger error-text" data-error="alternate_phone"></small>
        </div>

        {{-- Website --}}
        <div class="col-md-4 mb-3">
            <label>Website</label>
            <input type="text"
                name="website"
                class="form-control"
                value="{{ $hotel->website ?? '' }}">
            <small class="text-danger error-text" data-error="website"></small>
        </div>

        {{-- Address --}}
        <div class="col-md-6 mb-3">
            <label>Address</label>
            <textarea name="address_line1"
                class="form-control"
                rows="2">{{ $hotel->address_line1 ?? '' }}</textarea>
            <small class="text-danger error-text" data-error="address_line1"></small>
        </div>
        <div class="col-md-6 mb-3">
            <label>Logo</label>
            <input type="file" name="logo" class="form-control" accept="image/*">
            <small class="text-danger error-text" data-error="logo"></small>
        </div>
        {{-- City --}}
        <div class="col-md-3 mb-3">
            <label>City</label>
            <input type="text"
                name="city"
                class="form-control"
                value="{{ $hotel->city ?? '' }}">
            <small class="text-danger error-text" data-error="city"></small>
        </div>

        {{-- Country --}}
        <div class="col-md-3 mb-3">
            <label>Country</label>
            <select name="country" class="select2 form-control country-dropdown" data-target="#state" onchange="loadStatesByCountry(this, '{{ $hotel->state ?? '' }}')">
                <option value="">Select Country</option>
                @foreach($countries as $country)
                <option value="{{ $country->id }}" {{ isset($hotel) && $hotel->country == $country->id ? 'selected' : '' }}>
                    {{ $country->name }}
                </option>
                @endforeach
            </select>
            <small class="text-danger error-text" data-error="country"></small>
        </div>

        {{-- State --}}
        <div class="col-md-3 mb-3">
            <label>State</label>
            <select name="state" id="state" class="select2 form-control state-dropdown">
                <option value="">Select State</option>
            </select>
            <small class="text-danger error-text" data-error="state"></small>
        </div>

        {{-- Pincode --}}
        <div class="col-md-3 mb-3">
            <label>Pincode</label>
            <input type="text"
                name="pincode"
                class="form-control"
                value="{{ $hotel->pincode ?? '' }}">
            <small class="text-danger error-text" data-error="pincode"></small>
        </div>

    </div>

    <div class="text-right">
        <button type="submit"
            class="btn btn-primary"
            id="saveBasicInfoBtn">
            Save Basic Info
        </button>
    </div>
</form>

<script>
    $(document).ready(function() {

        $('.country-dropdown').each(function() {
            let selectedState = "{{ $hotel->state ?? '' }}";
            loadStatesByCountry(this, selectedState);
        });

    });
</script>