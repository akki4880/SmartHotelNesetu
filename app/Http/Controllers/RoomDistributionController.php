<?php

namespace App\Http\Controllers;

use App\Models\RoomDistribution;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class RoomDistributionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:room_booking_chart,view', only: ['index', 'show']),
            new Middleware('permission:room_booking_chart,create', only: ['create', 'store']),
            new Middleware('permission:room_booking_chart,edit', only: ['edit', 'update']),
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(RoomDistribution $roomDistribution)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RoomDistribution $roomDistribution)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RoomDistribution $roomDistribution)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RoomDistribution $roomDistribution)
    {
        //
    }
}
