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

        // Search by document number stored in JSON
        if ($request->filled('document_number')) {
            $query->whereJsonContains('documents', [
                'document_number' => $request->document_number
            ]);
        }

        $persons = $query->latest()->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Persons fetched successfully.',
            'data' => $persons
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id' => 'nullable|integer',
            'full_name' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female,Other',
            'mobile' => 'nullable|string|max:15',
            'address' => 'nullable|string',

            'documents' => 'nullable|array',
            'documents.*.document_type' => 'required|string|max:100',
            'documents.*.document_number' => 'required|string|max:100',
            'documents.*.photos' => 'nullable|array',
            'documents.*.photos.*' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $person = null;

        if ($request->filled('id')) {
            $person = Person::where('id', $request->id)
                ->where('hotel_id', auth()->user()->hotel_id)
                ->first();

            if (!$person) {
                return response()->json([
                    'status' => false,
                    'message' => 'Person not found.',
                ], 404);
            }
        }

        $existingDocuments = $person?->documents ?? [];
        $documents = [];

        foreach ($request->input('documents', []) as $index => $document) {

            $oldDocument = $existingDocuments[$index] ?? [];

            $photos = $oldDocument['photos'] ?? [];

            if ($request->hasFile("documents.$index.photos")) {
                foreach ($request->file("documents.$index.photos") as $photo) {
                    $fileName = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();

                    $photo->move(
                        public_path('persons/documents'),
                        $fileName
                    );

                    $photos[] = 'persons/documents/' . $fileName;
                }
            }

            $documents[] = [
                'document_type' => $document['document_type'],
                'document_number' => $document['document_number'],
                'photos' => $photos,
            ];
        }

        $data = [
            'hotel_id' => auth()->user()->hotel_id,
            'full_name' => $request->full_name,
            'gender' => $request->gender,
            'mobile' => $request->mobile,
            'address' => $request->address,
            'documents' => $documents,
        ];

        if ($person) {
            $person->update($data);
        } else {
            $person = Person::create($data);
        }

        return response()->json([
            'status' => true,
            'message' => $person->wasRecentlyCreated
                ? 'Person created successfully.'
                : 'Person updated successfully.',
            'data' => $person->fresh(),
        ]);
    }
}
