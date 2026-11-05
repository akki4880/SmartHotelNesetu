<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class HotelSetupController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:hotel_setup,view', only: ['index']),
            new Middleware('permission:hotel_setup,create', only: ['create', 'store']),
            new Middleware('permission:hotel_setup,edit', only: ['edit', 'update', 'saveBasicInfo', 'saveOperations', 'saveGST', 'saveTaxSettings']),
        ];
    }

    public function index()
    {
        $hotel = Hotel::where('id', auth()->user()->hotel_id)->firstOrFail();
        $countries = Country::all();
        $currencies = Currency::all();
        return view('pages.hotelsetup.index', compact('hotel', 'countries', 'currencies'));
    }

    public function saveBasicInfo(Request $request)
    {
        $request->validate([
            'hotel_name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'website' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',

            'address_line1' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
        ]);

        try {
            DB::beginTransaction();

            $hotel = Hotel::where('id', auth()->user()->hotel_id)->firstOrFail();

            $logoPath = $hotel->logo;
            if ($request->hasFile('logo')) {
                if ($hotel->logo && Storage::disk('public')->exists('hotel_logos/' . $hotel->logo)) {
                    Storage::disk('public')->delete('hotel_logos/' . $hotel->logo);
                }

                $logoName = time() . '_' . $request->file('logo')->getClientOriginalName();
                $request->file('logo')->storeAs('hotel_logos', $logoName, 'public');
                $logoPath = $logoName;
            }

            $hotel->update([
                'hotel_name' => $request->hotel_name,
                'legal_name' => $request->legal_name,
                'phone' => $request->phone,
                'alternate_phone' => $request->alternate_phone,
                'website' => $request->website,
                'logo' => $logoPath,

                'address_line1' => $request->address_line1,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'pincode' => $request->pincode,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Hotel basic information saved successfully.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to save hotel information.',
                'error' => $e->getMessage(), // remove in production
            ], 500);
        }
    }

    public function saveOperations(Request $request, Hotel $hotel)
    {
        $validated = $request->validate([
            'check_in_time'        => 'required',
            'check_out_time'       => 'required',
            'cancellation_policy'  => 'nullable',
            'terms_conditions'     => 'nullable',
        ]);
        try {
            DB::beginTransaction();

            $hotel->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Operations details saved successfully'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to save hotel information.',
                'error' => $e->getMessage(), // remove in production
            ], 500);
        }
    }

    public function savePricing(Request $request, Hotel $hotel)
    {
        $validated = $request->validate([
            'room_rate_type'   => 'required|array|min:1',
            'room_rate_type.*' => 'in:room,hourly',
            'currency'       => 'required|string|max:10',
        ]);
        try {
            DB::beginTransaction();

            $hotel->room_rate_type = $request->room_rate_type;
            $hotel->currency = $request->currency;
            $hotel->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pricing setup saved successfully'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to save hotel information.',
                'error' => $e->getMessage(), // remove in production
            ], 500);
        }
    }

    public function saveTaxBilling(Request $request, Hotel $hotel)
    {
        $request->validate([
            'is_gst_applicable' => 'nullable|boolean',
            'gst_number' => [
                'nullable',
                'required_if:is_gst_applicable,1',
                'string',
                'size:15',
                'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'
            ],

            'pan_number' => [
                'nullable',
                'string',
                'size:10',
                'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'
            ],
            'cgst_percentage' => 'nullable|numeric|min:0|max:100',
            'sgst_percentage' => 'nullable|numeric|min:0|max:100',
            'igst_percentage' => 'nullable|numeric|min:0|max:100',

            'invoice_tax_type' => 'nullable|in:inclusive,exclusive',
        ]);

        try {
            $isGstApplicable = $request->boolean('is_gst_applicable');
            $defaultTaxType = $hotel->invoice_tax_type ?? 'exclusive';
            $defaultCgst = $hotel->cgst_percentage ?? 9;
            $defaultSgst = $hotel->sgst_percentage ?? 9;
            $defaultIgst = $hotel->igst_percentage ?? 18;

            $hotel->update([
                'is_gst_applicable' => $isGstApplicable,
                'gst_number'        => $request->gst_number,
                'pan_number'        => $request->pan_number,
                'cgst_percentage' => $isGstApplicable
                    ? ($request->filled('cgst_percentage') ? $request->cgst_percentage : $defaultCgst)
                    : $defaultCgst,
                'sgst_percentage' => $isGstApplicable
                    ? ($request->filled('sgst_percentage') ? $request->sgst_percentage : $defaultSgst)
                    : $defaultSgst,
                'igst_percentage' => $isGstApplicable
                    ? ($request->filled('igst_percentage') ? $request->igst_percentage : $defaultIgst)
                    : $defaultIgst,
                'invoice_tax_type' => $isGstApplicable
                    ? ($request->invoice_tax_type ?? $defaultTaxType)
                    : $defaultTaxType,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Tax & billing info saved successfully.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save tax & billing info.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function saveBookingSettings(Request $request, Hotel $hotel)
    {
        try {

            // ===============================
            // VALIDATION
            // ===============================
            $validated = $request->validate([
                'booking_prefix'   => 'required|string|max:10',
                'booking_sequence' => 'required|integer|min:1'
            ]);

            // ===============================
            // PREVENT UPDATE IF ALREADY SET
            // ===============================
            if ($hotel->bookings()->count() > 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Booking settings cannot be modified once bookings are done.'
                ], 400);
            }

            // ===============================
            // UPDATE HOTEL
            // ===============================
            $hotel->update([
                'booking_prefix'   => $validated['booking_prefix'],
                'booking_sequence' => $validated['booking_sequence']
            ]);


            // dd($hotel);


            return response()->json([
                'status' => true,
                'message' => 'Booking settings saved successfully.'
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {

            \Log::error('Booking Settings Save Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while saving booking settings.'
            ], 500);
        }
    }

    public function savePOSettings(Request $request, Hotel $hotel)
    {
        try {

            // ===============================
            // VALIDATION
            // ===============================
            $validated = $request->validate([
                'po_prefix' => 'required|string|max:20',
                'po_start_number' => 'required|integer|min:1',
            ]);

            // ===============================
            // PREVENT UPDATE IF PO ALREADY CREATED
            // ===============================
            $poExists = \DB::table('inventory_purchase_orders')
                ->where('hotel_id', $hotel->id)
                ->exists();

            if ($poExists) {
                return response()->json([
                    'status' => false,
                    'message' => 'PO settings cannot be modified once Purchase Orders are created.'
                ], 400);
            }

            // ===============================
            // UPDATE HOTEL
            // ===============================
            $hotel->update([
                'po_prefix' => $validated['po_prefix'],
                'po_start_number' => $validated['po_start_number'],
            ]);

            return response()->json([
                'status' => true,
                'message' => 'PO settings saved successfully.'
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {

            \Log::error('PO Settings Save Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while saving PO settings.'
            ], 500);
        }
    }
}
