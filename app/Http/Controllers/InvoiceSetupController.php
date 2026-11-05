<?php

namespace App\Http\Controllers;

use App\Models\InvoiceTemplate;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class InvoiceSetupController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:invoice_setup,view', only: ['index', 'show']),
            new Middleware('permission:invoice_setup,create', only: ['create', 'store']),
            new Middleware('permission:invoice_setup,edit', only: ['edit', 'update']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $hotel = Hotel::where('id', auth()->user()->hotel_id)->firstOrFail();
        $templates = InvoiceTemplate::latest()->paginate(perPage())->withQueryString();

        return view('pages.invoiceManagement.setup.index', compact('hotel', 'templates'));
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
        $request->validate([
            'default_template_id' => 'required|exists:invoice_templates,id',
            'invoice_prefix' => 'nullable|string|max:50',
            'invoice_number_format' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $hotel = Hotel::where('id', auth()->user()->hotel_id)->firstOrFail();

            $hotel->update([
                'default_invoice_template_id' => $request->default_template_id,
                'invoice_prefix' => $request->invoice_prefix,
                'invoice_number_format' => $request->invoice_number_format,
                'invoice_notes' => $request->notes,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Invoice setup saved successfully.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to save invoice setup: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Hotel $hotel)
    {
        $data = [
            'title' => 'Invoice Setup Details',

            'fields' => [
                [
                    'label' => 'Hotel Name',
                    'value' => $hotel->hotel_name,
                ],
                [
                    'label' => 'Invoice Prefix',
                    'value' => $hotel->invoice_prefix ?? 'N/A',
                ],
                [
                    'label' => 'Invoice Number Format',
                    'value' => $hotel->invoice_number_format ?? 'N/A',
                ],
                [
                    'label' => 'Notes',
                    'value' => $hotel->invoice_notes ?? 'N/A',
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Hotel $hotel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'default_template_id' => 'required|exists:invoice_templates,id',
            'invoice_prefix' => 'nullable|string|max:50',
            'invoice_number_format' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $hotel = Hotel::findOrFail($id);

            $hotel->update([
                'default_invoice_template_id' => $request->default_template_id,
                'invoice_prefix' => $request->invoice_prefix,
                'invoice_number_format' => $request->invoice_number_format,
                'invoice_notes' => $request->notes,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Invoice setup updated successfully.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to update invoice setup: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hotel $hotel)
    {
        //
    }
}
