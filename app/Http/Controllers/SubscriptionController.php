<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Subscription $subscription)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subscription $subscription)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subscription $subscription)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subscription $subscription)
    {
        //
    }

    public function updateDates(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
            'start_date'      => 'required|date',
            'end_date'        => 'required|date|after_or_equal:start_date',
        ]);

        DB::beginTransaction();

        try {

            $subscription = Subscription::findOrFail($request->subscription_id);

            $subscription->start_date = $request->start_date;
            $subscription->end_date   = $request->end_date;

            // Auto status handling
            if (now()->gt($subscription->end_date)) {
                $subscription->status = 'expired';
            } else {
                $subscription->status = 'active';
            }

            $subscription->save();

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Subscription dates updated successfully'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update subscription'
            ], 500);
        }
    }
}
