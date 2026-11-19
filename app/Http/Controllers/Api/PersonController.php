<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Person;

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
            'pairing_id' => 'nullable|integer',
            'dob' => 'nullable|date',
            'document_id' => 'required|string|max:255',

            'documents' => 'nullable|array',
            'documents.*' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

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
            'hotel_id' => auth()->user()->hotel_id,
            'full_name' => $request->full_name,
            'gender' => $request->gender,
            'mobile' => $request->mobile,
            'address' => $request->address,
            'pairing_id' => $request->pairing_id,
            'dob' => $request->dob,
            'document_id' => $request->document_id,
            'documents' => $documents,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Person created successfully.',
            'data' => $person,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female,Other',
            'mobile' => 'nullable|string|max:15',
            'address' => 'nullable|string',
            'pairing_id' => 'nullable|integer',
            'dob' => 'nullable|date',
            'document_id' => 'required|string|max:255',

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

        // Existing document images
        $documents = $person->documents ?? [];

        $documentPath = public_path('persons/documents');

        if (!file_exists($documentPath)) {
            mkdir($documentPath, 0755, true);
        }

        // Upload new document images
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $photo) {

                $fileName = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();

                $photo->move($documentPath, $fileName);

                $documents[] = 'persons/documents/' . $fileName;
            }
        }

        // Update person
        $person->update([
            'full_name' => $request->full_name,
            'gender' => $request->gender,
            'mobile' => $request->mobile,
            'address' => $request->address,
            'pairing_id' => $request->pairing_id,
            'dob' => $request->dob,
            'document_id' => $request->document_id,
            'documents' => $documents,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Person updated successfully.',
            'data' => $person->fresh(),
        ]);
    }
}
