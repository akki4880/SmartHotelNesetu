@extends('invoices.master-invoice1.layouts.index')

@section('title', 'Hotel Invoice')

@php
$data = $viewData ?? [];

$invoice = $data['invoice'] ?? null;
$booking = $data['booking'] ?? null;
$guest = $data['guest'] ?? null;
$hotel = $data['hotel'] ?? null;
$items = $data['items'] ?? [];

$hotelName = $hotel?->hotel_name ?? $hotel?->name ?? 'Smart Hotel';
$hotelAddress = $hotel?->address ?? 'Hotel Address';
$hotelPhone = $hotel?->phone ?? '';
$hotelEmail = $hotel?->email ?? '';

$hotelLogo = $hotel?->logo
? asset('storage/hotel_logos/' . $hotel->logo)
: asset('assets/images/agency/logo.png');

$guestName = $guest?->full_name ?? $guest?->name ?? 'Guest';
$guestEmail = $guest?->email ?? '';
$guestPhone = $guest?->phone ?? '';
$guestAddress = trim(
($guest?->address ?? '') .
($guest?->city ? ', ' . $guest->city : '') .
($guest?->state?->name ? ', ' . $guest->state->name : '')
);

$invoiceNumber = $invoice?->invoice_number ?? 'INV-00001';

$invoiceDate = $invoice?->invoice_date
? \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y')
: now()->format('d M Y');

$subtotal = (float) ($invoice?->subtotal ?? $data['subtotal'] ?? 0);
$totalTax = (float) ($invoice?->total_tax ?? $data['tax'] ?? 0);
$grandTotal = (float) ($invoice?->grand_total ?? $data['grand_total'] ?? 0);

$cgst = (float) ($invoice?->cgst_amount ?? 0);
$sgst = (float) ($invoice?->sgst_amount ?? 0);
$igst = (float) ($invoice?->igst_amount ?? 0);

$roomAmount = (float) ($invoice?->room_amount ?? 0);
$serviceAmount = (float) ($invoice?->service_amount ?? 0);

$invoiceSubtotal = (float) ($invoice?->subtotal ?? $subtotal ?? 0);
$invoiceTaxTotal = (float) ($invoice?->total_tax ?? $totalTax ?? 0);
$invoiceGrandTotal = (float) ($invoice?->grand_total ?? $grandTotal ?? 0);
@endphp

@section('content')

