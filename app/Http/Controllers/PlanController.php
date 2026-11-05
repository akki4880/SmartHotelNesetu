<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PlanController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:plans,view', only: ['index', 'show']),
            new Middleware('permission:plans,create', only: ['create', 'store']),
            new Middleware('permission:plans,edit', only: ['edit', 'update']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Plan::query();

        // 🔹 Plan Name
        if ($request->filled('plan_name')) {
            $query->where('name', 'like', '%' . $request->plan_name . '%');
        }

        // 🔹 Duration
        if ($request->filled('duration')) {
            $query->where('duration', $request->duration);
        }

        // 🔹 Price Range
        if ($request->filled('price_from')) {
            $query->where('price', '>=', $request->price_from);
        }

        if ($request->filled('price_to')) {
            $query->where('price', '<=', $request->price_to);
        }

        // 🔹 Included Features
        if ($request->filled('included_features')) {
            $query->where('included_features', 'like', '%' . $request->included_features . '%');
        }

        // 🔹 Excluded Features
        if ($request->filled('excluded_features')) {
            $query->where('excluded_features', 'like', '%' . $request->excluded_features . '%');
        }

        // 🔹 Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // ✅ Pagination (IMPORTANT)
        $plans = $query->latest()
            ->paginate(perPage())
            ->withQueryString();

        $permissions = Permission::where('status', 1)->where('is_visible_to_hotel_owner', 1)->get();

        return view('pages.plans.index', compact('plans', 'permissions'));
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
            'name' => 'required|string|max:100|unique:plans,name',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'included_features' => 'nullable|string',
            'excluded_features' => 'nullable|string',
            'status' => 'required|in:0,1',
            'permissions'           => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $included = $request->included_features
                ? array_map('trim', explode(',', $request->included_features))
                : null;

            $excluded = $request->excluded_features
                ? array_map('trim', explode(',', $request->excluded_features))
                : null;
            $permissions = $request->permissions ?? [];

            Plan::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'duration' => $request->duration,
                'included_features' => $included,
                'excluded_features' => $excluded,
                'status' => $request->status,
                'permissions'   => $permissions,
            ]);

            DB::commit();
            return response()->json(['message' => 'Plan created successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create plan.' . $e->getMessage()], 500);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(Plan $plan)
    {
        $data = [
            'title' => 'View Plan Details',

            'fields' => [
                [
                    'label' => 'Plan Name',
                    'value' => $plan->name,
                ],
                [
                    'label' => 'Included Features',
                    'value' => $plan->included_features ? implode(', ', $plan->included_features) : 'N/A',
                ],
                [
                    'label' => 'Excluded Features',
                    'value' => $plan->excluded_features ? implode(', ', $plan->excluded_features) : 'N/A',
                ],
                [
                    'label' => 'Price',
                    'value' => '$' . number_format($plan->price, 2),
                ],
                [
                    'label' => 'Duration',
                    'value' => $plan->duration . ' Days',
                ],
                [
                    'label' => 'Status',
                    'value' => $plan->status ? 'Active' : 'Inactive',
                ],
                [
                    'label' => 'Permissions',
                    'value' => $plan->permissions
                        ? collect($plan->permissions)->map(function ($actions, $module) {
                            return $module . ': ' . implode('|', $actions);
                        })->implode(', ')
                        : 'N/A',
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Plan $plan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());
        $request->validate([
            'name' => 'required|string|max:100|unique:plans,name,' . $id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'included_features' => 'nullable|string',
            'excluded_features' => 'nullable|string',
            'status' => 'required|in:0,1',
            'permissions' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $plan = Plan::findOrFail($id);

            $included = $request->included_features
                ? array_map('trim', explode(',', $request->included_features))
                : null;

            $excluded = $request->excluded_features
                ? array_map('trim', explode(',', $request->excluded_features))
                : null;

            $plan->update([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'duration' => $request->duration,
                'included_features' => $included,
                'excluded_features' => $excluded,
                'status' => $request->status,
                'permissions' => $request->permissions ? $request->permissions : [],
            ]);

            DB::commit();
            return response()->json(['message' => 'Plan updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update plan' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Plan $plan)
    {
        //
    }

}
