<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Person;
use Illuminate\Support\Facades\DB;

class PersonController extends Controller
{
    public function index(Request $request)
    {
        $query = Person::where('hotel_id', auth()->user()->hotel_id);

        // Search by name
        if ($request->filled('full_name')) {
            $query->where('full_name', 'like', '%' . $request->full_name . '%');
        }

        // Search by mobile
        if ($request->filled('mobile')) {
            $query->where('mobile', 'like', '%' . $request->mobile . '%');
        }

        // Search by document ID
        if ($request->filled('document_id')) {
            $query->where('document_id', 'like', '%' . $request->document_id . '%');
        }

        // Search by document number inside JSON
        if ($request->filled('document_number')) {
            $query->whereRaw(
                "JSON_SEARCH(documents, 'one', ?) IS NOT NULL",
                ['%' . $request->document_number . '%']
            );
        }

        $persons = $query->latest()->get();

        // Group paired persons together
        $groupedPersons = $persons->groupBy(function ($person) {
            return $person->pairing_id
                ? 'pair_' . $person->pairing_id
                : 'person_' . $person->id;
        })->map(function ($group) {

            return [
                'pairing_id' => $group->first()->pairing_id,
                'persons' => $group->values()->map(function ($person) {
                    return [
                        'id' => $person->id,
                        'hotel_id' => $person->hotel_id,
                        'pairing_id' => $person->pairing_id,
                        'full_name' => $person->full_name,
                        'dob' => $person->dob,
                        'gender' => $person->gender,
                        'mobile' => $person->mobile,
                        'address' => $person->address,
                        'document_id' => $person->document_id,
                        'documents' => $person->documents,
                        'created_at' => $person->created_at,
                        'updated_at' => $person->updated_at,
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'status' => true,
            'message' => 'Persons fetched successfully.',
            'data' => $groupedPersons,
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female,Other',
            'mobile' => 'nullable|string|max:15',
            'address' => 'nullable|string',
            'dob' => 'nullable|date',
            'document_id' => 'required|string|max:255',

            'documents' => 'nullable|array',
            'documents.*' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $hotelId = auth()->user()->hotel_id;

        $documents = [];

        $documentPath = public_path('persons/documents');

        if (!file_exists($documentPath)) {
            mkdir($documentPath, 0755, true);
        }

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $photo) {

                $fileName = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();

                $photo->move($documentPath, $fileName);

                $documents[] = 'persons/documents/' . $fileName;
            }
        }

        $person = Person::create([
            'hotel_id' => $hotelId,
            'full_name' => $request->full_name,
            'gender' => $request->gender,
            'mobile' => $request->mobile,
            'address' => $request->address,
            'dob' => $request->dob,
            'document_id' => $request->document_id,
            'documents' => $documents,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Person created successfully.',
            'data' => $person->fresh(),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female,Other',
            'mobile' => 'nullable|string|max:15',
            'address' => 'nullable|string',
            'dob' => 'nullable|date',
            'document_id' => 'required|string|max:255',

            'pairing_id' => 'nullable|integer',

            'documents' => 'nullable|array',
            'documents.*' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $person = Person::where('id', $id)
            ->where('hotel_id', auth()->user()->hotel_id)
            ->first();

        if (!$person) {
            return response()->json([
                'status' => false,
                'message' => 'Person not found.',
            ], 404);
        }

        $documents = $person->documents ?? [];

        $documentPath = public_path('persons/documents');

        if (!file_exists($documentPath)) {
            mkdir($documentPath, 0755, true);
        }

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $photo) {
                $fileName = time() . '_' . uniqid() . '.' .
                    $photo->getClientOriginalExtension();

                $photo->move($documentPath, $fileName);

                $documents[] = 'persons/documents/' . $fileName;
            }
        }

        $person->update([
            'full_name' => $request->full_name,
            'gender' => $request->gender,
            'mobile' => $request->mobile,
            'address' => $request->address,
            'dob' => $request->dob,
            'document_id' => $request->document_id,
            'pairing_id' => $request->has('pairing_id') ? $request->pairing_id : $person->pairing_id,
            'documents' => $documents,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Person updated successfully.',
            'data' => $person->fresh(),
        ]);
    }

    public function search(Request $request)
    {
        $request->validate([
            'document_number' => 'nullable|string',
            'mobile' => 'nullable|string|max:15',
        ]);

        if (!$request->filled('document_number') && !$request->filled('mobile')) {
            return response()->json([
                'status' => false,
                'message' => 'Please enter a document number or mobile number.',
            ], 422);
        }

        $hotelId = auth()->user()->hotel_id;

        $query = Person::where('hotel_id', $hotelId);

        $query->where(function ($q) use ($request) {

            if ($request->filled('document_number')) {
                $q->where('document_id', $request->document_number);
            }

            if ($request->filled('mobile')) {
                if ($request->filled('document_number')) {
                    $q->orWhere('mobile', $request->mobile);
                } else {
                    $q->where('mobile', $request->mobile);
                }
            }
        });

        $person = $query->latest()->first();

        if (!$person) {
            return response()->json([
                'status' => true,
                'found' => false,
                'message' => 'No existing person found.',
                'data' => null,
            ]);
        }

        // Fetch all members of the matched person's group
        if ($person->pairing_id) {
            $persons = Person::where('hotel_id', $hotelId)
                ->where(function ($q) use ($person) {
                    $q->where('pairing_id', $person->pairing_id)
                        ->orWhere('id', $person->pairing_id);
                })
                ->get();
        } else {
            $persons = collect([$person]);
        }

        return response()->json([
            'status' => true,
            'found' => true,
            'message' => 'Person details fetched successfully.',
            'pairing_id' => $person->pairing_id,
            'data' => $persons,
        ]);
    }

    public function updatePairing(Request $request, $id)
    {
        $request->validate([
            'pairing_id' => 'nullable|integer',
            'pair_with_id' => 'nullable|integer',
        ]);

        $hotelId = auth()->user()->hotel_id;

        $person = Person::where('hotel_id', $hotelId)->find($id);

        if (!$person) {
            return response()->json([
                'status' => false,
                'message' => 'Person not found.',
            ], 404);
        }

        $targetId = $request->input('pair_with_id')
            ?? $request->input('pairing_id');

        // Unpair only selected person
        if ($targetId === null) {
            $person->update([
                'pairing_id' => null,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Person unpaired successfully.',
                'data' => $person->fresh(),
            ]);
        }

        if ((int) $targetId === (int) $person->id) {
            return response()->json([
                'status' => false,
                'message' => 'A person cannot be paired with themselves.',
            ], 422);
        }

        $target = Person::where('hotel_id', $hotelId)->find($targetId);

        if (!$target) {
            return response()->json([
                'status' => false,
                'message' => 'Person to pair with not found.',
            ], 404);
        }

        DB::transaction(function () use ($person, $target) {

            // Use target's existing group ID or target's own ID
            $pairingId = $target->pairing_id ?? $target->id;

            // Assign ONLY the selected person
            $person->update([
                'pairing_id' => $pairingId,
            ]);
        });

        return response()->json([
            'status' => true,
            'message' => 'Person assigned successfully.',
            'data' => $person->fresh(),
            'pairing_id' => $pairingId,
        ]);
    }
}
