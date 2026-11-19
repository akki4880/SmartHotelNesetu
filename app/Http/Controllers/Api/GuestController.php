<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Guest;
use App\Models\Country;

class GuestController extends Controller
{

    public function index(Request $request)
    {
        try {

            $query = Guest::with('idProofs')
                ->where('hotel_id', auth()->user()->hotel_id);

            /* Guest Name */
            if ($request->filled('full_name')) {
                $query->where('full_name', 'like', '%' . $request->full_name . '%');
            }

            /* Mobile */
            if ($request->filled('mobile')) {
                $query->where('mobile', 'like', '%' . $request->mobile . '%');
            }

            /* Email */
            if ($request->filled('email')) {
                $query->where('email', 'like', '%' . $request->email . '%');
            }

            /* ID Proof Type */
            if ($request->filled('id_type')) {
                $query->whereHas('idProofs', function ($q) use ($request) {
                    $q->where('id_type', $request->id_type);
                });
            }

            $guests = $query
                ->latest()
                ->paginate($request->input('per_page', 10))
                ->withQueryString();

            $countries = Country::all();

            return response()->json([
                'status' => true,
                'message' => 'Guests fetched successfully.',
                'data' => [
                    'guests' => $guests->items(),
                    'countries' => $countries,
                    'pagination' => [
                        'current_page' => $guests->currentPage(),
                        'last_page' => $guests->lastPage(),
                        'per_page' => $guests->perPage(),
                        'total' => $guests->total(),
                        'next_page_url' => $guests->nextPageUrl(),
                        'prev_page_url' => $guests->previousPageUrl(),
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
