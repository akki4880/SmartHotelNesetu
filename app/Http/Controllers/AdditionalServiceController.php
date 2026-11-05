<?php

namespace App\Http\Controllers;

use App\Models\AdditionalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AdditionalServiceController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:additional_service,view', only: ['index', 'show']),
            new Middleware('permission:additional_service,create', only: ['create', 'store']),
            new Middleware('permission:additional_service,edit', only: ['edit', 'update']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = AdditionalService::where('hotel_id', Auth::user()->hotel_id);

        /* Service Name */
        if ($request->filled('service_name')) {
            $query->where('service_name', 'like', '%' . $request->service_name . '%');
        }

        /* Price Range */
        if ($request->filled('min_price')) {
            $query->where('cost', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('cost', '<=', $request->max_price);
        }

        /* Status */
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $additionalServices = $query
            ->orderBy('service_name')
            ->paginate(perPage())
            ->withQueryString();

        return view(
            'pages.additionalservice.index',
            compact('additionalServices')
        );
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
        // dd($request->all());
        $request->validate([
            'service_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('additional_services')
                    ->where(function ($query) use ($request) {
                        return $query->where('hotel_id', Auth::user()->hotel_id);
                    }),
            ],
            'cost' => 'required|numeric|min:0',
            'status' => 'required|in:0,1',
        ]);

        try {
            DB::beginTransaction();
            AdditionalService::create([
                'service_name' => $request->service_name,
                'cost' => $request->cost,
                'hotel_id' => Auth::user()->hotel_id,
                'status' => $request->status,
            ]);

            DB::commit();
            return response()->json(['message' => 'Service added successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create plan.' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(AdditionalService $additionalService)
    { 
        $data = [
            'title' => 'View Additional Service Details',

            'fields' => [
                [
                    'label' => 'Service Name',
                    'value' => $additionalService->service_name,
                ],
                [
                    'label' => 'Cost',
                    'value' => $additionalService->cost,
                ],
                [
                    'label' => 'Status',
                    'value' => $additionalService->status ? 'Active' : 'Inactive',
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AdditionalService $additionalService)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AdditionalService $additional_service)
    {
        try {
            $validated = $request->validate([
                'service_name' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('additional_services')
                        ->where(function ($query) {
                            return $query->where('hotel_id', Auth::user()->hotel_id);
                        })
                        ->ignore($additional_service->id),
                ],
                'cost'   => 'required|numeric|min:0',
                'status' => 'required|in:0,1',
            ]);

            $additional_service->update($validated);

            return response()->json([
                'status'  => true,
                'message' => 'Additional service updated successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdditionalService $additionalService)
    {
        //
    }

}