<style>
    .hotel-invoice {
        max-width: 950px;
        margin: 30px auto;
        background: #fff;
        color: #252525;
        font-family: Arial, sans-serif;
        padding: 35px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 5px 25px rgba(0, 0, 0, .06);
    }

    .letterhead {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding-bottom: 22px;
        border-bottom: 3px solid #1f2937;
    }

    .letterhead img {
        max-width: 230px;
        max-height: 100px;
        object-fit: contain;
    }

    .hotel-letterhead-info {
        text-align: right;
        font-size: 13px;
        line-height: 1.8;
        color: #555;
    }

    .invoice-title-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 28px 0;
    }

    .invoice-title-row h1 {
        font-size: 30px;
        font-weight: 800;
        letter-spacing: 2px;
        margin: 0;
        color: #111827;
    }

    .invoice-meta {
        text-align: right;
        font-size: 13px;
        line-height: 1.8;
    }

    .invoice-meta strong {
        color: #111827;
    }

    .info-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
        margin-bottom: 30px;
    }

    .info-card {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        padding: 18px;
        border-radius: 8px;
    }

    .info-card h4 {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #64748b;
        margin: 0 0 12px;
    }

    .info-card h3 {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 8px;
        color: #111827;
    }

    .info-card p {
        font-size: 13px;
        line-height: 1.8;
        margin: 0;
        color: #555;
    }

    .section-heading {
        font-size: 17px;
        font-weight: 700;
        margin: 25px 0 12px;
    }

    .invoice-items {
        width: 100%;
        border-collapse: collapse;
        margin-top: 12px;
    }

    .invoice-items thead {
        background: #1f2937;
        color: white;
    }

    .invoice-items th {
        padding: 13px 12px;
        font-size: 12px;
        text-align: left;
        text-transform: uppercase;
    }

    .invoice-items td {
        padding: 13px 12px;
        border-bottom: 1px solid #e5e7eb;
        font-size: 13px;
    }

    .invoice-items tbody tr:nth-child(even) {
        background: #f9fafb;
    }

    .text-right {
        text-align: right !important;
    }

    .text-center {
        text-align: center !important;
    }

    .invoice-bottom {
        display: flex;
        justify-content: space-between;
        gap: 30px;
        margin-top: 25px;
    }

    .invoice-note {
        flex: 1;
        font-size: 12px;
        color: #64748b;
        line-height: 1.8;
    }

    .invoice-totals {
        width: 330px;
        border-collapse: collapse;
    }

    .invoice-totals td {
        padding: 9px 12px;
        font-size: 13px;
        border-bottom: 1px solid #e5e7eb;
    }

    .invoice-totals .grand-total td {
        background: #1f2937;
        color: #fff;
        font-size: 17px;
        font-weight: 700;
        border: none;
        padding: 15px 12px;
    }

    .invoice-footer {
        margin-top: 45px;
        padding-top: 20px;
        border-top: 1px solid #ddd;
        text-align: center;
        font-size: 12px;
        color: #64748b;
        line-height: 1.8;
    }

    .invoice-footer strong {
        color: #111827;
    }

    @media(max-width: 650px) {
        .hotel-invoice {
            padding: 18px;
        }

        .letterhead,
        .invoice-title-row {
            align-items: flex-start;
            flex-direction: column;
        }

        .hotel-letterhead-info,
        .invoice-meta {
            text-align: left;
        }

        .info-section {
            grid-template-columns: 1fr;
        }

        .invoice-bottom {
            flex-direction: column;
        }

        .invoice-totals {
            width: 100%;
        }

        .invoice-items {
            min-width: 600px;
        }

        .table-scroll {
            overflow-x: auto;
        }
    }

    @media print {
        body {
            background: #fff !important;
        }

        .hotel-invoice {
            max-width: 100%;
            margin: 0;
            padding: 15px;
            border: none;
            box-shadow: none;
        }

        .d-print-none,
        .agency-bottom-content {
            display: none !important;
        }

        .invoice-items thead {
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        .invoice-totals .grand-total td {
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }
    }
</style>

<div class="hotel-invoice" id="download_section">

    {{-- 1. HOTEL LETTERHEAD --}}
    <div class="letterhead">

        <div>
            <img src="{{ $hotelLogo }}" alt="{{ $hotelName }} Logo">
        </div>

        <div class="hotel-letterhead-info">
            <strong style="font-size: 17px; color:#111827;">
                {{ $hotelName }}
            </strong><br>

            {{ $hotelAddress }}<br>

            @if($hotelPhone)
            Phone: {{ $hotelPhone }}<br>
            @endif

            @if($hotelEmail)
            Email: {{ $hotelEmail }}
            @endif
        </div>

    </div>

    {{-- 2. INVOICE TITLE --}}
    <div class="invoice-title-row">

        <h1>INVOICE</h1>

        <div class="invoice-meta">
            <strong>Invoice No:</strong> {{ $invoiceNumber }}<br>
            <strong>Invoice Date:</strong> {{ $invoiceDate }}<br>

            @if($booking)
            <strong>Booking ID:</strong> {{ $booking->id }}
            @endif
        </div>

    </div>

    {{-- 3. CUSTOMER AND BOOKING INFORMATION --}}
    <div class="info-section">

        <div class="info-card">
            <h4>Bill To / Guest Information</h4>

            <h3>{{ $guestName }}</h3>

            <p>
                @if($guestAddress)
                {{ $guestAddress }}<br>
                @endif

                @if($guestPhone)
                Phone: {{ $guestPhone }}<br>
                @endif

                @if($guestEmail)
                Email: {{ $guestEmail }}
                @endif

                @if(!$guestAddress && !$guestPhone && !$guestEmail)
                Guest details not available.
                @endif
            </p>
        </div>

        <div class="info-card">
            <h4>Booking Information</h4>

            <h3>
                {{ $booking->booking_code ?? ('Booking #' . ($booking->id ?? 'N/A')) }}
            </h3>

            <p>
                @if($booking?->actual_check_in_at)
                Check-in:
                {{ \Carbon\Carbon::parse($booking->actual_check_in_at)->format('d M Y, h:i A') }}
                <br>
                @endif

                @if($booking?->actual_check_out_at)
                Check-out:
                {{ \Carbon\Carbon::parse($booking->actual_check_out_at)->format('d M Y, h:i A') }}
                <br>
                @endif

                @if($booking?->status)
                Status: {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                @endif
            </p>
        </div>

    </div>

    {{-- 4. INVOICE ITEMS TABLE --}}
    <h3 class="section-heading">Invoice Details</h3>

    <div class="table-scroll">
        <table class="invoice-items">

            <thead>
                <tr>
                    <th width="6%">#</th>
                    <th width="18%">Description</th>
                    <th width="10%">HSN</th>
                    <th width="10%" class="text-right">Rate</th>
                    <th width="8%" class="text-center">Qty</th>
                    <th width="12%" class="text-right">Subtotal</th>
                    <th width="10%" class="text-right">Discount</th>
                    <th width="10%" class="text-right">Taxable</th>
                    <th width="8%" class="text-right">GST %</th>
                    <th width="8%" class="text-right">CGST</th>
                    <th width="8%" class="text-right">SGST</th>
                    <th width="12%" class="text-right">Total</th>
                </tr>
            </thead>

            <tbody>
                @php
                $invoiceTaxType = strtolower($invoice?->invoice_tax_type ?? 'exclusive');

                $isInclusive = $invoiceTaxType === 'inclusive';
                $isExclusive = $invoiceTaxType === 'exclusive';
                @endphp
                @forelse($items as $index => $item)
                @php
                $lineQty = max((int) ($item->quantity ?? 1), 1);
                $lineRate = (float) ($item->price ?? 0);

                $lineSubtotal = $lineRate * $lineQty;
                $lineDiscount = 0;

                $amountAfterDiscount = max(0, $lineSubtotal - $lineDiscount);

                $gstPercent = 0;

                if ($invoice?->is_gst_applicable) {
                $gstPercent = (float) (
                ($invoice->cgst_percentage ?? 0) +
                ($invoice->sgst_percentage ?? 0)
                );

                if (
                ($invoice->igst_percentage ?? 0) > 0 &&
                ($invoice->cgst_percentage ?? 0) == 0 &&
                ($invoice->sgst_percentage ?? 0) == 0
                ) {
                $gstPercent = (float) $invoice->igst_percentage;
                }
                }

                // GST Calculation based on invoice_tax_type
                if (!$invoice?->is_gst_applicable || $gstPercent <= 0) {

                    $lineTaxable=$amountAfterDiscount;
                    $lineTax=0;
                    $lineTotal=$amountAfterDiscount;

                    } elseif ($isInclusive) {

                    // GST is already included in the item price
                    $lineTaxable=$amountAfterDiscount * 100 / (100 + $gstPercent);

                    $lineTax=$amountAfterDiscount - $lineTaxable;

                    $lineTotal=$amountAfterDiscount;

                    } else {

                    // GST will be added on top of the item price
                    $lineTaxable=$amountAfterDiscount;

                    $lineTax=($lineTaxable * $gstPercent) / 100;

                    $lineTotal=$lineTaxable + $lineTax;
                    }

                    // Calculate CGST / SGST / IGST
                    $lineCgst=0;
                    $lineSgst=0;
                    $lineIgst=0;

                    if ($invoice?->is_gst_applicable) {

                    if (
                    ($invoice->igst_percentage ?? 0) > 0 &&
                    ($invoice->cgst_percentage ?? 0) == 0 &&
                    ($invoice->sgst_percentage ?? 0) == 0
                    ) {
                    $lineIgst = $lineTax;
                    } else {
                    $lineCgst = $lineTax / 2;
                    $lineSgst = $lineTax / 2;
                    }
                    }
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $item->item_name ?? $item->item_type ?? 'Item' }}</strong>
                        </td>
                        <td>--</td>
                        <td class="text-right">₹{{ number_format($lineRate, 2) }}</td>
                        <td class="text-center">{{ $lineQty }}</td>
                        <td class="text-right">₹{{ number_format($lineSubtotal, 2) }}</td>
                        <td class="text-right">₹{{ number_format($lineDiscount, 2) }}</td>
                        <td class="text-right">₹{{ number_format($lineTaxable, 2) }}</td>
                        <td class="text-right">{{ number_format($gstPercent, 2) }}</td>
                        <td class="text-right">₹{{ number_format($lineCgst, 2) }}</td>
                        <td class="text-right">₹{{ number_format($lineSgst, 2) }}</td>
                        <td class="text-right">₹{{ number_format($lineTotal, 2) }}</td>
                    </tr>

                    @empty

                    @if($roomAmount > 0)
                    @php
                    $roomQty = max((int) ($booking?->no_of_rooms ?? 1), 1);
                    $roomRate = $roomQty > 0 ? ($roomAmount / $roomQty) : $roomAmount;
                    $roomCgst = (float) ($invoice?->cgst_amount ?? 0);
                    $roomSgst = (float) ($invoice?->sgst_amount ?? 0);
                    @endphp
                    <tr>
                        <td>1</td>
                        <td><strong>Room Charges</strong></td>
                        <td>--</td>
                        <td class="text-right">₹{{ number_format($roomRate, 2) }}</td>
                        <td class="text-center">{{ $roomQty }}</td>
                        <td class="text-right">₹{{ number_format($roomAmount, 2) }}</td>
                        <td class="text-right">₹0.00</td>
                        <td class="text-right">₹{{ number_format($roomAmount, 2) }}</td>
                        <td class="text-right">{{ number_format((float) (($invoice?->cgst_percentage ?? 0) + ($invoice?->sgst_percentage ?? 0)), 2) }}</td>
                        <td class="text-right">₹{{ number_format($roomCgst, 2) }}</td>
                        <td class="text-right">₹{{ number_format($roomSgst, 2) }}</td>
                        <td class="text-right">₹{{ number_format($roomAmount + $roomCgst + $roomSgst, 2) }}</td>
                    </tr>
                    @endif

                    @if($serviceAmount > 0)
                    @php
                    $serviceQty = max((int) ($booking?->no_of_rooms ?? 1), 1);
                    $serviceRate = $serviceQty > 0 ? ($serviceAmount / $serviceQty) : $serviceAmount;
                    $serviceCgst = (float) (($invoice?->cgst_amount ?? 0) * ($serviceAmount / max($invoiceSubtotal, 0.01)));
                    $serviceSgst = (float) (($invoice?->sgst_amount ?? 0) * ($serviceAmount / max($invoiceSubtotal, 0.01)));
                    @endphp
                    <tr>
                        <td>{{ $roomAmount > 0 ? 2 : 1 }}</td>
                        <td><strong>Extra Charges</strong></td>
                        <td>--</td>
                        <td class="text-right">₹{{ number_format($serviceRate, 2) }}</td>
                        <td class="text-center">{{ $serviceQty }}</td>
                        <td class="text-right">₹{{ number_format($serviceAmount, 2) }}</td>
                        <td class="text-right">₹0.00</td>
                        <td class="text-right">₹{{ number_format($serviceAmount, 2) }}</td>
                        <td class="text-right">{{ number_format((float) (($invoice?->cgst_percentage ?? 0) + ($invoice?->sgst_percentage ?? 0)), 2) }}</td>
                        <td class="text-right">₹{{ number_format($serviceCgst, 2) }}</td>
                        <td class="text-right">₹{{ number_format($serviceSgst, 2) }}</td>
                        <td class="text-right">₹{{ number_format($serviceAmount + $serviceCgst + $serviceSgst, 2) }}</td>
                    </tr>
                    @endif

                    @if($roomAmount <= 0 && $serviceAmount <=0)
                        <tr>
                        <td colspan="12" class="text-center">
                            No invoice items available.
                        </td>
                        </tr>
                        @endif

                        @endforelse

            </tbody>

        </table>
    </div>

    {{-- 5. TAX AND TOTAL --}}
    <div class="invoice-bottom">

        <div class="invoice-note">
            <strong>Note:</strong><br>
            This is a computer-generated invoice and does not require a physical signature.

            <br><br>

            Thank you for choosing {{ $hotelName }}.
            We appreciate your business.
        </div>

        <table class="invoice-totals">

            <tr>
                <td>Subtotal</td>
                <td class="text-right">
                    ₹{{ number_format($invoiceSubtotal, 2) }}
                </td>
            </tr>

            @if($invoice?->is_gst_applicable)

            @if($cgst > 0)
            <tr>
                <td>CGST ({{ $invoice->cgst_percentage ?? 0 }}%)</td>
                <td class="text-right">
                    ₹{{ number_format($cgst, 2) }}
                </td>
            </tr>
            @endif

            @if($sgst > 0)
            <tr>
                <td>SGST ({{ $invoice->sgst_percentage ?? 0 }}%)</td>
                <td class="text-right">
                    ₹{{ number_format($sgst, 2) }}
                </td>
            </tr>
            @endif

            @if($igst > 0)
            <tr>
                <td>IGST ({{ $invoice->igst_percentage ?? 0 }}%)</td>
                <td class="text-right">
                    ₹{{ number_format($igst, 2) }}
                </td>
            </tr>
            @endif

            <tr>
                <td>Total Tax</td>
                <td class="text-right">
                    ₹{{ number_format($invoiceTaxTotal, 2) }}
                </td>
            </tr>

            @else

            <tr>
                <td>Tax</td>
                <td class="text-right">
                    ₹{{ number_format($invoiceTaxTotal, 2) }}
                </td>
            </tr>

            @endif

            <tr class="grand-total">
                <td>Grand Total</td>
                <td class="text-right">
                    ₹{{ number_format($invoiceGrandTotal, 2) }}
                </td>
            </tr>

        </table>

    </div>

    {{-- 6. FOOTER --}}
    <div class="invoice-footer">

        <strong>{{ $hotelName }}</strong><br>

        {{ $hotelAddress }}<br>

        @if($hotelPhone)
        {{ $hotelPhone }}
        @endif

        @if($hotelPhone && $hotelEmail)
        |
        @endif

        @if($hotelEmail)
        {{ $hotelEmail }}
        @endif

        <br><br>

        <strong>Thank You! Visit Again.</strong>

    </div>

</div>

{{-- 7. PRINT AND DOWNLOAD BUTTONS --}}
<div class="d-flex justify-content-center gap-3 my-4 d-print-none">

    <button type="button" class="btn btn-dark" onclick="window.print()">
        <i class="fa fa-print"></i> Print Invoice
    </button>

    <button type="button" class="btn btn-primary" id="generatePDF">
        <i class="fa fa-download"></i> Download PDF
    </button>

</div>

@endsection