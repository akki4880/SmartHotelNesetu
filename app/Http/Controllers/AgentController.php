<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AgentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:agents,view', only: ['index', 'show']),
            new Middleware('permission:agents,create', only: ['create', 'store']),
            new Middleware('permission:agents,edit', only: ['edit', 'update']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Agent::where('hotel_id', Auth::user()->hotel_id);

        // Apply Filters
        if (request('name')) {
            $query->where('name', 'like', '%' . request('name') . '%');
        }

        if (request('email')) {
            $query->where('email', 'like', '%' . request('email') . '%');
        }

        if (request('mobile')) {
            $query->where('mobile', 'like', '%' . request('mobile') . '%');
        }

        if (request('company_name')) {
            $query->where('company_name', 'like', '%' . request('company_name') . '%');
        }

        if (request('commission_type')) {
            $query->where('commission_type', request('commission_type'));
        }

        if (request('status') !== null && request('status') !== '') {
            $query->where('status', request('status'));
        }

        // Final result
        $agents = $query->paginate(perPage())->withQueryString();

        return view('pages.agents.index', compact('agents'));
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
            // ✅ Validate input (NO hotel_id here)
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'mobile' => 'nullable|string|max:20',
                'company_name' => 'nullable|string|max:255',
                'commission_type' => 'required|in:percentage,fixed',
                'commission_value' => 'required|numeric|min:0',
                'status' => 'required|boolean',
            ]);

            DB::beginTransaction();

            // ✅ attach hotel automatically
            $validated['hotel_id'] = auth()->user()->hotel_id;

            $agent = Agent::create($validated);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Agent created successfully.',
                'data' => $agent
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Agent Create Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong. Please try again.' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Agent $agent)
    {
        $data = [
            'title' => 'View Agent Details',

            'fields' => [
                [
                    'label' => 'Agent Name',
                    'value' => $agent->name,
                ],
                [
                    'label' => 'Email',
                    'value' => $agent->email,
                ],
                [
                    'label' => 'Mobile',
                    'value' => $agent->mobile,
                ],
                [
                    'label' => 'Company Name',
                    'value' => $agent->company_name,
                ],
                [
                    'label' => 'Commission Type',
                    'value' => $agent->commission_type,
                ],
                [
                    'label' => 'Commission Value',
                    'value' => $agent->commission_value,
                ],
                [
                    'label' => 'Status',
                    'value' => $agent->status ? 'Active' : 'Inactive',
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render(); 
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Agent $agent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'mobile' => 'nullable|string|max:20',
                'company_name' => 'nullable|string|max:255',
                'commission_type' => 'required|in:percentage,fixed',
                'commission_value' => 'required|numeric|min:0',
                'status' => 'required|boolean',
            ]);

            $agent = Agent::where('hotel_id', auth()->user()->hotel_id)
                ->findOrFail($id);

            $agent->update($validated);

            return response()->json([
                'status' => true,
                'message' => 'Agent updated successfully.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Agent $agent)
    {
        //
    }
}
