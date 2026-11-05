<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\State;
use App\Models\Guest;
use App\Models\GuestIdProof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class GuestController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:guests,view', only: ['index', 'show']),
            new Middleware('permission:guests,create', only: ['create', 'store']),
            new Middleware('permission:guests,edit', only: ['edit', 'update']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
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

        /* ID Proof Type (relation filter) */
        if ($request->filled('id_type')) {
            $query->whereHas('idProofs', function ($q) use ($request) {
                $q->where('id_type', $request->id_type);
            });
        }

        $guests = $query
            ->latest()
            ->paginate(perPage())
            ->withQueryString();

        $countries = Country::all(); 

        return view(
            'pages.guests.index',
            compact('countries', 'guests')
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
        $request->validate([
            'full_name' => 'required|string|max:255',
            'mobile' => [
                'required',
                'string',
                'max:20',
                Rule::unique('guests')->where(function ($query) {
                    return $query->where('hotel_id', auth()->user()->hotel_id);
                }),
            ],
            'email'     => 'required|email',
            'address'   => 'nullable|string',
            'dob'       => 'nullable|date',
            'id_type.*'  => 'required_with:id_image.*|string',
            'id_image.*' => 'required_with:id_type.*|image|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);


        try {
            DB::beginTransaction();

            // 1️⃣ Create Guest
            $guest = Guest::create([
                'hotel_id' => auth()->user()->hotel_id, // adjust if needed
                'full_name' => $request->full_name,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'address' => $request->address,
                'dob' => $request->dob,
                'country_id' => $request->country_id,
                'state_id' => $request->state_id,
            ]);

            // 2️⃣ Save ID Proofs
            // 2️⃣ Save ID Proofs
            if ($request->has('id_type')) {

                foreach ($request->id_type as $index => $type) {

                    if (!$request->hasFile("id_image.$index")) {
                        continue;
                    }

                    // Dynamic folder
                    $folder = 'guest_ids/' . auth()->user()->hotel_id . '/' . $guest->id;

                    $path = $request->file("id_image.$index")
                        ->store($folder, 'public');

                    GuestIdProof::create([
                        'guest_id' => $guest->id,
                        'id_type' => $type,
                        'id_file_path' => $path,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Guest created successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Guest $guest)
    {
        $data = [
            'title' => 'View Guest Details',

            'fields' => [
                [
                    'label' => 'Guest Name',
                    'value' => $guest->full_name,
                ],
                [
                    'label' => 'Mobile',
                    'value' => $guest->mobile,
                ],
                [
                    'label' => 'Email',
                    'value' => $guest->email,
                ],
                [
                    'label' => 'Address',
                    'value' => $guest->address,
                ],
                [
                    'label' => 'Date of Birth',
                    'value' => $guest->dob ? formatDateTime($guest->dob, 'd M Y') : 'N/A',
                ],
                [
                    'label' => 'Country',
                    'value' => $guest->country->name ?? 'N/A',
                ],
                [
                    'label' => 'State',
                    'value' => $guest->state->state_name ?? 'N/A',
                ],
                [
                    'label' => 'Created At',
                    'value' => formatDateTime($guest->created_at),
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Guest $guest)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'mobile' => [
                'required',
                'string',
                'max:20',
                Rule::unique('guests')->where(function ($query) {
                    return $query->where('hotel_id', auth()->user()->hotel_id);
                })->ignore($id),
            ],
            'email'     => 'required|email',
            'address'   => 'nullable|string',
            'dob'       => 'nullable|date',
            'id_type.*'  => 'required_with:id_image.*|string',
            'id_image.*' => 'nullable|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $guest = Guest::findOrFail($id);

            // 1️⃣ Update Guest
            $guest->update([
                'full_name' => $request->full_name,
                'mobile'    => $request->mobile,
                'email'     => $request->email,
                'address'   => $request->address,
                'dob'       => $request->dob,
                'country_id' => $request->country_id,
                'state_id'   => $request->state_id,
            ]);

            // 2️⃣ Add new ID proofs if uploaded
            if ($request->has('id_type')) {

                foreach ($request->id_type as $index => $type) {

                    if (!$request->hasFile("id_image.$index")) {
                        continue;
                    }

                    $folder = 'guest_ids/' . auth()->user()->hotel_id . '/' . $guest->id;

                    $path = $request->file("id_image.$index")
                        ->store($folder, 'public');

                    GuestIdProof::create([
                        'guest_id' => $guest->id,
                        'id_type' => $type,
                        'id_file_path' => $path,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Guest updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Guest $guest)
    {
        //
    }

    public function find(Request $request)
    {
        try {
            $guest = Guest::with('idProofs')->where('hotel_id', auth()->user()->hotel_id)
                ->where('mobile', $request->mobile)
                ->first();

            return response()->json([
                'status' => true,
                'data'   => $guest
            ]);
        } catch (\Throwable $e) {

            \Log::error('Guest find error', [
                'mobile' => $request->mobile,
                'error'  => $e->getMessage()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch guest details',
                'error'   => $e->getMessage() // remove in production if needed
            ], 500);
        }
    }
}
