<h3>Purchase Order</h3>

<p>Dear {{ $po->vendor->name }},</p>

<p>Please find attached the Purchase Order <strong>{{ $po->po_number }}</strong>.</p>

<p><strong>PO Date:</strong> {{ $po->po_date }}</p>
<p><strong>Total:</strong> ₹ {{ number_format($po->grand_total, 2) }}</p>

<br>

<p>Thanks,<br>
{{ $po->hotel->name }}</p>