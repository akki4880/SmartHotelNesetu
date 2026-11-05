<form method="POST" action="{{ route('hotel.setup.tax-billing', $hotel->id) }}" class="ajax-form" data-reset="0" novalidate>
    @csrf
    <div class="row">

        {{-- GST Applicable --}}
        <div class="col-md-3 mb-3">
            <label>GST Applicable</label>
            <div class="form-check">
                <input class="form-check-input gstToggle" type="radio" name="is_gst_applicable" value="1"
                    {{ ($hotel->is_gst_applicable ?? false) ? 'checked' : '' }}>
                <label class="form-check-label">Yes</label>
            </div>
            <div class="form-check">
                <input class="form-check-input gstToggle" type="radio" name="is_gst_applicable" value="0"
                    {{ !($hotel->is_gst_applicable ?? false) ? 'checked' : '' }}>
                <label class="form-check-label">No</label>
            </div>
            <small class="text-danger error-text" data-error="is_gst_applicable"></small>
        </div>

        {{-- GST Percentage Section --}}
        <div class="col-md-9 gstFields" style="{{ ($hotel->is_gst_applicable ?? false) ? '' : 'display:none' }}">

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label>CGST %</label>
                    <input type="number" step="0.01" name="cgst_percentage"
                        class="form-control"
                        value="{{ $hotel->cgst_percentage ?? '' }}">
                </div>

                <div class="col-md-3 mb-3">
                    <label>SGST %</label>
                    <input type="number" step="0.01" name="sgst_percentage"
                        class="form-control"
                        value="{{ $hotel->sgst_percentage ?? '' }}">
                </div>

                <div class="col-md-3 mb-3">
                    <label>IGST %</label>
                    <input type="number" step="0.01" name="igst_percentage"
                        class="form-control"
                        value="{{ $hotel->igst_percentage ?? '' }}">
                </div>

                {{-- Tax Type --}}
                <div class="col-md-3 mb-3">
                    <label>Tax Type</label>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="invoice_tax_type" value="exclusive"
                            {{ ($hotel->invoice_tax_type ?? 'exclusive') == 'exclusive' ? 'checked' : '' }}>
                        <label class="form-check-label">
                            Exclusive (Tax added on top)
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="invoice_tax_type" value="inclusive"
                            {{ ($hotel->invoice_tax_type ?? '') == 'inclusive' ? 'checked' : '' }}>
                        <label class="form-check-label">
                            Inclusive (Tax included in price)
                        </label>
                    </div>

                </div>
            </div>
        </div>

        {{-- GST Number --}}
        <div class="col-md-3 mb-3">
            <label>GST Number</label>
            <input type="text"
                name="gst_number"
                class="form-control"
                value="{{ $hotel->gst_number ?? '' }}">
            <small class="text-danger error-text" data-error="gst_number"></small>
        </div>

        {{-- PAN Number --}}
        <div class="col-md-3 mb-3">
            <label>PAN Number</label>
            <input type="text"
                name="pan_number"
                class="form-control"
                value="{{ $hotel->pan_number ?? '' }}">
            <small class="text-danger error-text" data-error="pan_number"></small>
        </div>

    </div>

    <div class="text-right">
        <button type="submit" class="btn btn-primary" id="saveTaxBillingBtn">
            Save Tax & Billing
        </button>
    </div>
</form>

<script>
    $('.gstToggle').on('change', function() {

        if ($(this).val() == 1) {
            $('.gstFields').slideDown();
        } else {
            $('.gstFields').slideUp();
        }
    });
</script>