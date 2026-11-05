<?php

namespace App\Http\Controllers;

use App\Models\AdditionalService;
use App\Models\Agent;
use App\Models\Booking;
use App\Models\Country;
use App\Models\Invoice;
use App\Models\State;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class InvoiceController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:generate_invoice,create', only: ['create', 'store']),
            new Middleware('permission:generate_invoice,edit', only: ['edit', 'update']),
            new Middleware('permission:generate_invoice,view', only: ['generate', 'getInvoiceData']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'gstin' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'country_id' => ['nullable', 'integer'],
            'state_id' => ['nullable', 'integer'],
            'invoice_date' => ['required', 'date'],
            'items' => ['required', 'array'],
            'grand_total' => ['required', 'numeric'],
            'total_taxable' => ['nullable', 'numeric'],
            'total_gst' => ['nullable', 'numeric'],
            'balance_amount' => ['nullable', 'numeric'],
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);

        $invoice = Invoice::updateorcreate(['booking_id' => $booking->id],[
            'hotel_id' => $booking->hotel_id,
            'booking_id' => $booking->id,
            'invoice_number' => $this->generateInvoiceNumber($booking->hotel_id),
            'is_gst_applicable' => (bool) ($booking->hotel?->is_gst_applicable ?? 0),
            'invoice_tax_type' => $booking->hotel?->invoice_tax_type ?? 'exclusive',
            'cgst_percentage' => $booking->hotel?->cgst_percentage ?? 0,
            'sgst_percentage' => $booking->hotel?->sgst_percentage ?? 0,
            'igst_percentage' => $booking->hotel?->igst_percentage ?? 0,
            'room_amount' => (float) ($request->input('room_amount') ?? 0),
            'service_amount' => (float) ($request->input('service_amount') ?? 0),
            'subtotal' => (float) ($validated['total_taxable'] ?? 0),
            'cgst_amount' => (float) ($request->input('cgst_amount') ?? 0),
            'sgst_amount' => (float) ($request->input('sgst_amount') ?? 0),
            'igst_amount' => (float) ($request->input('igst_amount') ?? 0),
            'total_tax' => (float) ($validated['total_gst'] ?? 0),
            'grand_total' => (float) ($validated['grand_total'] ?? 0),
            'invoice_date' => $validated['invoice_date'],
        ]);

        $bookingServiceRows = [];
        $roomAmount = 0.0;
        $serviceAmount = 0.0;

        foreach ($validated['items'] as $item) {
            $itemType = strtolower((string) ($item['item_type'] ?? 'service'));
            $description = trim((string) ($item['description'] ?? '')) ?: 'Item';
            $quantity = (int) ($item['qty'] ?? $item['quantity'] ?? 1);
            $quantity = $quantity > 0 ? $quantity : 1;
            $price = (float) ($item['rate'] ?? $item['price'] ?? 0);
            $total = (float) ($item['total'] ?? ($price * max($quantity, 1)));

            if ($itemType === 'room') {
                $roomAmount += $total;
            } elseif (in_array($itemType, ['extra', 'service'], true)) {
                $serviceAmount += $total;
            }

            $existingItem = $invoice->items()
                ->where('item_type', $itemType)
                ->where('item_name', $description)
                ->first();

            if ($existingItem) {
                $existingItem->update([
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $total,
                ]);
            } else {
                $invoice->items()->create([
                    'item_type' => $itemType,
                    'item_name' => $description,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $total,
                ]);
            }

            if ($itemType === 'service' && !empty($item['additional_service_id'])) {
                $serviceId = (int) $item['additional_service_id'];
                $service = AdditionalService::where('id', $serviceId)
                    ->where('hotel_id', $booking->hotel_id)
                    ->first();

                if ($service) {
                    $bookingServiceRows[$serviceId] = [
                        'quantity' => $quantity,
                        'amount' => $total,
                    ];
                }
            }
        }

        if (!empty($bookingServiceRows)) {
            $booking->additionalServices()->detach(array_keys($bookingServiceRows));
            $booking->additionalServices()->attach($bookingServiceRows);
        }

        $invoice->update([
            'room_amount' => $roomAmount,
            'service_amount' => $serviceAmount,
            'subtotal' => (float) ($validated['total_taxable'] ?? 0),
            'cgst_amount' => (float) ($request->input('cgst_amount') ?? 0),
            'sgst_amount' => (float) ($request->input('sgst_amount') ?? 0),
            'igst_amount' => (float) ($request->input('igst_amount') ?? 0),
            'total_tax' => (float) ($validated['total_gst'] ?? 0),
            'grand_total' => (float) ($validated['grand_total'] ?? 0),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Invoice saved successfully.',
            'data' => ['invoice_id' => $invoice->id],
        ]);
    }

    protected function generateInvoiceNumber($hotelId)
    {
        $hotel = \App\Models\Hotel::findOrFail($hotelId);
        $prefix = $hotel->invoice_prefix ?? 'INV';
        $lastInvoice = Invoice::where('hotel_id', $hotelId)->orderByDesc('id')->first();
        $nextNumber = $lastInvoice ? (int) preg_replace('/\D+/', '', $lastInvoice->invoice_number) + 1 : 1;

        return $prefix . '-' . str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        //
    }

    public function generate()
    {
        $bookings = Booking::with(['primaryGuest', 'invoices'])
            ->where('hotel_id', Auth::user()->hotel_id)
            ->where(function ($query) {
                $query->whereIn('status', ['checked_in', 'checked_out'])
                    ->orWhereNotNull('actual_check_in_at')
                    ->orWhereNotNull('actual_check_out_at');
            })
            ->latest()
            ->paginate(perPage())
            ->withQueryString();
        $countries = Country::all();
        $states = State::all();
        $agents = Agent::all();
        $additionalServices = AdditionalService::where('hotel_id', Auth::user()->hotel_id)
            ->where('status', 1)
            ->orderBy('service_name')
            ->get();

        return view('pages.invoiceManagement.generateInvoice.index', compact('bookings', 'countries', 'states', 'agents', 'additionalServices'));
    }

    public function getInvoiceData($id)
    {
        $booking = Booking::with([
            'guests' => function ($q) {
                $q->where('is_primary_guest', 1);
            },
            'guests.country',
            'guests.state',
            'additionalServices' => function ($q) {
                $q->withPivot(['quantity', 'amount']);
            },
            'invoice',
        ])->findOrFail($id);

        $hotel = $booking->hotel;
        $guest = $booking->guests->first();

        $roomPrice = (float) $booking->base_amount;
        $extraRoomCharge = 0.0;
        $totalAmount = (float) $booking->total_amount;
        $hourlyBreakdown = null;

        if ($booking->booking_type === 'hourly' && $booking->actual_check_in_at && $booking->actual_check_out_at) {
            try {
                $hourlyBreakdown = PricingService::calculateActualHourlyCharge($booking);
                $roomPrice = (float) ($hourlyBreakdown['base_amount'] ?? 0);
                $extraRoomCharge = (float) ($hourlyBreakdown['extra_amount'] ?? 0);
                $totalAmount = (float) $hourlyBreakdown['total_amount'];
            } catch (\Throwable $e) {
                \Log::warning('Hourly invoice pricing fallback used for booking #' . $booking->id . ': ' . $e->getMessage());
            }
        }

        $additionalServices = $booking->additionalServices->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->service_name ?? $service->name ?? 'Additional Service',
                'quantity' => (int) ($service->pivot->quantity ?? 1),
                'unit_price' => (float) ($service->cost ?? $service->pivot->amount ?? 0),
                'amount' => (float) ($service->pivot->amount ?? 0),
            ];
        })->values()->all();

        return response()->json([
            'name' => $guest->full_name ?? '',
            'email' => $guest->email ?? '',
            'address' => $guest->address ?? '',
            'country' => $guest->country_id ?? '',
            'state' => $guest->state_id ?? '',
            'gstin' => '', // for now empty (you can store later)
            'invoice_date' => now()->format('Y-m-d'),
            'booking_type' => $booking->booking_type ?? 'room',
            'no_of_rooms' => (int) ($booking->no_of_rooms ?? 1),
            'room_price' => $roomPrice,
            'extra_room_charge' => $extraRoomCharge,
            'hourly_base_hours' => $hourlyBreakdown['base_hours'] ?? 0,
            'hourly_extra_hours' => $hourlyBreakdown['extra_hours'] ?? 0,
            'total_nights' => (int) ($booking->nights ?? 1),
            'additional_services' => $additionalServices,
            'service_total' => array_sum(array_map(fn($service) => (float) ($service['amount'] ?? 0), $additionalServices)),
            'total_amount' => $totalAmount,
            'hotel_state_id' => $hotel->state,
            'guest_state_id' => $guest->state_id,
            'invoice_id' => $booking->invoice?->id,
            'gst' => [
                'is_applicable' => $hotel->is_gst_applicable,
                'cgst' => $hotel->cgst_percentage,
                'sgst' => $hotel->sgst_percentage,
                'igst' => $hotel->igst_percentage,
                'type' => $hotel->invoice_tax_type // inclusive / exclusive
            ]
        ]);
    }

    public function download(Invoice $invoice)
    {
        $booking = $invoice->booking;
        $guest = $booking?->primaryGuest ?? $booking?->guests()->where('is_primary_guest', 1)->first();

        $viewData = [
            'invoice' => $invoice,
            'booking' => $booking,
            'guest' => $guest,
            'hotel' => $booking?->hotel,
            'items' => $invoice->items,
            'subtotal' => $invoice->subtotal,
            'tax' => $invoice->total_tax,
            'grand_total' => $invoice->grand_total,
        ];

        return view('invoices.master-invoice1.invoice-1', compact('viewData'));
    }
}
