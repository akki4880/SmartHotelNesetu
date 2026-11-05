<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\InventoryVendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class InventoryVendorController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:vendor_master,view', only: ['index', 'show']),
            new Middleware('permission:vendor_master,create', only: ['create', 'store']),
            new Middleware('permission:vendor_master,edit', only: ['edit', 'update']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $countries = Country::all();

        $vendors = InventoryVendor::where('hotel_id', Auth::user()->hotel_id);

        // 🔍 Filters
        if ($request->name) {
            $vendors->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->contact_person) {
            $vendors->where('contact_person', 'like', '%' . $request->contact_person . '%');
        }

        if ($request->phone) {
            $vendors->where('phone', 'like', '%' . $request->phone . '%');
        }

        if ($request->email) {
            $vendors->where('email', 'like', '%' . $request->email . '%');
        }

        if ($request->gst_number) {
            $vendors->where('gst_number', 'like', '%' . $request->gst_number . '%');
        }

        if ($request->pan_number) {
            $vendors->where('pan_number', 'like', '%' . $request->pan_number . '%');
        }

        if ($request->status !== null && $request->status !== '') {
            $vendors->where('status', $request->status);
        }

        // 📌 Order + Pagination
        $vendors = $vendors->paginate(perPage())->withQueryString();

        return view('pages.inventoryManagement.vendors.index', compact('countries', 'vendors'));
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
        try {

            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $vendor = InventoryVendor::create([
                'hotel_id' => Auth::user()->hotel_id,
                'user_id' => Auth::id(),
                'vendor_code' => CodeGenerator(
                    'inventory_vendors',
                    'vendor_code',
                    'VEN-',
                    4,
                    Auth::user()->hotel_id
                ), // Update later
                'name' => $request->name,
                'status' => $request->status ?? 1,
                'contact_person' => $request->contact_person,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'country' => $request->country,
                'state' => $request->state,
                'city' => $request->city,
                'pincode' => $request->pincode,
                'gst_number' => $request->gst_number,
                'pan_number' => $request->pan_number,
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'ifsc_code' => $request->ifsc_code,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Vendor created successfully',
                'data' => $vendor
            ]);
        } catch (\Exception $e) {

            // Log error (VERY IMPORTANT 🔥)
            Log::error('Vendor Store Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage() // remove in production if needed
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryVendor $Vendor)
    {
        $data = [
            'title' => 'View Vendor Details',

            'fields' => [
                [
                    'label' => 'Name',
                    'value' => $Vendor->name,
                ],
                [
                    'label' => 'Vendor Code',
                    'value' => $Vendor->vendor_code,
                ],
                [
                    'label' => 'Contact Person',
                    'value' => $Vendor->contact_person,
                ],
                [
                    'label' => 'CPhone',
                    'value' => $Vendor->phone,
                ],
                [
                    'label' => 'Email',
                    'value' => $Vendor->email,
                ],
                [
                    'label' => 'Adderss',
                    'value' => $Vendor->address,
                ],
                [
                    'label' => 'City',
                    'value' => $Vendor->city,
                ],
                [
                    'label' => 'State',
                    'value' => $Vendor->state,
                ],
                [
                    'label' => 'Country',
                    'value' => $Vendor->country,
                ],
                [
                    'label' => 'Pin Code',
                    'value' => $Vendor->pincode,
                ], 
                [
                    'label' => 'Pin Code',
                    'value' => $Vendor->pincode,
                ],  
                [
                    'label' => 'GST Number',
                    'value' => $Vendor->gst_number,
                ],  
                [
                    'label' => 'PAN Number',
                    'value' => $Vendor->pan_number,
                ],  
                [
                    'label' => 'Bank Name',
                    'value' => $Vendor->bank_name,
                ],  
                [
                    'label' => 'Account Number',
                    'value' => $Vendor->account_number,
                ],  
                [
                    'label' => 'IFSC Code',
                    'value' => $Vendor->ifsc_code,
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryVendor $inventoryVendor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {

            // 🔎 Validation
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            // 🔍 Find vendor
            $vendor = InventoryVendor::findOrFail($id);

            // ✏ Update fields
            $vendor->update([
                'name' => $request->name,
                'status' => $request->status ?? 1,
                'contact_person' => $request->contact_person,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'country' => $request->country,
                'state' => $request->state,
                'city' => $request->city,
                'pincode' => $request->pincode,
                'gst_number' => $request->gst_number,
                'pan_number' => $request->pan_number,
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'ifsc_code' => $request->ifsc_code,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Vendor updated successfully',
                'data' => $vendor
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'status' => false,
                'message' => 'Vendor not found'
            ], 404);
        } catch (\Exception $e) {

            // 🔥 Log full error
            Log::error('Vendor Update Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage() // remove in production
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryVendor $inventoryVendor)
    {
        //
    }
}
