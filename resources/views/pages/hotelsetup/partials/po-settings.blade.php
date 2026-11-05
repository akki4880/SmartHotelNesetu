<form method="POST" action="{{ route('hotel.po-settings.save', $hotel->id) }}" class="ajax-form" data-reset="0" novalidate>

    @csrf

    <div class="row">

        {{-- PO Prefix --}}
        <div class="col-md-4 mb-3">
            <label>PO Prefix</label>
            <input type="text" name="po_prefix" class="form-control"
                value="{{ $hotel->po_prefix ?? 'PO-' }}">
            <small class="text-danger error-text" data-error="po_prefix"></small>
        </div>

        {{-- Starting Number --}}
        <div class="col-md-4 mb-3">
            <label>Starting Number</label>
            <input type="number" name="po_start_number" class="form-control"
                value="{{ $hotel->po_start_number ?? 1 }}">
            <small class="text-danger error-text" data-error="po_start_number"></small>
        </div>

        {{-- Last Number (Read Only) --}}
        <div class="col-md-4 mb-3">
            <label>Last Generated Number</label>
            <input type="number" class="form-control"
                value="{{ $hotel->po_last_number ?? 0 }}" readonly>
        </div>

    </div>

    <hr>

    <div class="row">

        {{-- Preview --}}
        <div class="col-md-12">
            <label>Preview</label>
            <div class="alert alert-info">
                Next PO Number:
                <strong id="poPreview">
                    {{ ($hotel->po_prefix ?? 'PO-') . str_pad(($hotel->po_last_number ?? 0) + 1, 4, '0', STR_PAD_LEFT) }}
                </strong>
            </div>
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

<script>
    $('input[name="po_prefix"], input[name="po_start_number"]').on('input', function() {

        let prefix = $('input[name="po_prefix"]').val() || 'PO-';
        let start = parseInt($('input[name="po_start_number"]').val()) || 1;

        let preview = prefix + String(start).padStart(4, '0');

        $('#poPreview').text(preview);
    });
</script>