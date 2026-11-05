<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Purchase Order</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }

        .container {
            width: 100%;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .title {
            font-size: 22px;
            font-weight: bold;
        }

        .section {
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: #f5f5f5;
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .text-right {
            text-align: right;
        }

        .summary td {
            border: none;
            padding: 5px;
        }

        .footer {
            margin-top: 30px;
            font-size: 11px;
        }
    </style>
</head>

<body>
    <div class="container">

        {{-- HEADER --}}
        <div class="header">
            <div>
                <div class="title">PURCHASE ORDER</div>
                <div>PO #: {{ $po->po_number }}</div>
                <div>Date: {{ \Carbon\Carbon::parse($po->po_date)->format('d M Y') }}</div>
            </div>

            <div class="text-right">
                <strong>{{ $po->hotel->hotel_name ?? 'Your Hotel Name' }}</strong><br>
                {{ $po->hotel->address_line1 ?? '' }}<br>
                GST: {{ $po->hotel->gst_number ?? '-' }}
            </div>
        </div>

        {{-- VENDOR --}}
        <div class="section">
            <strong>Vendor Details:</strong><br>
            {{ $po->vendor->name }}<br>
            {{ $po->vendor->phone ?? '' }}<br>
            {{ $po->vendor->email ?? '' }}
        </div>

        {{-- ITEMS --}}
        <div class="section">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Rate</th>
                        <th>Tax %</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($po->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->item->name ?? '-' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->unit->short_name ?? '-' }}</td>
                        <td>₹ {{ number_format($item->rate, 2) }}</td>
                        <td>{{ $item->tax_percentage }}%</td>
                        <td>₹ {{ number_format($item->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- SUMMARY --}}
        <div class="section">
            <table class="summary">
                <tr>
                    <td class="text-right"><strong>Sub Total:</strong></td>
                    <td class="text-right">₹ {{ number_format($po->sub_total, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-right"><strong>Tax:</strong></td>
                    <td class="text-right">₹ {{ number_format($po->tax_amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-right"><strong>Discount:</strong></td>
                    <td class="text-right">₹ {{ number_format($po->discount_amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-right"><strong>Grand Total:</strong></td>
                    <td class="text-right"><strong>₹ {{ number_format($po->grand_total, 2) }}</strong></td>
                </tr>
            </table>
        </div>

        {{-- NOTES --}}
        @if($po->notes)
        <div class="section">
            <strong>Notes:</strong><br>
            {{ $po->notes }}
        </div>
        @endif

        {{-- FOOTER --}}
        <div class="footer">
            <p>This is a system generated Purchase Order.</p>
        </div>

    </div>
</body>

</html>